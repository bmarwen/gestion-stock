<?php

namespace App\Controller\admin;

use App\Entity\CommandOnLine;
use App\Entity\Product;
use App\Form\CommandOnLineType;
use App\Repository\CommandOnLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/command_online")
 */
class AdminCommandOnLineController extends AbstractController
{
    const STATUS_CMD = [
        CommandOnLine::CMD_STATUS_CREATED,
        CommandOnLine::CMD_STATUS_WAITING_DELIVERY,
        CommandOnLine::CMD_STATUS_CANCELED,
        CommandOnLine::CMD_STATUS_COMPLETED
    ];
    /**
     * @Route("/", name="admin.command_on_line_index", methods={"GET"})
     */
    public function index(CommandOnLineRepository $commandOnLineRepository): Response
    {
        
        return $this->render('command_on_line/index.html.twig', [
            'command_on_lines' => $commandOnLineRepository->findBy([],['createdAt' => 'ASC']),
            'statusCmd' => AdminCommandOnLineController::STATUS_CMD
        ]);
    }

    /**
     * @Route("/change_status/{id}", name="admin.command_on_line_change_status", methods={"POST"})
     */
    public function changeStatus(CommandOnLine $commandOnLine = null, Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        if(!$commandOnLine){
            throw new NotFoundHttpException("Commande non trouvé");
        }
        $status = $request->request->get('status_cmd',$commandOnLine->getStatus());
        if(in_array($status, AdminCommandOnLineController::STATUS_CMD)){
            $commandOnLine->setStatus($status);
            //si commande validée => il faut maj la quantité
            if($status === CommandOnLine::CMD_STATUS_COMPLETED){
                $cmdDetails = $commandOnLine->getCmdDetails();
                $commandOnLine->setIsContacted(true);
                foreach($cmdDetails as $cmdDetail){
                    $product = $entityManagerInterface->getRepository(Product::class)->findOneBy(['id' => $cmdDetail['id']]);
                    $product->setHowMany($product->getHowMany() - $cmdDetail['qty']);
                    $entityManagerInterface->persist($product);
                }
            }
            if($status === CommandOnLine::CMD_STATUS_CANCELED){
                $commandOnLine->setIsContacted(true);
            }
            $entityManagerInterface->persist($commandOnLine);
            $entityManagerInterface->flush();
            $this->addFlash(
                'update_cmd',
                'Status modifié!'
            );
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/change_contacted/{id}", name="admin.command_on_line_change_contacted", methods={"POST"})
     */
    public function changeContacted(CommandOnLine $commandOnLine = null, EntityManagerInterface $entityManagerInterface): Response
    {
        if(!$commandOnLine){
            throw new NotFoundHttpException("Commande non trouvé");
        }
        $commandOnLine->setIsContacted(true);
        $entityManagerInterface->persist($commandOnLine);
        $entityManagerInterface->flush();
        $this->addFlash(
            'update_cmd',
            'Client contacté modifié!'
        );
        return $this->redirectToRoute("admin.command_on_line_index");
    }


    /**
     * @Route("/{id}", name="admin.command_on_line_show", methods={"GET"})
     */
    public function show(CommandOnLine $commandOnLine): Response
    {
        return $this->render('command_on_line/show.html.twig', [
            'command_on_line' => $commandOnLine,
            'statusCmd' => AdminCommandOnLineController::STATUS_CMD
        ]);
    }

}
