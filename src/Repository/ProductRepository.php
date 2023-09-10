<?php

namespace App\Repository;

use App\Controller\DefaultController;
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

    public function findLastProductsCreatedSinceXDays($minPrice, $maxPrice, $priceOrder){
        $nowSubXDays = new \DateTime();
        $xDays = "P" . DefaultController::NEWS_PRODUCTS_FROM_X_DAYS . "D";
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
    
    public function findBySearchedWord($searchedWord, $minPrice = null, $maxPrice = null)
    {
        $qb = $this->createQueryBuilder("p");
        if ($minPrice === 0 || null !== $minPrice) {
            $params['minPrice'] = $minPrice;
        }
        if (null != $maxPrice) {
            $params['maxPrice'] = $maxPrice;
        }
        $productsInPromo = '';
        if ($searchedWord === DefaultController::SEARCH_PROMO) {// products in promo
            $productsInPromo = 'pr.isEnabled = true AND pr.startsAt <:now and pr.expiresAt > :now ';
            $params['now'] = new \DateTime();
            $qb->join('p.promos','pr');
            $qb->where($productsInPromo);
        } else if ($searchedWord === DefaultController::SEARCH_NEW) {// products new created past x days
           return $this->findLastProductsCreatedSinceXDays(0,9999999,'ASC');
        } else {
            $params['name'] = '%'. strtoupper($searchedWord)  . '%';
            $qb->where('p.name LIKE UPPER(:name)');
        }
        
        $qb->setParameters($params);
                  
        return $qb;
    }

    public function findByNameOrCode($searchValue)
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.name) LIKE :searchValue')
            ->orWhere('LOWER(p.code) LIKE :searchValue')
            ->setParameter('searchValue', '%' . strtolower($searchValue) . '%')
            ->getQuery()
            ->getResult();
    }
}
