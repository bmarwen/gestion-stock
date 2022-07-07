<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CodePromoRepository;
use Pagerfanta\Pagerfanta;
use App\Repository\ProductRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/panier",name="basket")
 */
class BasketController extends AbstractController
{

    private $requestStack; //session => products

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/", name="_show")
     */
    public function showBasket(ProductRepository $productRepository): Response
    {   
        $session = $this->requestStack->getSession();
        $products = $session->get('products',[]);
        $productsFromDb = [];
        
        //add product entity to session
        foreach ($products as $product) {
            if ($prodFromDb = $productRepository->findOneById($product['id'])){
                $productsFromDb[$prodFromDb->getId()] = $prodFromDb;
            } 
        }

        return $this->render('app/basket/basket.html.twig', [
            'products' => $products,
            'productsFromDb' => $productsFromDb
        ]);
    }

    /**
     * @Route("/add/{id}", name="_add")
     */
    public function addBasket(Product $product = null, Request $request): Response
    {   
        $session = $this->requestStack->getSession();
        $products = $session->get('products',[]);
        if($product){
            //check whether the product exist before inserting
            $indexProductInProductsBasket = $this->indexProductInProductsBasket($product->getId(), $products);
            if($indexProductInProductsBasket === -1){
                $products[] = [
                    'id' => $product->getId(), 
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'tva' => $product->getTva(),
                    'gain' => $product->getGain(),
                    'imageFilePath' => $product->getFilename(), 
                    'qty' => (int)$request->query->get('qty',1)
                ];
            }else{
                $products[$indexProductInProductsBasket]['qty'] = $products[$indexProductInProductsBasket]['qty'] + (int)$request->query->get('qty',1);
            }
            
        }
        $session->set('products',$products);
        return $this->redirectToRoute("basket_show");
        
    }

    /**
     * @Route("/remove/{id}", name="_remove")
     */
    public function removeBasket(Product $product = null): Response
    {   
        $session = $this->requestStack->getSession();
        $products = $session->get('products',[]);
        $indexProductInProductsBasket = $this->indexProductInProductsBasket($product->getId(), $products);
        unset($products[$indexProductInProductsBasket]);
        $session->set('products',$products);
        return $this->redirectToRoute("basket_show");
    }

    /**
     * @Route("/qty_update/{id}", name="_qty_update",options={"expose"=true})
     */
    public function qtyBasketUpdateQty(Product $product = null, Request $request, CodePromoRepository $codePromoRepository): Response
    {   
        
        $qty = (int)$request->query->get('qty',1);//1 for up 0 for down
        $session = $this->requestStack->getSession();
        $products = $session->get('products',[]);
        if($product){
            $indexProductInProductsBasket = $this->indexProductInProductsBasket($product->getId(), $products);
            if($indexProductInProductsBasket != -1){//exist
                $products[$indexProductInProductsBasket]['qty'] = $qty;
                $session->set('products',$products);
                if ($session->has('codepromo_id')) { // gestion code promo on va l'eliminé s'il le prix min n'est plus respecté
                    $codePromoId = $session->get('codepromo_id');
                    $codePromoEntity = $codePromoRepository->findOneBy(['id' => $codePromoId]);
                    if (null != $codePromoEntity && $codePromoEntity->isValid()) {
                        if (!$this->isCodePromoRespectingMinPrice($products, $codePromoEntity)) {
                            $session->remove('codepromo_percent');
                            $session->remove('codepromo_id');    
                        }
                    }
                }
                return $this->redirectToRoute("basket_show");
            }
        }
        
        return $this->redirectToRoute("basket_show");
    }

    /**
     * @Route("/qty/{id}", name="_updownqty")
     */
    public function qtyBasketUpOrDown(Product $product = null, Request $request): Response
    {   
        $upOrDownQty = $request->query->get('updownqty',1);//1 for up 0 for down
        $session = $this->requestStack->getSession();
        $products = $session->get('products',[]);
        $indexProductInProductsBasket = $this->indexProductInProductsBasket($product->getId(), $products);
        if($indexProductInProductsBasket != -1){
            $qty = $products[$indexProductInProductsBasket]['qty'];
            if($upOrDownQty == 1){
                $products[$indexProductInProductsBasket]['qty'] = $qty + 1; 
            }else{
                if($qty > 0){
                    $products[$indexProductInProductsBasket]['qty'] = $qty - 1; 
                }
            }
            $session->set('products', $products);
        }
        
        return $this->redirectToRoute("basket_show");
    }

    /**
     * @Route("/applyCodePromo", name="_apply_code_promo", methods="POST")
     */
    public function applyCodePromo(Request $request, CodePromoRepository $codePromoRepository){
        $session = $this->requestStack->getSession();
        $products = $session->get('products',[]);
        
        if ($session->has('codepromo_id')) {
            $this->addFlash('warning_apply_codepromo', 'Code promo déjà appliqué');
            return $this->redirectToRoute("basket_show");
        }
        if ($codePromo = $request->request->get('codepromo')) {
            $codePromoEntity = $codePromoRepository->findOneBy(['code' => $codePromo]);
            if (null != $codePromoEntity && $codePromoEntity->isValid()) {
                //check price min of code promo
                if (!$this->isCodePromoRespectingMinPrice($products, $codePromoEntity)) {
                    $session->remove('codepromo_percent');
                    $session->remove('codepromo_id');
                    $this->addFlash('error_apply_codepromo', 'Code promo invalide');
                    return $this->redirectToRoute("basket_show");
                }
                
                $this->addFlash('success_apply_codepromo', 'Code promo Appliqué');
                $session->set('codepromo_percent', $codePromoEntity->getPercent());
                $session->set('codepromo_id', $codePromoEntity->getId());
                return $this->redirectToRoute("basket_show");
            }
        }
        $this->addFlash('error_apply_codepromo', 'Code promo invalide');
        return $this->redirectToRoute("basket_show");
    }

    private function isCodePromoRespectingMinPrice($products, $codePromoEntity) {
        $totalBasket = 0;
        foreach ($products as $product) {
            $totalBasket += $product['price'] * $product['qty'];
        }
        if ($codePromoEntity->getMinPrice() >= $totalBasket) {
            return false;
        }
        return true;
    }

    private function indexProductInProductsBasket($id, $products){
        foreach($products as $index => $element){
            if($element['id'] == $id){
                return $index;
            }
        }
        return -1;//not found
    }

}
