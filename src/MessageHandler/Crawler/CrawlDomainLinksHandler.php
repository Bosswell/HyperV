<?php

namespace App\MessageHandler\Crawler;

use App\Entity\CrawlingHistory;
use App\Entity\Domain;
use App\Lock\LockFactory;
use App\Message\Crawler\CrawlDomainLinksMessage;
use App\Repository\DomainRepository;
use App\WebCrawler\Utils\DomainLinks;
use App\WebCrawler\Utils\UrlPath;
use App\WebCrawler\WebCrawlerException;
use App\WebCrawler\WebCrawlerFacade;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;


class CrawlDomainLinksHandler implements MessageHandlerInterface
{
    /** @var WebCrawlerFacade */
    private $webCrawlerFacade;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var CacheInterface */
    private $cache;

    /** @var DomainRepository */
    private $domainRepository;

    public function __construct(
        WebCrawlerFacade $webCrawlerFacade,
        EntityManagerInterface $entityManager,
        CacheInterface $cache,
        DomainRepository $domainRepository
    ) {
        $this->webCrawlerFacade = $webCrawlerFacade;
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->domainRepository = $domainRepository;
    }

    /**
     * @throws WebCrawlerException
     * @throws Throwable
     * @param CrawlDomainLinksMessage $crawlDomainLinksMessage
     */
    public function __invoke(CrawlDomainLinksMessage $crawlDomainLinksMessage)
    {
        $domainUrlPath = new UrlPath($crawlDomainLinksMessage->getDomainUrl());

        $lock = LockFactory::create('domainCrawler');

        if (!$lock->acquire()) {
            throw new WebCrawlerException(sprintf('Crawler is already in process of crawling given domain [%s]', $domainUrlPath->getDomain()));
        }

        try {
            $domain = $this->domainRepository->findOneBy(['name' => $domainUrlPath->getDomain()]);
            if (is_null($domain)) {
                $domain = (new Domain())
                    ->setName($domainUrlPath->getDomain());
                $this->entityManager->persist($domain);
            }

            // If we decided to continue crawling one of the previously crawled entry
            if (!is_null($crawlDomainLinksMessage->getCrawlingHistoryId())) {
                /** @var CrawlingHistory|null $crawlingHistory */
                $crawlingHistory = $this->entityManager->find(CrawlingHistory::class, $crawlDomainLinksMessage->getCrawlingHistoryId());

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

            $domainLinks = $this->webCrawlerFacade->getDomainLinks(
                $domainUrlPath,
                $this->getCallableFilter($crawlDomainLinksMessage),
                $crawlDomainLinksMessage->getLimit(),
                $domainLinks ?? null
            );

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
        } finally {
            $lock->release();
        }
    }

    private function getCallableFilter(CrawlDomainLinksMessage $crawlDomainLinksMessage): callable
    {
        return function ($url) use ($crawlDomainLinksMessage) {
            foreach ($crawlDomainLinksMessage->getExcludedPaths() as $excludedPlace) {
                if (preg_match(sprintf('/%s/', preg_quote($excludedPlace, '/')), $url)) {
                    return true;
                }
            }

            return false;
        };
    }
}