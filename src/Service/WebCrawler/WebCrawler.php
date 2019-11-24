<?php

namespace App\Service\WebCrawler;

use App\Dto\Crawler\CrawlerGetLinks;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Throwable;

class WebCrawler
{
    /** @var HttpClient */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
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
     * @return array
     * @throws WebCrawlerException
     * @param callable|null $filterCallback
     */
    public function getAllWebsiteLinks(UrlPath $urlPath, string $domainUrl, ?callable $filterCallback = null): array
    {
        $urlsList = [
            [
                'url' => $urlPath,
                'beenCrawled' => false
            ]
        ];

        $crawler = new Crawler(null, $domainUrl);

        $this->getPageLinks($urlsList, $crawler, $urlPath->getDomain(), $filterCallback);

        return $urlsList;
    }

    /**
     * @return bool|void
     * @throws WebCrawlerException
     * @param array $urlsList
     * @param Crawler $crawler
     * @param string $domain
     * @param callable|null $filterCallback -> callback which take $url as argument and return true, if url need to be excluded
     * @param int|null $key -> don't set it by default. Parameter is used by next internal calls of function
     */
    private function getPageLinks(array &$urlsList, Crawler $crawler, string $domain, ?callable $filterCallback = null, $key = null)
    {
        try {
            $key = $key ?? array_search(false, array_column($urlsList, 'beenCrawled'));

            // If there is no other links to process
            if (false === $key) {
                return true;
            }

            try {
                /** @var UrlPath $urlPath */
                $urlPath = $urlsList[$key]['url'];
                $document = $this->httpClient->request('GET', $urlPath->getUrl())->getContent(false);
            } catch (Throwable $exception) {
                $document = '';
            }

            $crawler->add($document);
            $links = $crawler->filter('a')->links();

            foreach ($links as $link) {
                $url = new UrlPath($link->getUri());

                if (
                    $url->getDomain() !== $domain
                    && $url->isRelative() === false
                    || $url->isValid() === false
                    || $filterCallback($url->getUrl()) ?? false
                ) {
                    continue;
                }

                if (false === in_array($link->getUri(), array_column($urlsList, 'url'))) {
                    array_push($urlsList, [
                        'url' => $url,
                        'beenCrawled' => false
                    ]);
                }
            }

            $urlsList[$key]['beenCrawled'] = true;
            $crawler->clear();
            $this->getPageLinks(
                $urlsList,
                $crawler,
                $domain,
                $filterCallback,
                array_key_exists(++$key, $urlsList) ? $key : null
            );

        } catch (Throwable $ex) {
            throw new WebCrawlerException(sprintf(
                'Error has occurred while processing page links. More details: %s, %s',
                $ex->getMessage(),
                $ex->getTraceAsString()
            ));
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