<?php

namespace App\Repository;

use App\Entity\CodePromoType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CodePromoType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodePromoType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodePromoType[]    findAll()
 * @method CodePromoType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodePromoTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodePromoType::class);
    }

    // /**
    //  * @return CodePromoType[] Returns an array of CodePromoType objects
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
    public function findOneBySomeField($value): ?CodePromoType
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
