<?php

namespace App\Controller\admin;

use App\Entity\Notification;
use App\Entity\Product;
use App\Form\NotificationType;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/notification")
 */
class AdminNotificationController extends AbstractController
{
    /**
     * @Route("/", name="admin.notification_index", methods={"GET"})
     */
    public function index(NotificationRepository $notificationRepository): Response
    {
        return $this->render('notification/index.html.twig', [
            'notifications' => $notificationRepository->findBy([],['createdAt' => 'DESC']),
        ]);
    }

    /**
     * @Route("/{id}", name="admin.notification_show", methods={"GET"})
     */
    public function show(Notification $notification): Response
    {
        if(!$notification) throw new NotFoundHttpException();
        $notification->setIsOpen(true);
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository(Product::class)->find($notification->getConcern()->getId());
        $em->persist($notification);
        $em->flush();
        return $this->render('notification/show.html.twig', [
            'notification' => $notification,
            'product' => $product
        ]);
    }

    /**
     * @Route("/open/all",name="admin.notification_all_open")
     */
    public function allOpen(NotificationRepository $notificationRepository){
        $em = $this->getDoctrine()->getManager();
        $notifications = $notificationRepository->findBy(["isOpen" => false]);
        foreach ($notifications as $notification){
            $notification->setIsOpen(true);
            $em->persist($notification);
        }
        $em->flush();
        return $this->redirectToRoute("admin.notification_index");
    }
}
