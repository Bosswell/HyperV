<?php

namespace App\PageExtractor;

use App\Dto\Crawler\CrawlerGetDomainLinks;
use App\Entity\CrawledDomain;
use App\Exception\ValidationException;
use App\Repository\CrawledDomainPatternRepository;
use App\Repository\CrawledDomainRepository;
use App\Service\DtoValidator;
use App\Service\WebCrawler\DomainLinks;
use App\Service\WebCrawler\UrlPath;
use App\Service\WebCrawler\WebCrawler;;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
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

    /** @var CrawledDomainRepository */
    private $crawledDomainRepository;

    /** @var CrawledDomainPatternRepository */
    private $crawledDomainPatternRepository;

    public function __construct(
        WebCrawler $webCrawler,
        CacheInterface $cache,
        DtoValidator $dtoValidator,
        EntityManagerInterface $entityManager,
        CrawledDomainRepository $crawledDomainRepository,
        CrawledDomainPatternRepository $crawledDomainPatternRepository
    ) {
        $this->webCrawler = $webCrawler;
        $this->cache = $cache;
        $this->dtoValidator = $dtoValidator;
        $this->entityManager = $entityManager;
        $this->crawledDomainRepository = $crawledDomainRepository;
        $this->crawledDomainPatternRepository = $crawledDomainPatternRepository;
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function crawlDomainLinks(CrawlerGetDomainLinks $crawlerGetDomainLinks, ?int $limit = null, bool $continueCrawling = false)
    {
        $this->dtoValidator->validate($crawlerGetDomainLinks);

        $filterCallback = function ($url) use ($crawlerGetDomainLinks) {
            foreach ($crawlerGetDomainLinks->getExcludedPaths() as $excludedPlace) {
                if (preg_match(sprintf('/%s/', preg_quote($excludedPlace, '/')), $url)) {
                    return true;
                }
            }

            return false;
        };

        $domainUrlPath = new UrlPath($crawlerGetDomainLinks->getDomainUrl());

        if ($continueCrawling) {
            /** @var CrawledDomain|null $latestCrawledDomain */
            $latestCrawledDomain = $this->crawledDomainRepository->findLatestCrawledDomain($domainUrlPath->getDomain());

            if (!is_null($latestCrawledDomain)) {
                $domainLinks = new DomainLinks(
                    $latestCrawledDomain->getExtractedLinks(),
                    $latestCrawledDomain->getCrawledLinks(),
                    $latestCrawledDomain->getFileName()
                );
            }
        }

        $domainLinks = $this->webCrawler->getDomainLinks($domainUrlPath, $filterCallback, $limit, $domainLinks ?? null);

        $crawledDomain = (new CrawledDomain())
            ->setDomainName($domainUrlPath->getDomain())
            ->setFileName($domainLinks->getFileName())
            ->setCrawledLinks($domainLinks->getCrawledLinks())
            ->setExtractedLinks($domainLinks->getExtractedLinks());

        $this->entityManager->persist($crawledDomain);
        $this->entityManager->flush();
    }

    public function getDomainLinksByPattern()
    {

    }
}
