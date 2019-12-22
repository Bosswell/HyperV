<?php

namespace App\Repository;

use App\Entity\CrawlingPattern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CrawlingPattern|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrawlingPattern|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrawlingPattern[]    findAll()
 * @method CrawlingPattern[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrawlingPatternRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawlingPattern::class);
    }

    // /**
    //  * @return CrawlingPattern[] Returns an array of CrawlingPattern objects
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
    public function findOneBySomeField($value): ?CrawlingPattern
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
