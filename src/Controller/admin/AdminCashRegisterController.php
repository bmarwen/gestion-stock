<?php

namespace App\Controller\admin;

use App\Entity\CashRegisterPiece;
use App\Repository\CashRegisterPieceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/cashRegister")
 */
class AdminCashRegisterController extends AbstractController
{
    /**
     * @Route("/", name="admin.cash_register_index", methods={"GET"})
     */
    public function index(Request $request, CashRegisterPieceRepository $cashRegisterPieceRepository): Response
    {
        $whichday =  $request->query->has('whichday') ? new \DateTime($request->query->get('whichday')) : new \DateTime();
        
        $cashRegisterPieceMonnaie = $cashRegisterPieceRepository->findByWhichday($whichday);
        
        return $this->render('cash_register/index.html.twig', [
            'whichday' => $whichday,
            'piece_monnaie' => CashRegisterPiece::PIECE_MONNAIE_DINARD,
            'cash_register_piece_monnaies' => $cashRegisterPieceMonnaie
        ]);
    }

    /**
     * @Route("/update_piece_monnaie", name="admin.update_piece_monnaie", methods={"POST"})
     */
    public function updatePieceMonnaie(Request $request, CashRegisterPieceRepository $cashRegisterPieceRepository, EntityManagerInterface $em): Response
    {
        $whichday =  $request->request->has('_whichday') ? new \DateTime($request->request->get('_whichday')) : new \DateTime(); //hidden input
        
        //$cashRegisterPieceMonnaie = $cashRegisterPieceRepository->findByWhichday($whichday);
        $allRequestParams = $request->request->all();
        
        foreach ($allRequestParams as $requestParam => $value) {
            if (strpos($requestParam, 'piece') !== false) {
                $piece = $this->retreivePieceFromRequestParams($requestParam);
                $cashRegisterPieceMonnaie = $cashRegisterPieceRepository->findOneBy(['whichday' => $whichday,'moneyPiece' => $piece]);
                if ($cashRegisterPieceMonnaie != null) {
                    if ($value == 0) {
                        $em->remove($cashRegisterPieceMonnaie);
                    } else {
                        $cashRegisterPieceMonnaie->setHowMany($value);
                    }
                    
                } else if($value > 0) {
                    $cashRegisterPieceMonnaie = new CashRegisterPiece($piece,$value,$whichday);
                }
                if($cashRegisterPieceMonnaie != null && $value > 0){
                    $em->persist($cashRegisterPieceMonnaie);
                }
                
            }
        }
        $em->flush();
        
        return $this->redirectToRoute('admin.cash_register_index',['whichday' => $whichday->format('Y-m-d')]);
    }

    private function retreivePieceFromRequestParams(string $requestParam): float
    {
        $prefix = 'piece_';
        $index = strpos($requestParam, $prefix) + strlen($prefix);
        return floatval(str_replace('_', '.',substr($requestParam, $index)));
    }

}