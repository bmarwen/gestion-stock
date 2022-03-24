<?php

namespace App\Controller\admin;

use App\Entity\Application;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/application")
 */
class AdminApplicationController extends AbstractController
{
    /**
     * @Route("/", name="admin.application_index", methods={"GET"})
     */
    public function index(ApplicationRepository $applicationRepository): Response
    {
        return $this->render('application/index.html.twig', [
            'applications' => $applicationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin.application_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $application = new Application();
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(!empty($application->getRedirectTo())){
                $redirectTo = $application->getRedirectTo();
                if(!substr($redirectTo, 0, 1) != '/'){
                    $redirectTo = "/". $redirectTo; 
                    $application->setRedirectTo($redirectTo);
                }
            }else{
                $application->setRedirectTo("/");
            }
            $entityManager->persist($application);
            $entityManager->flush();

            return $this->redirectToRoute('admin.application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('application/new.html.twig', [
            'application' => $application,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin.application_show", methods={"GET"})
     */
    public function show(Application $application): Response
    {
        return $this->render('application/show.html.twig', [
            'application' => $application,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin.application_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Application $application, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin.application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('application/edit.html.twig', [
            'application' => $application,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin.application_delete", methods={"POST"})
     */
    public function delete(Request $request, Application $application, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$application->getId(), $request->request->get('_token'))) {
            $entityManager->remove($application);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.application_index', [], Response::HTTP_SEE_OTHER);
    }
}
