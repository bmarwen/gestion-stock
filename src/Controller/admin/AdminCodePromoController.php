<?php

namespace App\Controller\admin;

use App\Entity\CodePromo;
use App\Form\CodePromoType;
use App\Repository\CodePromoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/code_promo")
 */
class AdminCodePromoController extends AbstractController
{
    /**
     * @Route("/", name="admin.code_promo_index", methods={"GET"})
     */
    public function index(CodePromoRepository $codePromoRepository): Response
    {
        return $this->render('code_promo/index.html.twig', [
            'code_promos' => $codePromoRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin.code_promo_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $codePromo = new CodePromo();
        $form = $this->createForm(CodePromoType::class, $codePromo);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($codePromo);
            $entityManager->flush();

            return $this->redirectToRoute('admin.code_promo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('code_promo/new.html.twig', [
            'code_promo' => $codePromo,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin.code_promo_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, CodePromo $codePromo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CodePromoType::class, $codePromo);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin.code_promo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('code_promo/edit.html.twig', [
            'code_promo' => $codePromo,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin.code_promo_delete", methods={"POST"})
     */
    public function delete(Request $request, CodePromo $codePromo, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$codePromo->getId(), $request->request->get('_token'))) {
            if (null != $codePromo->getCommands() || null != $codePromo->getCommandsOnLine()) {
                $this->addFlash('error', 'Erreur : Code promo ' . $codePromo->getCode() . ' est déjà utilisé dans des commandes.');
            } else {
                $entityManager->remove($codePromo);
                $entityManager->flush();
            }
        }
        
        return $this->redirectToRoute('admin.code_promo_index', [], Response::HTTP_SEE_OTHER);
    }
}
