<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
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
    public function qtyBasketUpdateQty(Product $product = null, Request $request): Response
    {   
        $qty = (int)$request->query->get('qty',1);//1 for up 0 for down
        $session = $this->requestStack->getSession();
        $products = $session->get('products',[]);
        if($product){
            $indexProductInProductsBasket = $this->indexProductInProductsBasket($product->getId(), $products);
            if($indexProductInProductsBasket != -1){//exist
                $products[$indexProductInProductsBasket]['qty'] = $qty;
                $session->set('products',$products);
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

    private function indexProductInProductsBasket($id, $products){
        foreach($products as $index => $element){
            if($element['id'] == $id){
                return $index;
            }
        }
        return -1;//not found
    }

}
