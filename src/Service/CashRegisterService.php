<?php

namespace App\Service;

use App\Repository\CashRegisterPieceRepository;
use App\Repository\CashRegisterRepository;

class CashRegisterService
{

    private $cashRegisterPieceRepository;
    private $cashRegisterRepository;

    public function __construct(CashRegisterPieceRepository $cashRegisterPieceRepository, CashRegisterRepository $cashRegisterRepository)
    {
        $this->cashRegisterPieceRepository = $cashRegisterPieceRepository;
        $this->cashRegisterRepository = $cashRegisterRepository;
    }

    public function getWhatWeAlreadyHaveInCashRegister(\DateTime $whichday): string // the day before the whichday
    {
        $whichdayBeforeObj = $this->cashRegisterPieceRepository->getWhichDayBeforeTheWhichdaySelectedInCashRegister($whichday);
        $restInRegisterOfPrevDay = 0;
        
        if (null != $whichdayBeforeObj) {
            $whichdayBefore = $whichdayBeforeObj[0]['whichdayBefore'];
            $whichdayBeforewDateTime = new \DateTime($whichdayBefore);
            $cashRegisterOfPrevDay = $this->cashRegisterRepository->findOneBy(['day' => $whichdayBeforewDateTime]);
            if (null != $cashRegisterOfPrevDay) {
                $restInRegisterOfPrevDay = $cashRegisterOfPrevDay->getRestInRegister();    
            }
        }
        
        return $restInRegisterOfPrevDay;
    }
}