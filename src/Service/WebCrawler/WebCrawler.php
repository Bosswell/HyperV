<?php

namespace App\Service\WebCrawler;

use App\Entity\CrawledDomain;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class WebCrawler
{
    /** @var HttpClient */
    private $httpClient;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(HttpClientInterface $httpClient, LoggerInterface $linkCrawlerLogger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $linkCrawlerLogger;
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

//    /**
//     * @return string[]
//     * @throws WebCrawlerException
//     * @param callable|null $filterCallback
//     */
//    public function getAllWebsiteLinks(
//        UrlPath $urlPath,
//        string $domainUrl,
//        int $limit,
//        ?callable $filterCallback = null,
//        ?array $urlsList = null,
//        ?int $lastCrawledUrls = null
//    ): array {
//        if (is_null($urlsList)) {
//            $urlsList = [
//                [
//                    'url' => $urlPath->getUrl(),
//                    'beenCrawled' => false
//                ]
//            ];
//        } else {
//            array_map(function ($url, $key) use ($lastCrawledUrls) {
//                return [
//                    'url' => $url,
//                    'beenCrawled' => $key < $lastCrawledUrls ? true : false
//                ];
//            }, $urlsList);
//
//            // Last URL always make not crawled to avoid calculation
//            end($url)['beenCrawled'] = false;
//        }
//
//        $crawler = new Crawler(null, $domainUrl);
//        [$extractedUrls, $crawledUrls] = $this->getPageLinks($urlsList, $crawler, $urlPath->getDomain(), $limit, $filterCallback);
//
//        return [
//            array_column($urlsList, 'url'),
//            $extractedUrls,
//            $crawledUrls
//        ];
//    }

//    /**
//     * @return array|void
//     * @throws WebCrawlerException
//     * @param array $urlsList
//     * @param Crawler $crawler
//     * @param string $domain
//     * @param callable|null $filterCallback -> callback which take $url as argument and return true, if url need to be excluded
//     * @param int|null $key -> don't set it by default. Parameter is used by next internal calls of function
//     * @param int $extractedUrls
//     */
//    private function getPageLinks(array &$urlsList, Crawler $crawler, string $domain, int $limit, ?callable $filterCallback = null, ?int $key = 0, int $extractedUrls = 0)
//    {
//        try {
//            $key = $key ?? array_search(false, array_column($urlsList, 'beenCrawled'));
//
//            /** @var UrlPath $urlPath */
//            $urlPath = new UrlPath($urlsList[$key]['url']);
//
//            try {
//                $document = $this->httpClient->request('GET', $urlPath->getUrl())->getContent(false);
//            } catch (Throwable $exception) {
//                $document = '';
//            }
//
//            $crawler->add($document);
//            $links = $crawler->filter('a')->links();
//
//            foreach ($links as $link) {
//                $url = new UrlPath($link->getUri());
//
//                if (
//                    $url->isValid() === false
//                    && $url->isRelative() === false
//                    || $url->getDomain() !== $domain
//                    || $filterCallback($url->getUrl()) ?? false
//                ) {
//                    continue;
//                }
//
//                if (false === in_array($url->getUrl(), array_column($urlsList, 'url'))) {
//                    $extractedUrls++;
//
//                    array_push($urlsList, [
//                        'url' => $url->getUrl(),
//                        'beenCrawled' => false
//                    ]);
//                }
//            }
//        } catch (Throwable $ex) {
//            $this->logger->warning(sprintf(
//                'Error has occurred while processing page links. More details: %s, %s',
//                $ex->getMessage(),
//                $ex->getTraceAsString()
//            ));
//        } finally {
//            $crawler->clear();
//            // Those variables are not cleared after executing another recursive function
//            // in fact garbage collector knows shit about them
//            unset($document, $links, $urlPath);
//
//            if (false !== $key && $key !== $limit) {
//                $this->logger->info(sprintf(
//                    'Domain: [%s],  Processed links: [%d], Uniq crawled links: [%d]',
//                    $domain,
//                    $key + 1,
//                    $extractedUrls
//                ));
//
//                $urlsList[$key]['beenCrawled'] = true;
//                [$extractedUrlss, $crawledUrls] = $this->getPageLinks(
//                    $urlsList,
//                    $crawler,
//                    $domain,
//                    $limit,
//                    $filterCallback,
//                    array_key_exists(++$key, $urlsList) ? $key : null,
//                    $extractedUrls
//                );
//dump([$extractedUrlss, $crawledUrls]);
//                return [
//                    $extractedUrlss,
//                    $crawledUrls > $key ? $crawledUrls : $key
//                ];
//
//            } else {
//                $this->logger->info('Extracting links from finished', [$domain]);
//            }
//        }
//    }

    public function getPageLinks(?callable $filterCallback = null)
    {
        $domain = 'x-kom.pl';
        $filesystem = new Filesystem();
        $fileName =__DIR__ . '/hello.txt';
        $filesystem->touch($fileName);
        $firstLink = 'https://www.x-kom.pl/';
        $crawler = new Crawler(null, $firstLink);

        $fileHandler = fopen($fileName,'a+');
//        fwrite($fileHandler, $firstLink);
        $file = new \SplFileObject($fileName, 'a+');
        $extractedLinks = 1;
        $crawledLinks = 0;

        do {
            $leftToCrawl = $extractedLinks - $crawledLinks - 1;
            $file->seek($leftToCrawl);
            $httpResponses = [];

            while (!$file->eof()) {
                $line = rtrim($file->fgets());
                array_push($httpResponses, $this->httpClient->request('GET', $line));
            }

            foreach ($this->httpClient->stream($httpResponses, 2.5) as $response => $chunk) {
                $crawledLinks++;

                try {
                    if (
                        $chunk->isFirst()
                        && $response->getHeaders(false)['content-type'][0] !== 'text/html'
                    ) {
                        continue;
                    } elseif ($chunk->isLast()) {
                        if ($response->getStatusCode() >= 400) continue;

                        $document = $response->getContent(false);
                        $pageLinks = $this->extractPageLinks($crawler, $document, $domain, $filterCallback);

                        $file->rewind();
                        while (!$file->eof()) {
                            if ($key = array_search($file->fgets(), $pageLinks)) {
                                unset($pageLinks[$key]);
                                continue;
                            }
                        }

                        foreach ($pageLinks as $pageLink) {
                            $file->fwrite(sprintf("%s\n", $pageLink));
                            $extractedLinks++;
                        }
                    }
                } catch (TransportExceptionInterface $e) {
                    // ...
                }
            }

            $crawledLinks++;
        } while($extractedLinks !== $crawledLinks);
    }

    private function extractPageLinks(Crawler $crawler, string $document, string $domain, callable $filterCallback)
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

        return $crawledLinks;
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