<?php

namespace App\Service\WebCrawler;

use App\Entity\CrawledDomainHistory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
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

    /**
     * @return string[]
     * @throws WebCrawlerException
     * @param callable|null $filterCallback
     */
    public function getAllWebsiteLinks(
        UrlPath $urlPath,
        string $domainUrl,
        int $limit,
        ?callable $filterCallback = null,
        ?array $urlsList = null,
        ?int $lastCrawledUrls = null
    ): array {
        if (is_null($urlsList)) {
            $urlsList = [
                [
                    'url' => $urlPath->getUrl(),
                    'beenCrawled' => false
                ]
            ];
        } else {
            array_map(function ($url, $key) use ($lastCrawledUrls) {
                return [
                    'url' => $url,
                    'beenCrawled' => $key < $lastCrawledUrls ? true : false
                ];
            }, $urlsList);

            // Last URL always make not crawled to avoid calculation
            end($url)['beenCrawled'] = false;
        }

        dump($urlsList);die();


        $crawler = new Crawler(null, $domainUrl);
        $crawledUrls = $this->getPageLinks($urlsList, $crawler, $urlPath->getDomain(), $limit, $filterCallback);
        return [
            'urlsList' => array_column($urlsList, 'url'),
            'crawledUrls' => $crawledUrls
        ];
    }

    /**
     * @return int|void
     * @throws WebCrawlerException
     * @param array $urlsList
     * @param Crawler $crawler
     * @param string $domain
     * @param callable|null $filterCallback -> callback which take $url as argument and return true, if url need to be excluded
     * @param int|null $key -> don't set it by default. Parameter is used by next internal calls of function
     * @param int $crawledUrls
     */
    private function getPageLinks(array &$urlsList, Crawler $crawler, string $domain, int $limit, ?callable $filterCallback = null, ?int $key = 0, int $crawledUrls = 0)
    {
        try {
            $key = $key ?? array_search(false, array_column($urlsList, 'beenCrawled'));

            // If there is no other links to process
            if (false === $key || $key === $limit - 1) {
                $this->logger->info('Extracting links from finished', [$domain]);

                return $crawledUrls;
            }

            /** @var UrlPath $urlPath */
            $urlPath = new UrlPath($urlsList[$key]['url']);

            try {
                $document = $this->httpClient->request('GET', $urlPath->getUrl())->getContent(false);
            } catch (Throwable $exception) {
                $document = '';
            }

            $crawler->add($document);
            $links = $crawler->filter('a')->links();

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

                if (false === in_array($url->getUrl(), array_column($urlsList, 'url'))) {
                    $crawledUrls++;

                    array_push($urlsList, [
                        'url' => $url->getUrl(),
                        'beenCrawled' => false
                    ]);
                }
            }
        } catch (Throwable $ex) {
            $this->logger->warning(sprintf(
                'Error has occurred while processing page links. More details: %s, %s',
                $ex->getMessage(),
                $ex->getTraceAsString()
            ));
        } finally {
            $crawler->clear();
            // Those variables are not cleared after executing another recursive function
            // in fact garbage collector knows shit about them
            unset($document, $links, $urlPath);

            if (false !== $key && $key !== $limit) {
                $this->logger->info(sprintf(
                    'Domain: [%s],  Processed links: [%d], Uniq crawled links: [%d], MB used [%s] -> [%s]',
                    $domain,
                    $key + 1,
                    $crawledUrls
                ));
                
                $urlsList[$key]['beenCrawled'] = true;
                $this->getPageLinks(
                    $urlsList,
                    $crawler,
                    $domain,
                    $limit,
                    $filterCallback,
                    array_key_exists(++$key, $urlsList) ? $key : null,
                    $crawledUrls
                );
            }
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