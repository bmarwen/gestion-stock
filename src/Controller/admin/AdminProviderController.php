<?php

namespace App\Controller\admin;

use App\Entity\Provider;
use App\Form\ProviderType;
use App\Repository\ProviderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/provider")
 */
class AdminProviderController extends AbstractController
{
    /**
     * @Route("/", name="admin.provider_index", methods={"GET"})
     */
    public function index(ProviderRepository $providerRepository): Response
    {
        return $this->render('provider/index.html.twig', [
            'providers' => $providerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin.provider_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $provider = new Provider();
        $form = $this->createForm(ProviderType::class, $provider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($provider);
            $entityManager->flush();

            return $this->redirectToRoute('admin.provider_index');
        }

        return $this->render('provider/new.html.twig', [
            'provider' => $provider,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin.provider_show", methods={"GET"})
     */
    public function show(Provider $provider): Response
    {
        if(!$provider) throw new NotFoundHttpException();
        return $this->render('provider/show.html.twig', [
            'provider' => $provider,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin.provider_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Provider $provider): Response
    {
        $form = $this->createForm(ProviderType::class, $provider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin.provider_index');
        }

        return $this->render('provider/edit.html.twig', [
            'provider' => $provider,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin.provider_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Provider $provider): Response
    {
        if ($this->isCsrfTokenValid('delete'.$provider->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($provider);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.provider_index');
    }
}
