<?php

namespace App\Repository;

use App\Entity\CrawledDomain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method CrawledDomain|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrawledDomain|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrawledDomain[]    findAll()
 * @method CrawledDomain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrawledDomainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawledDomain::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findLatestCrawledLinks(string $domainName): ?int
    {
        /** @var CrawledDomain $history */
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
