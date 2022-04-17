<?php

namespace App\Entity;

use App\Repository\CashRegisterPieceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CashRegisterPieceRepository::class)
 */
class CashRegisterPiece
{
    const PIECE_MONNAIE_DINARD = [
        50,
        20,
        10,
        5,
        2,
        1,
        0.5,
        0.2,
        0.1,
        0.05,
        0.02
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $moneyPiece;

    /**
     * @ORM\Column(type="integer")
     */
    private $howMany;

    /**
     * @ORM\Column(type="date")
     */
    private $whichday;

    
    public function __construct(float $moneyPiece = 50, int $howMany = 0, \DateTime $whichday = null)
    {
        $this->moneyPiece = $moneyPiece;
        $this->howMany = $howMany;

        if ($whichday == null) {
            $this->whichday = new \DateTime();
        } else {
            $this->whichday = $whichday;
        }
        
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMoneyPiece(): ?float
    {
        return $this->moneyPiece;
    }

    public function setMoneyPiece(float $moneyPiece): self
    {
        $this->moneyPiece = $moneyPiece;

        return $this;
    }

    public function getHowMany(): ?int
    {
        return $this->howMany;
    }

    public function setHowMany(int $howMany): self
    {
        $this->howMany = $howMany;

        return $this;
    }

    public function getWhichday(): ?\DateTimeInterface
    {
        return $this->whichday;
    }

    public function setWhichday(\DateTimeInterface $whichday): self
    {
        $this->whichday = $whichday;

        return $this;
    }
}
