<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\ApplicationRepository;
use Pagerfanta\Pagerfanta;
use App\Repository\ProductRepository;
use App\Repository\PromoRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{

    const SEARCH_ALL = "ALL";
    const SEARCH_PROMO = "PROMO";
    const SEARCH_NEW = "NEW";
    const NEWS_PRODUCTS_FROM_X_DAYS = 90;
    const LAST_PROMOS_ELEMENTS = 8;
    const MAX_PRICE_IN_RANGE_DEFAULT = 999;
    const MAX_PER_PRODUCTS_PAGE = 18;

    private $requestStack; //session => products

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    /**
     * @Route("/", name="home")
     */
    public function index(ApplicationRepository $applicationRepository, ProductRepository $productRepository, PromoRepository $promoRepository): Response
    {
        $imageHomeTopLeft1 = $applicationRepository->findOneByName('image_home_top_left_1');
        $imageHomeTopLeft2 = $applicationRepository->findOneByName('image_home_top_left_2');


        $imagesHomeTopRightSlider = $applicationRepository->nameStartsWith('image_home_top_right_slider');

        $imageHomeBottomLeft1 = $applicationRepository->findOneByName('image_home_bottom_left_1');
        $imageHomeBottomCenter = $applicationRepository->findOneByName('image_home_bottom_center');
        $imageHomeBottomRigth1 = $applicationRepository->findOneByName('image_home_bottom_right_1');
        
        
        $lastProducts = $productRepository->findLastProductsCreatedSinceXDays(-1, 999999, false)->getQuery()->getResult();
        $lastPromos = $promoRepository->getLastPromos(DefaultController::LAST_PROMOS_ELEMENTS);
        
        return $this->render('app/default/index.html.twig', [
            'imageHomeTopLeft1' => $imageHomeTopLeft1,
            'imageHomeTopLeft2' => $imageHomeTopLeft2,
            'imagesHomeTopRightSlider' => $imagesHomeTopRightSlider,
            'imageHomeBottomLeft1' => $imageHomeBottomLeft1,
            'imageHomeBottomCenter' => $imageHomeBottomCenter,
            'imageHomeBottomRigth1' => $imageHomeBottomRigth1,
            'lastProducts' => $lastProducts,
            'lastPromos' => $lastPromos
        ]);
    }

    /**
     * @Route("/produits/{id}", name="product_show")
     */
    public function showProduits(Product $product = null): Response
    {   
        if(!$product){
            $session = $this->requestStack->getSession();
            $session->set('products',[]);
            throw new NotFoundHttpException("Produit inexistant");
            
        }
        return $this->render('app/default/product.html.twig', [
            'product' => $product,
        ]);
    }


     /**
     * @Route("/categories/{categorie}", name="categories")
     */
    public function showCategories(Request $request, ProductRepository $productRepository, Category $categorie = null): Response
    {
        if($categorie){
            $minPriceIntheApplication = $productRepository->findMaxOrMinPriceByCategory('min', $categorie->getId());
            $maxPriceIntheApplication = $productRepository->findMaxOrMinPriceByCategory('max', $categorie->getId());
            if(!$minPriceIntheApplication){
                $minPriceIntheApplication = 0;
                $maxPriceIntheApplication = self::MAX_PRICE_IN_RANGE_DEFAULT;
            }
            if($minPriceIntheApplication){
                $priceRangeMin = $minPriceIntheApplication[0]['price'];
                $priceRangeMax = $maxPriceIntheApplication[0]['price'];
            }else{
                $priceRangeMin = 0;
                $priceRangeMax = self::MAX_PRICE_IN_RANGE_DEFAULT;
            }
           
            $priceMinQuery = (float)$request->query->get('minPrice', $priceRangeMin);
            $priceMaxQuery = (float)$request->query->get('maxPrice', $priceRangeMax);
           
            $priceOrder = $request->query->get('priceOrder', null); //ASC DESC 
            $qbProducts = $productRepository->findByCategoriesAndPriceRange($categorie, $priceMinQuery, $priceMaxQuery, $priceOrder);
            $pagerfanta = new Pagerfanta(new QueryAdapter($qbProducts));
            $pagerfanta->setMaxPerPage(self::MAX_PER_PRODUCTS_PAGE);

            try{
                $pagerfanta->setCurrentPage($request->query->get('page', 1));
            }catch(\Exception $e){
                $request->query->set('page', 1);
                return $this->showCategories($request,$productRepository,$categorie);
            }
            return $this->render('app/default/search.html.twig', [
                'categorie' => $categorie,
                'priceRangeMin' => $priceRangeMin,
                'priceRangeMax' => $priceRangeMax,
                'prices' => ['minPrice' => $priceMinQuery, 'maxPrice' => $priceMaxQuery],
                'productsPager' => $pagerfanta
            ]);
        }
        throw new NotFoundHttpException("Catégorie n'existe pas.");
    }

    /**
     * @Route("/search", name="search_show")
     */
    public function search(Request $request, ProductRepository $productRepository): Response
    {   
        $priceOrder = $request->query->get('priceOrder', null); //ASC DESC 
        $search = $request->query->has('search') ? $request->query->get('search') : null;//for post param
        $searchedWord = $request->request->has('searchedWord') ? $request->request->get('searchedWord') : null;//for post param
        if (null === $searchedWord) {//for get param
            $searchedWord = $request->query->has('searchedWord') ? $request->query->get('searchedWord') : null;
        }
        $priceRangeMin = 0;
        
        $priceRangeMax = self::MAX_PRICE_IN_RANGE_DEFAULT;
        if (null != $searchedWord) {
            $minPriceIntheApplication = $productRepository->findMaxOrMinPriceByName('min',$searchedWord);
            $maxPriceIntheApplication = $productRepository->findMaxOrMinPriceByName('max',$searchedWord);
            if($minPriceIntheApplication){
                $priceRangeMin = $minPriceIntheApplication[0]['price'];
                $priceRangeMax = $maxPriceIntheApplication[0]['price'];
            }
        }
        $priceMinQuery = (float)$request->query->get('minPrice', $priceRangeMin);
        $priceMaxQuery = (float)$request->query->get('maxPrice', $priceRangeMax);
        
        $typeOfSearch = $request->query->get("search",self::SEARCH_ALL); //type could be all(by search bar) or promo(search for promo articles) or new product(search for new articles)
        if ($typeOfSearch === strtoupper(self::SEARCH_NEW)) {
            $qbProducts = $productRepository->findLastProductsCreatedSinceXDays($priceMinQuery, $priceMaxQuery, $priceOrder);
            $minPriceIntheApplication = $productRepository->findMaxOrMinPriceByName('min',strtoupper(self::SEARCH_NEW));
            $maxPriceIntheApplication = $productRepository->findMaxOrMinPriceByName('max',strtoupper(self::SEARCH_NEW));
            if($minPriceIntheApplication){
                $priceRangeMin = $minPriceIntheApplication[0]['price'];
                $priceRangeMax = $maxPriceIntheApplication[0]['price'];
            }
        }elseif ($typeOfSearch === strtoupper(self::SEARCH_PROMO)) {
            $qbProducts = $productRepository->findByInPromoNow($priceMinQuery, $priceMaxQuery, $priceOrder);
            $minPriceIntheApplication = $productRepository->findMaxOrMinPriceByName('min',strtoupper(self::SEARCH_PROMO));
            $maxPriceIntheApplication = $productRepository->findMaxOrMinPriceByName('max',strtoupper(self::SEARCH_PROMO));
            if($minPriceIntheApplication){
                $priceRangeMin = $minPriceIntheApplication[0]['price'];
                $priceRangeMax = $maxPriceIntheApplication[0]['price'];
            }
        }else{ //all
            if ($searchedWord == null) {
                $qbProducts = null;
            } else {
                $qbProducts = $productRepository->findByNameAndPriceRange($searchedWord, $priceMinQuery, $priceMaxQuery, $priceOrder);
            }
        }
        if(null != $qbProducts){
            $pagerfanta = new Pagerfanta(new QueryAdapter($qbProducts));
            $pagerfanta->setMaxPerPage(self::MAX_PER_PRODUCTS_PAGE);
            try{
                $pagerfanta->setCurrentPage($request->query->get('page', 1));
            }catch(\Exception $e){
                $request->query->set('page', 1);
                return $this->search($request,$productRepository);
            }
        }else {
            $pagerfanta = [];
        }
        $param = [];
        if (null != $search) {
            $param = ['search' => $search];
        } else if (null != $searchedWord) {
            $param = ['searched' => $searchedWord];
        }
        
        return $this->render('app/default/search.html.twig', [
                'priceRangeMin' => $priceRangeMin,
                'priceRangeMax' => $priceRangeMax,
                'prices' => ['minPrice' => $priceMinQuery, 'maxPrice' => $priceMaxQuery],
                'productsPager' => $pagerfanta,
                'searchParam' => $param
        ]);
    }

    /**
     * @Route("/apropos", name="a_propos")
     */
    public function apropos(ApplicationRepository $applicationRepository): Response
    {
        return $this->render('app/default/a_propos.html.twig', [
            
        ]);
    }



}
