<?php

namespace App\Controller\admin;

use App\Entity\CashRegister;
use App\Entity\CashRegisterPiece;
use App\Entity\Command;
use App\Entity\OtherSales;
use App\Entity\Product;
use App\Form\CashRegisterType;
use App\Form\OtherSalesType;
use App\Repository\CashRegisterPieceRepository;
use App\Repository\CashRegisterRepository;
use App\Service\CashRegisterService;
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
    public function index(Request $request, EntityManagerInterface $em, CashRegisterService $cashRegisterService): Response
    {
        $whichday =  $request->query->has('whichday') ? new \DateTime($request->query->get('whichday')) : new \DateTime();
        $cashRegisterPieceMonnaie = $em->getRepository(CashRegisterPiece::class)->findByWhichday($whichday);
        $restInRegisterOfPrevDay = $cashRegisterService->getWhatWeAlreadyHaveInCashRegister($whichday);
        //get commands of whichday and add product information from db
        $commandsOfWhichDay = $em->getRepository(Command::class)->getCommandsOfWhichDay($whichday);
        foreach ($commandsOfWhichDay as $key => $command) {
            $products = [];
            foreach ($command->getProducts() as $productFromCmd) {
                $idProduct = $productFromCmd['id'];
                $productFromDb = $em->getRepository(Product::class)->findOneById($idProduct);
                $productFromCmd['productFromDb'] = $productFromDb;
                $products[] = $productFromCmd; 
            }
            $commandsOfWhichDay[$key]->setProducts($products);
        }

        
        //retreive all other expenses
        $otherExpensesOfWhichDay = $em->getRepository(OtherSales::class)->getExpensesOfWhichDay($whichday);
        $totalExpenses = $em->getRepository(OtherSales::class)->getTotalExpensesOfWhichDay($whichday)[0]['totalExpenses'];

        //autre dépenses
        $otherExpense = new OtherSales();
        $form = $this->createForm(OtherSalesType::class,$otherExpense);
        
        //versement
        $versement = $em->getRepository(CashRegister::class)->findOneByDay($whichday);
        $versementExist = true;
        
        if($versement == null){
            $versement = new CashRegister();
            $versementExist = false;
        }
        $formVersement = $this->createForm(CashRegisterType::class, $versement);
        
        $infoCommands = $this->calculateTotalOfCommands($commandsOfWhichDay);
        $totalAllCommands = $infoCommands['totalAllCommands'];
        $totalMoneyRendered = $infoCommands['totalMoneyRendered'];
        
        return $this->render('cash_register/index.html.twig', [
            'whichday' => $whichday,
            'piece_monnaie' => CashRegisterPiece::PIECE_MONNAIE_DINARD,
            'cash_register_piece_monnaies' => $cashRegisterPieceMonnaie,
            'commands' => $commandsOfWhichDay,
            'other_expenses' => $otherExpensesOfWhichDay,
            'total_expenses' => $totalExpenses,
            'form_expenses' => $form->createView(),
            'form_versement' => $formVersement->createView(),
            'versement' => $versement,
            'versement_exist' => $versementExist,
            'totalAllCommands' => $totalAllCommands,
            'totalMoneyRendered' => $totalMoneyRendered,
            'restInRegisterOfPrevDay' => $restInRegisterOfPrevDay
        ]);
    }

    /**
     * @Route("/add_other_expense", name="admin.add_other_expense", methods={"POST"})
     */
    public function addOtherExpense(Request $request, EntityManagerInterface $em): Response
    {
        $whichday =  $request->query->has('whichday') ? new \DateTime($request->query->get('whichday')) : new \DateTime();
        $otherExpense = new OtherSales();
        $form = $this->createForm(OtherSalesType::class,$otherExpense);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Dépense créé avec succès');
            $whichday->setTime(12,0,0);
            $otherExpense->setCreatedAt($whichday); 
            $em->persist($otherExpense);
            $em->flush();
        } elseif (!$form->isValid()) {
            $errorMsg = "";
            foreach ($form->getErrors(true) as $key => $error) {
                $errorMsg = $errorMsg . $error->getMessage() . " '" .  $error->getMessageParameters()['{{ value }}'] .  "' ";
                $this->addFlash('error', $errorMsg);
            }
        }

        return $this->redirectToRoute("admin.cash_register_index",['whichday' => $whichday->format('Y-m-d')]);
    }

    /**
     * @Route("/remove_other_expense/{id}", name="admin.remove_other_expense", methods={"POST"})
     */
    public function removeOtherExpense(EntityManagerInterface $em, Request $request, OtherSales $otherSales): Response
    {
        $whichday =  $request->request->has('_whichday') ? new \DateTime($request->request->get('_whichday')) : new \DateTime(); //hidden input
        $em->remove($otherSales);
        $em->flush();
        $this->addFlash('success', 'Créances/Dépenses modifiés avec succès');

        return $this->redirectToRoute("admin.cash_register_index",['whichday' => $whichday->format('Y-m-d')]);
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

    /**
     * @Route("/set_versement_value", name="admin.set_versement_value", methods={"POST"})
     */
    public function setVersementValue(Request $request, CashRegisterRepository $cashRegisterRepository, EntityManagerInterface $em): Response
    {
        $whichday =  $request->request->has('_whichday') ? new \DateTime($request->request->get('_whichday')) : new \DateTime(); //hidden input
        $versement = new CashRegister();
        $form = $this->createForm(CashRegisterType::class, $versement);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Versement ajouté avec succès');
            $versement->setDay($whichday);
            $cashRegisterPieceMonnaies = $em->getRepository(CashRegisterPiece::class)->findByWhichday($whichday);
            $totalCaisse = 0;
            foreach($cashRegisterPieceMonnaies as $cashRegisterPieceMonnaie){
                $totalCaisse = $totalCaisse + $cashRegisterPieceMonnaie->getMoneyPiece() * $cashRegisterPieceMonnaie->getHowMany();
            }
            $versement->setRestInRegister($totalCaisse - $versement->getVersementBank());
            $em->persist($versement);
            $em->flush();
        } elseif (!$form->isValid()) {
            $errorMsg = "";
            foreach ($form->getErrors(true) as $error) {
                $errorMsg = $errorMsg . $error->getMessage() . " ";
                $this->addFlash('error', $errorMsg);
            }
        }
        
        return $this->redirectToRoute('admin.cash_register_index',['whichday' => $whichday->format('Y-m-d')]);
    }

    private function retreivePieceFromRequestParams(string $requestParam): float
    {
        $prefix = 'piece_';
        $index = strpos($requestParam, $prefix) + strlen($prefix);
        return floatval(str_replace('_', '.',substr($requestParam, $index)));
    }

    public function calculateTotalOfCommands($commands)
    {
        $totalAllCommands = 0;
        $totalMoneyRendered = 0;
        foreach ($commands as $command) {
            $totalMoneyRendered+=$command->getMoneyReturnedToTheClient();
            $totalCommand = 0;
            foreach ($command->getProducts() as $productFromCmd) {
                if ($productFromCmd['promo'] != "0") {
                    $totalCommand = $totalCommand + ($productFromCmd['price']- ($productFromCmd['promo'] * $productFromCmd['price']/100))* $productFromCmd['howMany'];
                } else {
                    $totalCommand = $totalCommand + $productFromCmd['price'] * $productFromCmd['howMany'];
                }
            }
            $totalAllCommands = $totalAllCommands + $totalCommand;
        }

        return ['totalAllCommands' => round($totalAllCommands,1), 'totalMoneyRendered' => round($totalMoneyRendered,1)];
    }




}