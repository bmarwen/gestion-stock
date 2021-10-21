<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * this could be product, command, client, provider
     * @ORM\Column(type="string", length=35)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isOpen;

    /**
     * this could be qty, expr_date
     * @ORM\Column(type="string", length=125)
     */
    private $regardingType;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expiresAt;

    /**
     * @ORM\Column(type="object")
     */
    private $concern;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isOpen = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIsOpen(): ?bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(bool $isOpen): self
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    public function getRegardingType(): ?string
    {
        return $this->regardingType;
    }

    public function setRegardingType(string $regardingType): self
    {
        $this->regardingType = $regardingType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getConcern()
    {
        return $this->concern;
    }

    public function setConcern($concern): self
    {
        $this->concern = $concern;

        return $this;
    }
}
