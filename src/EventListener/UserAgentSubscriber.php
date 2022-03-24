<?php


namespace App\EventListener;

use App\Entity\Application;
use App\Entity\Notification;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class UserAgentSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    private $twig;

    public function __construct(EntityManagerInterface $entityManager,Environment $twig)
    {
        $this->em = $entityManager;
        $this->twig = $twig;
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'onKernelRequest'
        ];
    }

    public function onKernelRequest()
    {
        //get shipping information
        $shippingPrice = $this->em->getRepository(Application::class)->findOneByName('shipping_price');
        $freeShippingMinPrice = $this->em->getRepository(Application::class)->findOneByName('free_shipping_min_price');
        
        $notifications = $this->em->getRepository(Notification::class)->findAll();
        $todayPlusThreeMonths = new \DateTime();
        $todayPlusThreeMonths->add(new \DateInterval('P3M'));
        // we will verify if there is notifications
        $productsHowMany = $this->em->getRepository(Product::class)->findByHowManyLessThan(2);
        foreach ($productsHowMany as $product){ //['type' => 'product','id' => 56,'howMany' => 1,'expiration_date' => '16/03/2020']
            if($this->objectNotInNotifications($product,$notifications,"product","qty")){
                $notification = new Notification();
                $notification->setExpiresAt($todayPlusThreeMonths);
                $notification->setType("product");
                $notification->setConcern($product);
                $notification->setRegardingType("qty");
                $this->em->persist($notification);
            }
        }

        $productsExpirationDate = $this->em->getRepository(Product::class)->findByExpirationDateLessThan($todayPlusThreeMonths);
        foreach ($productsExpirationDate as $product){ //['type' => 'product','id' => 56,'howMany' => 1,'expiration_date' => '16/03/2020']
            if($this->objectNotInNotifications($product,$notifications,"product","expr_date")){
                $notification = new Notification();
                $notification->setExpiresAt($todayPlusThreeMonths);
                $notification->setType("product");
                $notification->setConcern($product);
                $notification->setRegardingType("expr_date");
                $this->em->persist($notification);
            }
        }
        $this->em->flush();
        $notifications = $this->em->getRepository(Notification::class)->findBy(['isOpen' => false],['createdAt' => 'DESC']);
        if(empty($notifications)){
            $notificationsCount = 0;
            $notificationsLast = [];
        }else{
            $notificationsLast = array_slice($notifications,0,9);
            $notificationsCount = count($notifications);
        }
        $notificationsToBeDeleted = $this->em->getRepository(Notification::class)->findNotificationsExpired();
        foreach ($notificationsToBeDeleted as $notif){
            $this->em->remove($notif);
        }
        $this->em->flush();
        $this->twig->addGlobal("notifications_count",$notificationsCount);
        $this->twig->addGlobal("notifications_last_five",$notificationsLast);
        $this->twig->addGlobal("shippingPrice",$shippingPrice);
        $this->twig->addGlobal("freeShippingMinPrice",$freeShippingMinPrice);
    }

    /**
     * @param object $object
     * @param Notification[] $notifications
     * @param string $type
     * @param string $regardingType
     */
    private function objectNotInNotifications($object,$notifications,string $type,string $regardingType){
        foreach ($notifications as $notification){
            if($notification->getRegardingType() === $regardingType && $notification->getType() === $type && $object->getId() === $notification->getConcern()->getId()){
                return false;
            }
        }
        return true;
    }

}