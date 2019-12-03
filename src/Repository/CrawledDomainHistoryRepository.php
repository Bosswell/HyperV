<?php

namespace App\Repository;

use App\Entity\CrawledDomainHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method CrawledDomainHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrawledDomainHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrawledDomainHistory[]    findAll()
 * @method CrawledDomainHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrawledDomainHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawledDomainHistory::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findLatestCrawledLinks(string $domainName): ?int
    {
        /** @var CrawledDomainHistory $history */
        $history =  $this->createQueryBuilder('c')
            ->andWhere('c.domainName = :domainName')
            ->setParameter('domainName', $domainName)
            ->orderBy('c.cratedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $history->getCrawledUrls() ?? null;
    }
}
