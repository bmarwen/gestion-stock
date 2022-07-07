<?php

namespace App\Repository;

use App\Entity\CashRegister;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CashRegister|null find($id, $lockMode = null, $lockVersion = null)
 * @method CashRegister|null findOneBy(array $criteria, array $orderBy = null)
 * @method CashRegister[]    findAll()
 * @method CashRegister[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CashRegisterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CashRegister::class);
    }

    

    // /**
    //  * @return CashRegister[] Returns an array of CashRegister objects
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
    public function findOneBySomeField($value): ?CashRegister
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
