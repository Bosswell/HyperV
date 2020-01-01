<?php

namespace App\MessageHandler\Crawler;

use App\Entity\CrawlingHistory;
use App\Entity\CrawlingPattern;
use App\Message\Crawler\FilterCrawledLinksMessage;
use App\Repository\DomainRepository;
use App\Service\ResourcesManager;
use App\WebCrawler\WebCrawlerFacade;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Exception;


class FilterCrawledLinksHandler implements MessageHandlerInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ResourcesManager */
    private $resourcesManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ResourcesManager $resourcesManager
    ) {
        $this->entityManager = $entityManager;
        $this->resourcesManager = $resourcesManager;
    }

    /**
     * @throws EntityNotFoundException
     * @throws Exception
     * @param FilterCrawledLinksMessage $filterCrawledLinksMessage
     */
    public function __invoke(FilterCrawledLinksMessage $filterCrawledLinksMessage)
    {
        /** @var CrawlingHistory|null $crawlingHistory */
        $crawlingHistory = $this->entityManager->find(
            CrawlingHistory::class,
            $filterCrawledLinksMessage->getCrawlingHistoryId()
        );

        if (is_null($crawlingHistory)) {
            throw new EntityNotFoundException('Given domain has not been found');
        }

        $domainName = $crawlingHistory->getDomain()->getName();
        if ($filterCrawledLinksMessage->isRefresh()) {
            $this->resourcesManager->removeFilteredDomainLinks($filterCrawledLinksMessage->getEncodedPattern(), $domainName);
        }

        $filteredLinksFile = $this->resourcesManager->getFilteredDomainLinksFile(
            $domainName,
            $filterCrawledLinksMessage->getEncodedPattern()
        );

        if ($filteredLinksFile->getSize() > 0) {
            return;
        }

        $filteredLinksQuantity = $this->resourcesManager->filterDomainLinksByPattern(
            $filterCrawledLinksMessage->getPattern(),
            $filteredLinksFile,
            $crawlingHistory
        );

        if ($filteredLinksFile->getSize() === 0) {
            return;
        }

        $crawlingPattern = new CrawlingPattern();
        $crawlingPattern
            ->setPattern($filterCrawledLinksMessage->getPattern())
            ->setUrlsQuantity($filteredLinksQuantity);

        $crawlingHistory->addCrawlingPattern($crawlingPattern);
        $this->entityManager->persist($crawlingPattern);
        $this->entityManager->flush();
    }
}