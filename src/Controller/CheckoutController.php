<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\CodePromo;
use App\Entity\CommandOnLine;
use App\Entity\Product;
use App\Form\CommandOnLineType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/checkout",name="checkout")
 */
class CheckoutController extends AbstractController
{

    private $requestStack; //session => products

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/", name="_new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {   
        $commandOnLine = new CommandOnLine();
        $form = $this->createForm(CommandOnLineType::class, $commandOnLine);
        $form->handleRequest($request);

        $session = $this->requestStack->getSession();
        $products = $session->get('products',[]);
        
        
        if ($form->isSubmitted() && $form->isValid()) {
            $productsToInsertInDb = [];
            foreach ($products as $product) {
                if ($prodFromDb = $entityManager->getRepository(Product::class)->findOneById($product['id'])){
                    if ($promo = $prodFromDb->getCurrentPromo()) {
                        $product['hasPromo'] = true;
                        $product['promo'] = [
                            'price_after_promo' => $prodFromDb->getPriceAfterPromotionIfExists(),
                            'percent' => $promo->getPourcent(),
                            'id' => $promo->getId()
                        ];
                    }
                } 
                $productsToInsertInDb[] = $product;
            }
            
            $commandOnLine->setCmdDetails($productsToInsertInDb);//$products[] = ['id' => xx, 'name' => xx,'price' => xx,'imageFilePath' => xx, 'qty' => xx];
            //verify shipping Price eligible or not for free shipping
            $shippingPrice = $entityManager->getRepository(Application::class)->findOneByName('shipping_price');
            $freeShippingMinPrice = $entityManager->getRepository(Application::class)->findOneByName('free_shipping_min_price');
            $shippingPriceCalculated = 0;
            if ($commandOnLine->getTotalCommand() <= $freeShippingMinPrice->getValue()) {
                $shippingPriceCalculated = $shippingPrice->getValue();
            }
            //gestion code promo
            if ($session->has('codepromo_id')) {
                $codePromoId = $session->get('codepromo_id');
                $codePromo = $entityManager->getRepository(CodePromo::class)->findOneBy(['id' => $codePromoId]);
                if(null != $codePromo && $codePromo->isValid()) {
                    $commandOnLine->setCodePromo($codePromo);
                    $codePromo->addCommandsOnLine($commandOnLine);
                }
            }
            $commandOnLine->setShippingPrice($shippingPriceCalculated);
            $entityManager->persist($commandOnLine);
            $entityManager->persist($codePromo);
            $entityManager->flush();
            $session->remove('products');
            $session->remove('codepromo_id');
            $session->remove('codepromo_percent');
            return $this->redirectToRoute('checkout_finalized', ['id' => $commandOnLine->getId()], Response::HTTP_SEE_OTHER);
        }

        $productsFromDb = [];
        //add product entity to session
        foreach ($products as $product) {
            if ($prodFromDb = $entityManager->getRepository(Product::class)->findOneById($product['id'])){
                $productsFromDb[$prodFromDb->getId()] = $prodFromDb;
            } 
        }

        
        return $this->renderForm('app/checkout/new.html.twig', [
            'command_on_line' => $commandOnLine,
            'form' => $form,
            'products' => $products,
            'productsFromDb' => $productsFromDb
        ]);
    }

    /**
     * @Route("/finalized/{id}", name="_finalized")
     */
    public function finalized(CommandOnLine $commandOnLine): Response
    {   
        if(!$commandOnLine){
            throw new NotFoundHttpException();
        }
        return $this->render('app/checkout/finalized.html.twig', [
            'commande' => $commandOnLine
        ]);
    }



}
