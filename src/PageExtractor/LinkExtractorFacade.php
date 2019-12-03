<?php

namespace App\PageExtractor;

use App\Dto\Crawler\CrawlerGetLinks;
use App\Entity\CrawledDomainHistory;
use App\Exception\ValidationException;
use App\Repository\CrawledDomainHistoryRepository;
use App\Service\DtoValidator;
use App\Service\WebCrawler\UrlPath;
use App\Service\WebCrawler\WebCrawler;;
use Doctrine\ORM\EntityManagerInterface;
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

    /** @var DtoValidator */
    private $dtoValidator;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        WebCrawler $webCrawler,
        CacheInterface $cache,
        DtoValidator $dtoValidator,
        EntityManagerInterface $entityManager
    ) {
        $this->webCrawler = $webCrawler;
        $this->cache = $cache;
        $this->dtoValidator = $dtoValidator;
        $this->entityManager = $entityManager;
    }

    /**
     * TODO Save used patterns for domain, (and domain) and destroy them if someone continue crawling?
     * @return array
     * @throws ExtractorException
     * @throws ValidationException
     */
    public function getLinks(CrawlerGetLinks $crawlerGetLinks, bool $continueCrawling = false): array
    {
        $this->dtoValidator->validate($crawlerGetLinks);

        try {
            if ($continueCrawling) {
                // TODO look at annotations
                $this->cache->delete($crawlerGetLinks->getEncodedPattern());
            }

            return $this->cache->get($crawlerGetLinks->getEncodedPattern(), function (ItemInterface $item) use ($crawlerGetLinks, $continueCrawling) {
                $item->expiresAfter(1000000);

                return $this->extractLinks($crawlerGetLinks, $continueCrawling);
            });
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
     */
    private function extractLinks(CrawlerGetLinks $crawlerGetLinks, bool $continueCrawling = false): array
    {
        $filterCallback = function ($url) use ($crawlerGetLinks) {
            foreach ($crawlerGetLinks->getExcludedPaths() as $excludedPlace) {
                if (preg_match(sprintf('/%s/', preg_quote($excludedPlace, '/')), $url)) {
                    return true;
                }
            }

            return false;
        };

        $oldUrlsList = null;
        if ($continueCrawling) {
            $oldUrlsList = $this->cache->get($crawlerGetLinks->getEncodedDomain(), function () use ($crawlerGetLinks) {
                $this->extractLinks($crawlerGetLinks);
            });
            $this->cache->delete($crawlerGetLinks->getEncodedDomain());
        }

        $urlsList = $this->cache->get($crawlerGetLinks->getEncodedDomain(), function (ItemInterface $item) use ($crawlerGetLinks, $filterCallback, $oldUrlsList) {
            $item->expiresAfter(1000000);
            $domainUrlPath = new UrlPath($crawlerGetLinks->getDomainUrl());

            $lastCrawledQuantity = null;
            if (!is_null($oldUrlsList)) {
                /** @var CrawledDomainHistoryRepository $crawledDomainHist */
                $crawledDomainHist = $this->entityManager->getRepository(CrawledDomainHistory::class);
                $lastCrawledQuantity = $crawledDomainHist->findLatestCrawledLinks($domainUrlPath->getDomain());
            }

            [$urlsList, $crawledUrls] =  $this->webCrawler->getAllWebsiteLinks(
                $domainUrlPath,
                $crawlerGetLinks->getDomainUrl(),
                $crawlerGetLinks->getLimit(),
                $filterCallback,
                $oldUrlsList,
                $lastCrawledQuantity
            );

            $domainHistory = new CrawledDomainHistory();
            $domainHistory->setDomainName($domainUrlPath->getDomain());
            $domainHistory->setCrawledUrls($crawledUrls);
            $this->entityManager->persist($domainHistory);
            $this->entityManager->flush();

            return $urlsList;
        });

        if ($pattern = $crawlerGetLinks->getPattern()) {
            $urlsList = array_filter($urlsList, function (string $url) use ($pattern) {
                return (bool)preg_match($pattern, $url);
            });
        }

        return $urlsList;
    }
}
