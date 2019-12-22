<?php

namespace App\WebCrawler;

use App\Dto\Crawler\CrawlerGetDomainLinks;
use App\Entity\CrawlingHistory;
use App\Entity\Domain;
use App\Exception\ValidationException;
use App\Repository\DomainRepository;
use App\Service\DtoValidator;
use App\WebCrawler\Utils\DomainLinks;
use App\WebCrawler\Utils\UrlPath;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct(
        WebCrawler $webCrawler,
        CacheInterface $cache,
        DtoValidator $dtoValidator,
        EntityManagerInterface $entityManager,
        DomainRepository $domainRepository
    ) {
        $this->webCrawler = $webCrawler;
        $this->cache = $cache;
        $this->dtoValidator = $dtoValidator;
        $this->entityManager = $entityManager;
        $this->domainRepository = $domainRepository;
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function crawlDomainLinks(CrawlerGetDomainLinks $crawlerGetDomainLinks, ?int $limit = null, ?int $crawlingHistoryId = null)
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

    public function getDomainLinksByPattern()
    {

    }

    /**
     * @return Collection|CrawlingHistory[]
     * @param string $domain
     */
    public function getDomainCrawlingHistory(string $domain): ?Collection
    {
        $domain = $this->domainRepository->findOneBy(['name' => $domain]);

        return $domain->getCrawlingHistory() ?? null;
    }
}
