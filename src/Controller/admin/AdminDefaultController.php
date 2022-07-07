<?php
namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminDefaultController extends AbstractController {

    /**
     * @Route("/", name="admin.index")
     * @return Response
     */
    public function index() : Response{
        return $this->redirectToRoute('admin.cash_register_index');
    }

}