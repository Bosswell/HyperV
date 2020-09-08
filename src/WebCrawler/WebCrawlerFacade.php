<?php

namespace App\WebCrawler;

use App\Service\ResourcesManager;
use App\WebCrawler\Utils\DomainLinks;
use App\WebCrawler\Utils\Selector;
use App\WebCrawler\Utils\SelectorCollection;
use App\WebCrawler\Utils\UrlPath;
use Generator;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use SplFileObject;;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class WebCrawlerFacade
{
    // Crawling request are chunked because of memory leak
    // 100 requests at once consume around 40 MB RAM
    const MAX_REQUESTS_CHUNKS_SIZE = 100;

    /** @var HttpClient */
    private $httpClient;

    /** @var LoggerInterface */
    private $logger;

    /** @var CacheItemPoolInterface */
    private $cacheItemPool;

    /** @var ResourcesManager */
    private $resourceManager;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $linkCrawlerLogger,
        CacheItemPoolInterface $cacheItemPool,
        ResourcesManager $resourcesManager
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $linkCrawlerLogger;
        $this->cacheItemPool = $cacheItemPool;
        $this->resourceManager = $resourcesManager;
    }

    /**
     * @return SelectorCollection
     * @throws WebCrawlerException
     * @param SelectorCollection $selectorCollection
     * @param string $pageUrl
     */
    public function extractSelectorsFromWebPage(SelectorCollection $selectorCollection, string $pageUrl): SelectorCollection
    {
        try {
            $crawler = new Crawler($this->httpClient->request('GET', $pageUrl)->getContent());

            foreach ($selectorCollection as &$selector) {
                $this->extractSelectors($selector, $crawler);
            }

            return $selectorCollection;
        } catch (Throwable $ex) {
            throw new WebCrawlerException(sprintf(
                    'Error has occurred while extracting page selectors. Message: %s',
                    $ex->getMessage()
                )
            );
        }
    }

    /**
     * @return DomainLinks
     * @param UrlPath $urlPath
     * @param callable|null $filterCallback
     * @param DomainLinks|null $domainLinks -> if not null, Crawler will continue from last registered checkpoint
     *
     * @throws Throwable
     */
    public function getDomainLinks(UrlPath $urlPath, ?callable $filterCallback = null, ?int $limit = null, ?DomainLinks $domainLinks = null): DomainLinks
    {
        $crawler = new Crawler(null, $urlPath->getUrl());

        if (!is_null($domainLinks)) {
            $extractedLinks = $domainLinks->getExtractedLinks();
            $crawledLinks = $domainLinks->getCrawledLinks();
        } else {
            $extractedLinks = 0;
            $crawledLinks = 0;
        }

        $pageLinksFile = $this->resourceManager->getDomainCrawledLinksFile($urlPath, $domainLinks);

        do {
            foreach ($this->createRequests($pageLinksFile, $crawledLinks) as $httpResponsesChunk) {
                foreach ($this->httpClient->stream($httpResponsesChunk, 3) as $response => $chunk) {
                    try {
                        if ($chunk->isFirst() || $chunk->isTimeout()) {
                            $crawledLinks++;
                        }

                        if (
                            $chunk->isFirst()
                            && (
                                strpos($response->getHeaders(false)['content-type'][0],'text/html') === false
                                || $response->getStatusCode() >= 400
                            )
                        ) {
                            $response->cancel();
                            continue;
                        } elseif ($chunk->isLast()) {
                            $document = $response->getContent(false);
                            $pageLinks = $this->extractPageLinks($crawler, $document, $urlPath->getDomain(), $filterCallback);

                            $this->cacheHtmlDoc($document, $response);

                            $pageLinksFile->rewind();
                            while (!$pageLinksFile->eof()) {
                                if (($key = array_search(rtrim($pageLinksFile->fgets()), $pageLinks)) !== false) {
                                    unset($pageLinks[$key], $key);
                                    continue;
                                } elseif (empty($pageLinks)) {
                                    break;
                                }
                            }

                            foreach ($pageLinks as $pageLink) {
                                $this->logger->info(sprintf('Crawled url [%s]', $pageLink));
                                $pageLinksFile->fwrite(sprintf("%s\n", $pageLink));
                                $extractedLinks++;
                            }

                            unset($pageLinks, $document);
                        }
                    } catch (TransportExceptionInterface $e) {
                        $this->logger->warning('Network error has occurred', [$e->getMessage()]);
                    } catch (Throwable $e) {
                        $this->logger->critical(sprintf(
                            'Something went terrible wrong. File with crawled URLs is going to be deleted. [%s]',
                            $e->getMessage(),
                        ), [$e->getTraceAsString()]);

                        unlink($pageLinksFile);
                        break 3;
                    }
                }

                // $limit equal to 0 mean 'Unlimited'
                if ($limit !== 0 && $limit <= $crawledLinks) {
                    break 2;
                }
            }
        } while ($extractedLinks !== $crawledLinks);

        return new DomainLinks($extractedLinks, $crawledLinks, $pageLinksFile->getFilename());
    }

    /**
     * @return array
     * @param string $document HTML document
     * @param string $domain
     * @param callable $filterCallback
     * @param Crawler $crawler
     */
    private function extractPageLinks(Crawler $crawler, string &$document, string $domain, callable &$filterCallback)
    {
        $crawler->add($document);
        $links = $crawler->filter('a')->links();
        $crawler->clear();
        $crawledLinks = [];

        foreach ($links as $link) {
            $url = new UrlPath($link->getUri());

            if (
                $url->isValid() === false
                && $url->isRelative() === false
                || $url->getDomain() !== $domain
                || $filterCallback($url->getUrl()) ?? false
            ) {
                continue;
            }

            array_push($crawledLinks, $url->getUrl());
        }

        unset($links);

        return array_unique($crawledLinks);
    }

    private function cacheHtmlDoc(string $document, ResponseInterface $response): void
    {
        try {
            $cacheItem = $this->cacheItemPool->getItem(base64_encode($response->getInfo()['url']));

            if ($this->cacheItemPool->hasItem($cacheItem->getKey())) {
                $this->cacheItemPool->deleteItem($cacheItem->getKey());
            }

            $cacheItem->set($document);

            $this->cacheItemPool->save($cacheItem);
        } catch (CacheException $ex) {
            $this->logger->warning('Problem has occur while trying to cache HTML document.', [
                $ex->getMessage()
            ]);
        }
    }

    private function createRequests(SplFileObject $file, int $crawledLinks): Generator
    {
        $urls = [];

        if ($crawledLinks >= 0) {
            $file->seek($crawledLinks);
        }

        while (!$file->eof()) {
            $line = rtrim($file->fgets());
            if (!empty($line)) {
                array_push($urls, $line);
            }
        }

        foreach (array_chunk($urls, self::MAX_REQUESTS_CHUNKS_SIZE) as $urlsChunk) {
            yield array_map(function (string $url) {
                return $this->httpClient->request('GET', $url);
            }, $urlsChunk);
        }
    }

    /**
     * @return Selector
     * @throws WebCrawlerException
     * @param Selector $selector
     * @param Crawler $crawler
     */
    private function extractSelectors(Selector &$selector, Crawler $crawler): Selector
    {
        switch ($selector->getType()) {
            case Selector::CSS_TYPE:
                $selector->setCrawler(
                    $crawler->filter($selector->getPath())
                );

                return $selector;
            case Selector::XPATH_TYPE:
                $selector->setCrawler(
                    $crawler->filterXPath($selector->getPath())
                );

                return $selector;
        }

        throw new WebCrawlerException(sprintf(
                'Selector type [%s] is not supported',
                $selector->getType()
            )
        );
    }
}
