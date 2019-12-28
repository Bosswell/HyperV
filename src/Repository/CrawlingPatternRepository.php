<?php

namespace App\Repository;

use App\Entity\CrawlingPattern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\Persistence\ManagerRegistry;

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

    /**
     * @return array|null [pattern, urls_quantity]
     * @throws DBALException
     * @param string $pattern
     */
    public function findOneByPattern(string $pattern): ?array
    {
        $sql = ' 
            SELECT cp.pattern,
                   cp.urls_quantity
            FROM crawling_pattern cp
            INNER JOIN crawling_pattern_crawling_history cpch 
            ON cpch.crawling_pattern_id = cp.id
            WHERE cp.pattern = :pattern
        ';

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(':pattern', $pattern);
        $result = $stmt->fetch();

        return $result ? $result : null;
    }
}
