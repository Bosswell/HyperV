<?php

namespace App\Repository;

use App\Entity\CrawledDomainHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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

    // /**
    //  * @return CrawledDomainHistory[] Returns an array of CrawledDomainHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CrawledDomainHistory
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
