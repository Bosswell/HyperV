<?php

namespace App\PageExtractor;

use App\Dto\Crawler\CrawlerGetLinks;
use App\Service\WebCrawler\UrlPath;
use App\Service\WebCrawler\WebCrawler;;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Throwable;


final class LinkExtractorFacade
{
    /** @var WebCrawler */
    private $webCrawler;

    /** @var CacheInterface */
    private $cache;


    public function __construct(WebCrawler $webCrawler, CacheInterface $cache)
    {
        $this->webCrawler = $webCrawler;
        $this->cache = $cache;
    }

    /**
     * @return string[]
     * @throws ExtractorException
     * @param CrawlerGetLinks $crawlerGetLinks
     */
    public function getLinks(CrawlerGetLinks $crawlerGetLinks): array
    {
        try {
            $cachedUrlsList = $this->cache->get($crawlerGetLinks->getEncodedPattern(), function (ItemInterface $item) use ($crawlerGetLinks) {
                $item->expiresAfter(1000000);

                return $this->extractLinks($crawlerGetLinks);
            });

            return $cachedUrlsList;
        } catch (InvalidArgumentException | Throwable $exception) {
            throw new ExtractorException(sprintf(
                'Error has occurred while extracting page links: Details [%s]',
                $exception->getMessage()
            ));
        }
    }

    /**
     * @return string[]
     * @throws InvalidArgumentException
     * @param CrawlerGetLinks $crawlerGetLinks
     */
    private function extractLinks(CrawlerGetLinks $crawlerGetLinks): array
    {
        $filterCallback = function ($url) use ($crawlerGetLinks) {
            foreach ($crawlerGetLinks->getExcludedPaths() as $excludedPlace) {
                if (preg_match(sprintf('/%s/', preg_quote($excludedPlace, '/')), $url)) {
                    return true;
                }
            }

            return false;
        };

        $urlsList = $this->cache->get($crawlerGetLinks->getName(), function () use ($crawlerGetLinks, $filterCallback) {
            return $this->webCrawler->getAllWebsiteLinks(
                new UrlPath($crawlerGetLinks->getDomainUrl()),
                $crawlerGetLinks->getDomainUrl(),
                $filterCallback
            );
        });

        if ($pattern = $crawlerGetLinks->getPattern()) {
            $urlsList = array_filter($urlsList, function (string $url) use ($pattern) {
                return (bool)preg_match($pattern, $url);
            });
        }

        return $urlsList;
    }
}
