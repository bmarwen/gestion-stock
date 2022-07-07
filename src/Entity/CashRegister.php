<?php

namespace App\Entity;

use App\Repository\CashRegisterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CashRegisterRepository::class)
 * @UniqueEntity("day")
 */
class CashRegister
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotNull
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "Le champ versement ne peut pas être inférieur à {{ limit }}"
     * )
     * @ORM\Column(type="float")
     */
    private $versementBank;

    /**
     * @ORM\Column(type="date")
     */
    private $day;

    /**
     * @ORM\Column(type="float")
     */
    private $restInRegister;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersementBank(): ?float
    {
        return $this->versementBank;
    }

    public function setVersementBank(float $versementBank): self
    {
        $this->versementBank = $versementBank;

        return $this;
    }

    public function getDay(): ?\DateTimeInterface
    {
        return $this->day;
    }

    public function setDay(\DateTimeInterface $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getRestInRegister(): ?float
    {
        return $this->restInRegister;
    }

    public function setRestInRegister(float $restInRegister): self
    {
        $this->restInRegister = $restInRegister;

        return $this;
    }
}
