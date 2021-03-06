<?php

namespace App\Repository;

use App\Entity\Product;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{

    const PRICE_TTC_SQL = "(p.purchacePriceUnHt * (p.tva /100) + p.purchacePriceUnHt)";
    const PRICE_TTC_GAIN_SQL = "( " . ProductRepository::PRICE_TTC_SQL . " + (p.gain * " . ProductRepository::PRICE_TTC_SQL . "/100))" ;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByHowManyLessThan(int $number){
        return $this->createQueryBuilder("p")
                    ->where("p.howMany <= :number")
                    ->setParameter("number",$number)
                    ->getQuery()
                    ->getResult();
                    
    }

    public function findByExpirationDateLessThan($date){
        return $this->createQueryBuilder("p")
            ->where("p.expirationDate <= :date")
            ->setParameter("date",$date)
            ->getQuery()
            ->getResult();
    }


    /**
     * $minOrMax could be 'min' or 'max'
     */
    public function findMaxOrMinPriceByCategory(string $minOrMax, $categoryId){ 
        $order = $minOrMax === 'min' ? 'ASC' : 'DESC'; 
        
        return $this->createQueryBuilder("p")
                    ->select(ProductRepository::PRICE_TTC_GAIN_SQL . ' as price, MAX(' . ProductRepository::PRICE_TTC_GAIN_SQL . ') AS HIDDEN max_score')
                    ->where('p.category = :categoryId')
                    ->setParameter('categoryId',$categoryId)
                    ->groupBy('p')
                    ->setMaxResults(1)
                    ->orderBy('max_score', $order)
                    ->getQuery()
                    ->getResult();

    }

    /**
     * $minOrMax could be 'min' or 'max'
     */
    public function findMaxOrMinPriceByName(string $minOrMax, $searchedWord){ 
        $order = $minOrMax === 'min' ? 'ASC' : 'DESC'; 
        
        return $this->findBySearchedWord($searchedWord)
                    ->select(ProductRepository::PRICE_TTC_GAIN_SQL . ' as price, MAX(' . ProductRepository::PRICE_TTC_GAIN_SQL . ') AS HIDDEN max_score')
                    ->groupBy('p')
                    ->setMaxResults(1)
                    ->orderBy('max_score', $order)
                    ->getQuery()
                    ->getResult();

    }


    public function findByCategoriesAndPriceRange($categoryId, $minPrice, $maxPrice, $priceOrder){
        $qb = $this->createQueryBuilder("p")
                    ->where('p.category = :categoryId AND ' . ProductRepository::PRICE_TTC_GAIN_SQL . ' >= :minPrice AND ' . ProductRepository::PRICE_TTC_GAIN_SQL . ' <= :maxPrice')
                    ->setParameters(['categoryId' => $categoryId, 'minPrice' => $minPrice, 'maxPrice' => $maxPrice])
                    ->groupBy('p');
                    if($priceOrder){
                        $qb->orderBy(ProductRepository::PRICE_TTC_GAIN_SQL, $priceOrder);
                    }else{
                        $qb->orderBy('p.createdAt', 'DESC');
                    }
        return $qb;

    }

    public function findByNameAndPriceRange($searchedWord, $minPrice, $maxPrice, $priceOrder){
        $qb = $this->findBySearchedWord($searchedWord, $minPrice, $maxPrice)
                    ->andWhere(ProductRepository::PRICE_TTC_GAIN_SQL . ' >= :minPrice AND ' . ProductRepository::PRICE_TTC_GAIN_SQL . ' <= :maxPrice')
                    ->groupBy('p');
                    if($priceOrder){
                        $qb->orderBy(ProductRepository::PRICE_TTC_GAIN_SQL, $priceOrder);
                    }else{
                        $qb->orderBy('p.createdAt', 'DESC');
                    }
                   
        return $qb;

    }

    public function findByInPromoNow($minPrice, $maxPrice, $priceOrder){
        $now = new \DateTime();
        $qb = $this->createQueryBuilder("p")
                    ->join('p.promos','pr')
                    ->where('pr.isEnabled = true AND pr.startsAt <:now and pr.expiresAt > :now')
                    ->andWhere(ProductRepository::PRICE_TTC_GAIN_SQL . ' >= :minPrice AND ' . ProductRepository::PRICE_TTC_GAIN_SQL . ' <= :maxPrice')
                    ->setParameters(['now' => $now, 'minPrice' => $minPrice, 'maxPrice' => $maxPrice])
                    ->groupBy('p');
                    if($priceOrder){
                        $qb->orderBy(ProductRepository::PRICE_TTC_GAIN_SQL, $priceOrder);
                    }else{
                        $qb->orderBy('p.createdAt', 'DESC');
                    }

        return $qb;
    }

    public function findProductCreatedSinceXDays($xDays = 15, $minPrice, $maxPrice, $priceOrder){
        $nowSubXDays = new \DateTime();
        $xDays = "P" . $xDays . "D";
        $nowSubXDays->sub(new \DateInterval($xDays)); 
        $qb = $this->createQueryBuilder("p")
                    ->where('p.createdAt >= :nowSubXDays')
                    ->andWhere(ProductRepository::PRICE_TTC_GAIN_SQL . ' >= :minPrice AND ' . ProductRepository::PRICE_TTC_GAIN_SQL . ' <= :maxPrice')
                    ->setParameters(['nowSubXDays' => $nowSubXDays, 'minPrice' => $minPrice, 'maxPrice' => $maxPrice])
                    ->groupBy('p');
                    if($priceOrder){
                        $qb->orderBy(ProductRepository::PRICE_TTC_GAIN_SQL, $priceOrder);
                    }else{
                        $qb->orderBy('p.createdAt', 'DESC');
                    }
                    
        return $qb;
    }
    
    public function findBySearchedWord($searchedWord, $minPrice = null, $maxPrice = null){
        if ($minPrice === 0 || null !== $minPrice) {
            $params['minPrice'] = $minPrice;
        }
        if (null != $maxPrice) {
            $params['maxPrice'] = $maxPrice;
        }
        $params['name'] = '%'. strtoupper($searchedWord)  . '%';
       
        $qb = $this->createQueryBuilder("p")
                    ->where('p.name LIKE UPPER(:name)')
                    ->setParameters($params);
                  
        return $qb;
    }


    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
