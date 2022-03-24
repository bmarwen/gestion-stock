<?php

namespace App\Twig\Extension;

use App\Entity\Category;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class DatabaseGlobalsExtension extends AbstractExtension implements GlobalsInterface
{

   /**
    * EntityManagerInterface $em
    */ 
   protected $em;

   public function __construct(EntityManagerInterface $em)
   {
      $this->em = $em;
   }

   public function getGlobals(): array
   {
      return [
          'categories' => $this->em->getRepository(Category::class)->findAll(),
      ];
   }
}