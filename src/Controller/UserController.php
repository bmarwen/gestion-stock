<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CommandOnLineRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile")
 */
class UserController extends AbstractController
{

    /**
     * @Route("/", name="user_show", methods={"GET"})
     */
    public function show(CommandOnLineRepository $commandOnLineRepository): Response
    {
        $user = $this->getUser();
        $commandOnLinesOfCurrentUser = $commandOnLineRepository->findBy([],['createdAt' => 'ASC']);
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'my_commands' => $commandOnLinesOfCurrentUser
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_show');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
