<?php

namespace App\PageExtractor;

use App\Dto\Crawler\CrawlerGetLinks;
use App\Service\WebCrawler\UrlPath;
use App\Service\WebCrawler\WebCrawler;
use App\Service\WebCrawler\WebCrawlerException;

class LinkExtractorFacade
{
    /** @var WebCrawler */
    private $webCrawler;


    public function __construct(WebCrawler $webCrawler)
    {
        $this->webCrawler = $webCrawler;
    }

    /**
     * @throws WebCrawlerException
     */
    public function getLinks(CrawlerGetLinks $crawlerGetLinks): array
    {
        $filterCallback = function ($url) use ($crawlerGetLinks) {
            foreach ($crawlerGetLinks->getExcludedPaths() as $excludedPlace) {
                if (preg_match(preg_quote($excludedPlace), $url)) {
                    return true;
                }
            }

            return false;
        };

        $urlList = $this->webCrawler->getAllWebsiteLinks(
            new UrlPath($crawlerGetLinks->getDomainUrl()),
            $crawlerGetLinks->getDomainUrl(),
            $filterCallback
        );

        return $urlList;
    }
}