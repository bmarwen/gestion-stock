<?php

namespace App\Repository;

use App\Entity\CommandOnLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommandOnLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandOnLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandOnLine[]    findAll()
 * @method CommandOnLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandOnLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandOnLine::class);
    }

    // /**
    //  * @return CommandOnLine[] Returns an array of CommandOnLine objects
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
    public function findOneBySomeField($value): ?CommandOnLine
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
