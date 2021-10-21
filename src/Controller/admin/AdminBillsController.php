<?php

namespace App\Controller\admin;

use App\Entity\Bills;
use App\Form\BillsType;
use App\Repository\BillsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/bills")
 */
class AdminBillsController extends AbstractController
{
    /**
     * @Route("/", name="admin.bills_index", methods={"GET"})
     */
    public function index(BillsRepository $billsRepository): Response
    {
        return $this->render('bills/index.html.twig', [
            'bills' => $billsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin.bills_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $bill = new Bills();
        $form = $this->createForm(BillsType::class, $bill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($bill);
            $entityManager->flush();

            return $this->redirectToRoute('admin.bills_index');
        }

        return $this->render('bills/new.html.twig', [
            'bill' => $bill,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin.bills_show", methods={"GET"})
     */
    public function show(Bills $bill): Response
    {
        if(!$bill) throw new NotFoundHttpException();
        return $this->render('bills/show.html.twig', [
            'bill' => $bill,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin.bills_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Bills $bill): Response
    {
        $form = $this->createForm(BillsType::class, $bill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin.bills_index');
        }

        return $this->render('bills/edit.html.twig', [
            'bill' => $bill,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin.bills_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Bills $bill): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bill->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $products = $bill->getProducts();
            foreach ($products as $product){
                $product->setBill(null);
                $entityManager->persist($product);
            }
            $entityManager->remove($bill);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.bills_index');
    }
}
