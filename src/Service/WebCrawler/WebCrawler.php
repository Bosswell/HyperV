<?php

namespace App\Service\WebCrawler;

use Generator;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use SplFileObject;;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class WebCrawler
{
    // Crawling request are chunked because of memory leak
    // 100 requests at once consume around 40 MB RAM
    const MAX_REQUESTS_CHUNKS_SIZE = 100;

    /** @var HttpClient */
    private $httpClient;

    /** @var LoggerInterface */
    private $logger;

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var CacheItemPoolInterface */
    private $cacheItemPool;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $linkCrawlerLogger,
        ParameterBagInterface $parameterBag,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $linkCrawlerLogger;
        $this->parameterBag = $parameterBag;
        $this->cacheItemPool = $cacheItemPool;
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
            $fileName = $domainLinks->getFileName();
        } else {
            $date = new \DateTime('now');

            $extractedLinks = 0;
            $crawledLinks = 0;
            $fileName = sprintf(
                '%s__%s',
                $urlPath->getDomain(),
                $date->format('Y-m-d__H_i_s')
            );
        }

        $varDir = ($this->parameterBag->get('kernel.project_dir') . '/var/');
        $pageLinksFile = $varDir . $fileName . '.txt';

        $file = new SplFileObject($pageLinksFile, 'a+');
        $file->fwrite($urlPath->getUrl(). "\n");

        do {
            foreach ($this->createRequests($file, $crawledLinks) as $httpResponsesChunk) {
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

                            $file->rewind();
                            while (!$file->eof()) {
                                if (($key = array_search(rtrim($file->fgets()), $pageLinks)) !== false) {
                                    unset($pageLinks[$key], $key);
                                    continue;
                                } elseif (empty($pageLinks)) {
                                    break;
                                }
                            }

                            foreach ($pageLinks as $pageLink) {
                                $this->logger->info(sprintf('Crawled url [%s]', $pageLink));
                                $file->fwrite(sprintf("%s\n", $pageLink));
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

                if (!is_null($limit) && $limit <= $crawledLinks) {
                    break 2;
                }
            }
        } while ($extractedLinks !== $crawledLinks);

        return new DomainLinks($extractedLinks, $crawledLinks, $fileName);
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
                $selector->setValue(
                    $crawler->filter($selector->getPath())->text()
                );

                return $selector;
            case Selector::XPATH_TYPE:
                $selector->setValue(
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