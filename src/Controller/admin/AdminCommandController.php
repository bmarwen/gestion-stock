<?php

namespace App\Controller\admin;

use App\Entity\Command;
use App\Entity\CommandOnLine;
use App\Form\CommandType;
use App\Repository\CommandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/command")
 */
class AdminCommandController extends AbstractController
{
    /**
     * @Route("/", name="admin.command_index", methods={"GET"})
     */
    public function index(CommandRepository $commandRepository): Response
    {
        return $this->render('command/index.html.twig', [
            'commands' => $commandRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin.command_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $command = new Command();
        $form = $this->createForm(CommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($command);
            $entityManager->flush();

            return $this->redirectToRoute('admin.command_index');
        }

        return $this->render('command/new.html.twig', [
            'command' => $command,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin.command_show", methods={"GET"})
     */
    public function show(Command $command = null): Response
    {
        if(!$command) throw new NotFoundHttpException();
        return $this->render('command/show.html.twig', [
            'command' => $command,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin.command_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Command $command): Response
    {
        $form = $this->createForm(CommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin.command_index');
        }

        return $this->render('command/edit.html.twig', [
            'command' => $command,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin.command_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Command $command): Response
    {
        if ($this->isCsrfTokenValid('delete'.$command->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($command);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.command_index');
    }

    /**
     * @Route("/generate/novice/{id}",name="admin.generate_novice")
     */
    public function generateNovice(Command $command){
        return $this->render("command/novice.html.twig",[
            'command' => $command
        ]);
    }

    /**
     * @Route("/generate/novice_cmd_online/{id}",name="admin.generate_novice_cmd_online")
     */
    public function generateNoviceCmdOnline(CommandOnLine $commandOnLine = null){
        if(!$commandOnLine) throw new NotFoundHttpException();
        if($commandOnLine->getStatus() === CommandOnLine::CMD_STATUS_COMPLETED){
            return $this->render("command_on_line/novice.html.twig",[
                'commandOnLine' => $commandOnLine
            ]);
        }
        throw new BadRequestHttpException("Commande n'est pas encore terminé");

    }
}
