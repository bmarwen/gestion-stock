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

    const LAST_PROMOS_ELEMENTS = 8;
    const LAST_PRODUCTS_ELEMENTS = 8;

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

        $imageHomeTopRightSlider1 = $applicationRepository->findOneByName('image_home_top_right_slider1');
        $imageHomeTopRightSlider2 = $applicationRepository->findOneByName('image_home_top_right_slider2');

        $imageHomeBottomLeft1 = $applicationRepository->findOneByName('image_home_bottom_left_1');
        $imageHomeBottomCenter = $applicationRepository->findOneByName('image_home_bottom_center');
        $imageHomeBottomRigth1 = $applicationRepository->findOneByName('image_home_bottom_right_1');
        

        $lastProducts = $productRepository->findBy([],['createdAt' => 'DESC'],DefaultController::LAST_PRODUCTS_ELEMENTS);
        $lastPromos = $promoRepository->getLastPromos(DefaultController::LAST_PROMOS_ELEMENTS);
        
        return $this->render('app/default/index.html.twig', [
            'imageHomeTopLeft1' => $imageHomeTopLeft1,
            'imageHomeTopLeft2' => $imageHomeTopLeft2,
            'imageHomeTopRightSlider1' => $imageHomeTopRightSlider1,
            'imageHomeTopRightSlider2' => $imageHomeTopRightSlider2,
            'imageHomeBottomLeft1' => $imageHomeBottomLeft1,
            'imageHomeBottomCenter' => $imageHomeBottomCenter,
            'imageHomeBottomRigth1' => $imageHomeBottomRigth1,
            'lastProducts' => $lastProducts,
            'lastPromos' => $lastPromos
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
                $maxPriceIntheApplication = 999;
            }
            if($minPriceIntheApplication){
                $priceRangeMin = $minPriceIntheApplication[0]['price'];
                $priceRangeMax = $maxPriceIntheApplication[0]['price'];
            }else{
                $priceRangeMin = 0;
                $priceRangeMax = 999;
            }
           
            $priceMinQuery = (float)$request->query->get('minPrice', $priceRangeMin);
            $priceMaxQuery = (float)$request->query->get('maxPrice', $priceRangeMax);
           
            $priceOrder = $request->query->get('priceOrder', null); //ASC DESC 
            $qbProducts = $productRepository->findByCategoriesAndPriceRange($categorie, $priceMinQuery, $priceMaxQuery, $priceOrder);

            $pagerfanta = new Pagerfanta(new QueryAdapter($qbProducts));
            $pagerfanta->setMaxPerPage(12);
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
            
            return $this->render('app/default/categorie.html.twig', [
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


}
