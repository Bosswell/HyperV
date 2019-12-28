<?php

namespace App\Repository;

use App\Entity\CrawlingHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CrawlingHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrawlingHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrawlingHistory[]    findAll()
 * @method CrawlingHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrawlingHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawlingHistory::class);
    }
}
