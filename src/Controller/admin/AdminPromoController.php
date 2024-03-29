<?php

namespace App\Controller\admin;

use App\Entity\Product;
use App\Entity\Promo;
use App\Form\PromoType;
use App\Repository\PromoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/promo")
 */
class AdminPromoController extends AbstractController
{
    /**
     * @Route("/", name="admin.promo_index", methods={"GET"})
     */
    public function index(PromoRepository $promoRepository): Response
    {
        return $this->render('promo/index.html.twig', [
            'promos' => $promoRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin.promo_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $promo = new Promo();
        $form = $this->createForm(PromoType::class, $promo);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $idProduct = $request->request->get('command_products') ?? null;
            if(null === $idProduct){
                $form->addError(new FormError("Produit obligatoire"));
                return $this->renderForm('promo/new.html.twig', [
                    'promo' => $promo,
                    'form' => $form,
                ]);
            }
            $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $idProduct]); 
            if(null === $product) {
                $form->addError(new FormError("Produit n'existe pas."));
                return $this->renderForm('promo/new.html.twig', [
                    'promo' => $promo,
                    'form' => $form,
                ]);
            }
            //check if the product has already an active promo
            if ($product->getCurrentPromo()) {
                $form->addError(new FormError('Ce produit a déjà une promotion en cours'));
                return $this->renderForm('promo/new.html.twig', [
                    'promo' => $promo,
                    'form' => $form,
                ]);
            }
            $promo->setProduct($product);
            $entityManager->persist($promo);
            $entityManager->flush();

            return $this->redirectToRoute('admin.promo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('promo/new.html.twig', [
            'promo' => $promo,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin.promo_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Promo $promo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PromoType::class, $promo);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $idProduct = $request->request->get('command_products') ?? null;
            if(null === $idProduct){
                $form->addError(new FormError("Produit obligatoire"));
                return $this->renderForm('promo/new.html.twig', [
                    'promo' => $promo,
                    'form' => $form,
                ]);
            }
            $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $idProduct]); 
            if(null === $product) {
                $form->addError(new FormError("Produit n'existe pas."));
                return $this->renderForm('promo/new.html.twig', [
                    'promo' => $promo,
                    'form' => $form,
                ]);
            }
            //check if the product has already an active promo
            if ($product->getCurrentPromo()) {
                $form->addError(new FormError('Ce produit a déjà une promotion en cours'));
                return $this->renderForm('promo/new.html.twig', [
                    'promo' => $promo,
                    'form' => $form,
                ]);
            }
            $promo->setProduct($product);
            $entityManager->flush();

            return $this->redirectToRoute('admin.promo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('promo/edit.html.twig', [
            'promo' => $promo,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin.promo_delete", methods={"POST"})
     */
    public function delete(Request $request, Promo $promo, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promo->getId(), $request->request->get('_token'))) {
            $entityManager->remove($promo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.promo_index', [], Response::HTTP_SEE_OTHER);
    }
}
