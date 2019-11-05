<?php

namespace App\Facade;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WebsiteCrawlerFacade
{
    private $websiteLink = 'https://symfony.com/doc/current/components/dom_crawler.html';
    private $selectors = [
        'Setup' => [
            'path' => '//html/body/div[2]/div/div[2]',
            'type' => 'XPath'
        ],
        'Installation' => [
            'path' => '//html/body/div[2]/div/div[2]',
            'type' => 'CSSSelector'
        ],
    ];

    /** @var Crawler */
    private $crawler;

    /** @var HttpClient */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
        $this->crawler = new Crawler();
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function crawlWebsite(string $pageLink)
    {
        $this->crawler->add(
            $this->httpClient->request('GET', $pageLink)->getContent()
        );

        foreach ($this->selectors as $selector) {
            switch ($selector['type']) {
                case 'XPath':
                    $this->crawler->filterXPath($selector['path']);
                    break;
                case 'CSSSelector':
                    $this->crawler->filter($selector['path']);
                    break;
                default:// wyjątek może być  wyrzucowny po zdefiniowaniu klasy selector
                   throw new \Exception(sprintf(
                       'Invalid selector type. Allowed [XPath ,CSSSelector], given [%s]',
                       $selector['type']
                   ));
            }
        }
    }
}