<?php

namespace App\Repository;

use App\Entity\OtherSales;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OtherSales|null find($id, $lockMode = null, $lockVersion = null)
 * @method OtherSales|null findOneBy(array $criteria, array $orderBy = null)
 * @method OtherSales[]    findAll()
 * @method OtherSales[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OtherSalesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OtherSales::class);
    }


    public function getExpensesOfWhichDay(\DateTime $whichDay)
    {
        $startOfWhichDay = new \DateTime($whichDay->setTime(0,0)->format('y-m-d H:i'));
        $endOfWhichDay = new \DateTime($whichDay->setTime(23,59,59)->format('y-m-d H:i'));
        
        return $this->createQueryBuilder('os')
            ->where('os.createdAt >= :startOfWhichDay and os.createdAt <= :endOfWhichDay')
            ->setParameters(
                [
                    'startOfWhichDay' => $startOfWhichDay,
                    'endOfWhichDay' => $endOfWhichDay
                ])
            ->orderBy('os.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getTotalExpensesOfWhichDay(\DateTime $whichDay)
    {
        $startOfWhichDay = new \DateTime($whichDay->setTime(0,0)->format('y-m-d H:i'));
        $endOfWhichDay = new \DateTime($whichDay->setTime(23,59,59)->format('y-m-d H:i'));
        
        return $this->createQueryBuilder('os')
            ->where('os.createdAt >= :startOfWhichDay and os.createdAt <= :endOfWhichDay')
            ->setParameters(
                [
                    'startOfWhichDay' => $startOfWhichDay,
                    'endOfWhichDay' => $endOfWhichDay
                ])
            ->select('SUM(os.price) as totalExpenses')
            ->orderBy('os.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return OtherSales[] Returns an array of OtherSales objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OtherSales
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
