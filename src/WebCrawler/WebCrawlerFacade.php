<?php

namespace App\WebCrawler;

use App\Dto\Crawler\CrawlDomainLinksDto;
use App\Dto\Crawler\FilterCrawledLinksDto;
use App\Entity\CrawlingHistory;
use App\Entity\CrawlingPattern;
use App\Entity\Domain;
use App\Exception\ValidationException;
use App\Repository\CrawlingPatternRepository;
use App\Repository\DomainRepository;
use App\Service\CrawlerResourcesManager;
use App\Service\DtoValidator;
use App\WebCrawler\Utils\DomainLinks;
use App\WebCrawler\Utils\UrlPath;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Psr\Cache\InvalidArgumentException;
use SplFileObject;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;


final class WebCrawlerFacade
{
    /** @var WebCrawler */
    private $webCrawler;

    /** @var CacheInterface */
    private $cache;

    /** @var DtoValidator */
    private $dtoValidator;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var DomainRepository */
    private $domainRepository;

    /** @var CrawlingPatternRepository */
    private $crawlingPatternRepository;

    /** @var CrawlerResourcesManager */
    private $crawledResourceManager;

    public function __construct(
        WebCrawler $webCrawler,
        CacheInterface $cache,
        DtoValidator $dtoValidator,
        EntityManagerInterface $entityManager,
        DomainRepository $domainRepository,
        CrawlingPatternRepository $crawlingPatternRepository,
        CrawlerResourcesManager $crawlerResourcesManager
    ) {
        $this->webCrawler = $webCrawler;
        $this->cache = $cache;
        $this->dtoValidator = $dtoValidator;
        $this->entityManager = $entityManager;
        $this->domainRepository = $domainRepository;
        $this->crawledResourceManager = $crawlerResourcesManager;
        $this->crawlingPatternRepository = $crawlingPatternRepository;
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function crawlDomainLinks(CrawlDomainLinksDto $crawlerGetDomainLinks, ?int $limit = null, ?int $crawlingHistoryId = null)
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

        $domain = $this->domainRepository->findOneBy(['name' => $domainUrlPath->getDomain()]);

        if (is_null($domain)) {
            $domain = (new Domain())
                ->setName($domainUrlPath->getDomain());
            $this->entityManager->persist($domain);
        }

        if (!is_null($crawlingHistoryId)) {
            /** @var CrawlingHistory|null $crawlingHistory */
            $crawlingHistory = $this->entityManager->find(CrawlingHistory::class, $crawlingHistoryId);

            if (!is_null($crawlingHistory)) {
                if ($crawlingHistory->getExtractedLinks() === $crawlingHistory->getCrawledLinks()) {
                    return;
                }

                $domainLinks = new DomainLinks(
                    $crawlingHistory->getExtractedLinks(),
                    $crawlingHistory->getCrawledLinks(),
                    $crawlingHistory->getFileName()
                );
            }
        }

        $domainLinks = $this->webCrawler->getDomainLinks($domainUrlPath, $filterCallback, $limit, $domainLinks ?? null);

        if (isset($crawlingHistory) && !is_null($crawlingHistory)) {
            $crawlingHistory
                ->setCrawledLinks($domainLinks->getCrawledLinks())
                ->setExtractedLinks($domainLinks->getExtractedLinks());
        } else {
            $crawlingHistory = (new CrawlingHistory())
                ->setDomain($domain)
                ->setFileName($domainLinks->getFileName())
                ->setCrawledLinks($domainLinks->getCrawledLinks())
                ->setUpdatedAt(new \DateTime('now'))
                ->setExtractedLinks($domainLinks->getExtractedLinks());

            $this->entityManager->persist($crawlingHistory);
        }

        $this->entityManager->flush();
    }

    /**
     * @return SplFileObject
     * @throws EntityNotFoundException
     * @throws Exception
     * @param FilterCrawledLinksDto $filterDomainLinksDto
     */
    public function getDomainLinksByPattern(FilterCrawledLinksDto $filterDomainLinksDto, bool $refresh = false): SplFileObject
    {
        $this->dtoValidator->validate($filterDomainLinksDto);

        /** @var CrawlingHistory|null $crawlingHistory */
        $crawlingHistory = $this->entityManager->find(
            CrawlingHistory::class,
            $filterDomainLinksDto->getCrawlingHistoryId()
        );

        if (is_null($crawlingHistory)) {
            throw new EntityNotFoundException('Given domain has not been found');
        }

        $domainName = $crawlingHistory->getDomain()->getName();
        if ($refresh) {
            $this->crawledResourceManager->removeFilteredDomainLinks($filterDomainLinksDto, $domainName);
        }

        $filteredLinksFile = $this->crawledResourceManager->getFilteredDomainLinksFile(
            $domainName,
            $filterDomainLinksDto->getEncodedPattern()
        );

        if ($filteredLinksFile->getSize() > 0) {
            return $filteredLinksFile;
        }

        $this->filterDomainLinksByPattern($filterDomainLinksDto, $filteredLinksFile, $crawlingHistory);

        return $filteredLinksFile;
    }

    /**
     * @throws Exception
     * @param SplFileObject $filteredLinksFile
     * @param CrawlingHistory $crawlingHistory
     * @param FilterCrawledLinksDto $filterDomainLinksDto
     */
    private function filterDomainLinksByPattern(
        FilterCrawledLinksDto $filterDomainLinksDto,
        SplFileObject $filteredLinksFile,
        CrawlingHistory $crawlingHistory
    ): void {
        $filteredLinksQuantity = $this->crawledResourceManager->filterDomainLinksByPattern(
            $filterDomainLinksDto->getPattern(),
            $filteredLinksFile,
            $crawlingHistory
        );

        $crawlingPattern = new CrawlingPattern();
        $crawlingPattern
            ->setPattern($filterDomainLinksDto->getPattern())
            ->setUrlsQuantity($filteredLinksQuantity);

        $crawlingHistory->addCrawlingPattern($crawlingPattern);
        $this->entityManager->persist($crawlingPattern);
        $this->entityManager->flush();
    }
}
