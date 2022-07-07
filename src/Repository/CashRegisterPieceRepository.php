<?php

namespace App\Repository;

use App\Entity\CashRegisterPiece;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CashRegisterPiece|null find($id, $lockMode = null, $lockVersion = null)
 * @method CashRegisterPiece|null findOneBy(array $criteria, array $orderBy = null)
 * @method CashRegisterPiece[]    findAll()
 * @method CashRegisterPiece[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CashRegisterPieceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CashRegisterPiece::class);
    }

    /** 
      * @return CashRegisterPiece[] Returns an array of CashRegister objects
      */
      public function getWhichDayBeforeTheWhichdaySelectedInCashRegister(\DateTime $whichdaySelected)
      {
          return $this->createQueryBuilder('c')
              ->select('MAX(c.whichday) as whichdayBefore')
              ->where('c.whichday < :whichdaySelected')
              ->setParameter('whichdaySelected', $whichdaySelected)
              ->setMaxResults(1)
              ->getQuery()
              ->getResult()
          ;
      }

    // /**
    //  * @return CashRegisterPiece[] Returns an array of CashRegisterPiece objects
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
    public function findOneBySomeField($value): ?CashRegisterPiece
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
