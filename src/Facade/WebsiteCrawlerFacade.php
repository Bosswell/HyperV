<?php

namespace App\Facade;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class WebsiteCrawlerFacade
{
    private $websiteLink = 'https://symfony.com/doc/current/components/dom_crawler.html';
    private $testCase = [
        'XPath' => [
            'Setup' => '//html/body/div[2]/div/div[2]',
        ],
        'CSSSelector' => [
            'Installation' => '#installation > h2:nth-child(1)'
        ]
    ];

    /** @var Crawler */
    private $crawler;

    public function __construct()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $this->websiteLink);
        $content = $response->getContent();
        $this->crawler = new Crawler($content);
        $xd = '//main/h1';
        dump($this->crawler->filterXPath($this->testCase['XPath']['Setup'])->filterXPath($xd)->text());
//        dump($this->crawler->filter($this->testCase['CSSSelector']['Installation']));
    }

    public function crawl()
    {
//        $this->crawler->
    }
}