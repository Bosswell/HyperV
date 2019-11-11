<?php

namespace App\WebCrawler;

use App\Dto\Crawler\CrawlerGetLinks;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Throwable;

class WebCrawlerFacade
{
    /** @var Crawler */
    private $crawler;

    /** @var HttpClient */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
        $this->crawler = new Crawler(null, 'https://greencell.global/');
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
            $this->crawler->add(
                $this->httpClient->request('GET', $pageUrl)->getContent()
            );

            foreach ($selectorCollection as &$selector) {
                $this->extractSelectors($selector);
            }

            return $selectorCollection;
        } catch (Throwable $ex) {
            throw new WebCrawlerException(sprintf(
                    'Error has occurred while extracting page selectors. Message: %s',
                    $ex->getMessage()
                )
            );
        } finally {
            $this->crawler->clear();
        }
    }

    /**
     * @throws WebCrawlerException
     * @param CrawlerGetLinks $crawlerGetLinks
     */
    public function getAllWebsiteLinks(CrawlerGetLinks $crawlerGetLinks)
    {
        $linksList = [
            [
                'name' => $crawlerGetLinks->getDomainUrl(),
                'beenCrawled' => false
            ]
        ];

        $this->getPageLinks($linksList);
        die();
    }

    /**
     * @return bool|void
     * @throws WebCrawlerException
     * @param array $linksList
     */
    private function getPageLinks(array &$linksList)
    {
        try {
            $key = array_search(false, array_column($linksList, 'beenCrawled'));

            if (false === $key) {
                return true;
            }

            try {
                $document = $this->httpClient->request('GET', $linksList[$key]['name'])->getContent(false);
            } catch (Throwable $exception) {
                $document = '';
            }
            $this->crawler->add($document);

            foreach ($this->crawler->filter('a')->links() as $link) {
                $url = $link->getNode()->getAttribute('href');


                // TODO Improve that shit beyond
                if (false !== strpos($url, 'https://gr')) {
                    continue;
                }

                if (false === filter_var($link->getUri(), FILTER_VALIDATE_URL)) {
                    continue;
                }

                if (false === in_array($link->getUri(), array_column($linksList, 'name'))) {
                    array_push($linksList, [
                        'name' => $link->getUri(),
                        'beenCrawled' => false
                    ]);
                }
            }
            dump($linksList);
            $linksList[$key]['beenCrawled'] = true;
            $this->crawler->clear();
            $this->getPageLinks($linksList);

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
     */
    private function extractSelectors(Selector &$selector): Selector
    {
        switch ($selector->getType()) {
            case Selector::CSS_TYPE:
                $selector->setValue(
                    $this->crawler->filter($selector->getPath())->text()
                );

                return $selector;
            case Selector::XPATH_TYPE:
                $selector->setValue(
                    $this->crawler->filterXPath($selector->getPath())
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