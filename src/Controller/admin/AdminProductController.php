<?php

namespace App\Controller\admin;

use App\Entity\Notification;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @Route("/admin/product")
 */
class AdminProductController extends AbstractController
{
    /**
     * @Route("/", name="admin.product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin.product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'Article créé avec succès');

            return $this->redirectToRoute('admin.product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin.product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        if(!$product) throw new NotFoundHttpException();
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin.product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product = null): Response
    {
        if(!$product) throw new NotFoundHttpException();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($product->getBill() != null && $product->getBill()->getNumber() == null) {
                $form->addError(new FormError("Numéro de la facture obligatoire"));
                return $this->render('product/edit.html.twig', [
                    'product' => $product,
                    'form' => $form->createView(),
                ]);
            }
            if ($product->getBill() != null && $product->getBill()->getBillPdfname() == null && $product->getBill()->getBillPdfFile() == null) {
                $form->addError(new FormError("Image facture obligatoire"));
                return $this->render('product/edit.html.twig', [
                    'product' => $product,
                    'form' => $form->createView(),
                ]);
            }
           
            
            $this->addFlash('success', 'Article modifié avec succès');
            //remove this product from notification system
            $em = $this->getDoctrine()->getManager();
            $notifications = $em->getRepository(Notification::class)->findAll();
            /** * @var Notification[] $notifications */

            foreach ($notifications as $notification){
                if($notification->getConcern()->getId() === $product->getId()){
                    $em->remove($notification);
                }
            }
            $em->flush();
            return $this->redirectToRoute('admin.product_show',['id' => $product->getId()]);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin.product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Article supprimé avec succès');
        }

        return $this->redirectToRoute('admin.product_index');
    }
}
