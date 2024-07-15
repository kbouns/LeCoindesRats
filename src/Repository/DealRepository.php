<?php

namespace App\Repository;

use App\Entity\Deal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deal>
 */
class DealRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deal::class);
    }

    /**
     * @return Deal[] Returns an array of Deal objects ordered by ID in ascending order
     */
    public function findDealsAscending(): array
    {
        return $this->createQueryBuilder('deals')
            ->orderBy('deals.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $searchTerm
     * @return Deal[]
     */
    public function searchDeals(string $searchTerm): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.title LIKE :searchTerm OR d.description LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('d.publicationdate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?Deal
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
