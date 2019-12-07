<?php

namespace App\Repository;

use App\Entity\CrawledDomainPattern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CrawledDomainPattern|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrawledDomainPattern|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrawledDomainPattern[]    findAll()
 * @method CrawledDomainPattern[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrawledDomainPatternRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawledDomainPattern::class);
    }

    // /**
    //  * @return CrawledDomainPattern[] Returns an array of CrawledDomainPattern objects
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
    public function findOneBySomeField($value): ?CrawledDomainPattern
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
