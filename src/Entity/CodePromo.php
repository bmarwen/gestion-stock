<?php

namespace App\Entity;

use App\Repository\CodePromoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use ApiPlatform\Core\Annotation\ApiFilter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CodePromoRepository::class)
 * @UniqueEntity("code")
 * @ApiResource()
 * @ApiFilter(SearchFilter::class, properties = {"code" : "exact"})
 * @ApiFilter(BooleanFilter::class, properties={"isValid"})
 */
class CodePromo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $code;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startsAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expiresAt;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $options = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEnabled;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $percent;

    /**
     * @ORM\Column(type="float", nullable=false)
     */
    private $minPrice;

    /**
     * @ORM\OneToMany(targetEntity="Command", mappedBy="codePromo")
     */
    private $commands;

    /**
     * @ORM\OneToMany(targetEntity="CommandOnLine", mappedBy="codePromo")
     */
    private $commandsOnLine;

    public function __construct()
    {
        $this->commands = new ArrayCollection();
        $this->commandsOnLine = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->minPrice = -1;
    }

    public function isValid()
    {
        $now = new \DateTime();
        if($this->getIsEnabled() && $this->getStartsAt() < $now && $this->getExpiresAt() > $now){
            return true;
        }
        return false;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getStartsAt(): ?\DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeInterface $startsAt): self
    {
        $this->startsAt = $startsAt;

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

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getPercent(): ?int
    {
        return $this->percent;
    }

    public function setPercent(?int $percent): self
    {
        $this->percent = $percent;

        return $this;
    }

    

    /**
     * Get the value of commands
     */ 
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Add the value of commands
     *
     * @return  self
     */ 
    public function addCommands($command)
    {
        $this->commands[] = $command;

        return $this;
    }

    /**
     * Remove the value of commands
     *
     * @return  self
     */ 
    public function removeCommands($command)
    {
        unset($this->commands[$command]);

        return $this;
    }

    /**
     * Get the value of commandsOnLine
     */ 
    public function getCommandsOnLine()
    {
        return $this->commandsOnLine;
    }

    /**
     * Add the value of commandsOnLine
     *
     * @return  self
     */ 
    public function addCommandsOnLine($commandOnLine)
    {
        $this->commandsOnLine[] = $commandOnLine;

        return $this;
    }

    /**
     * Remove the value of commandsOnLine
     *
     * @return  self
     */ 
    public function removeCommandsOnLine($commandOnLine)
    {
        unset($this->commandsOnLine[$commandOnLine]);

        return $this;
    }

    /**
     * Get the value of minPrice
     */ 
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * Set the value of minPrice
     *
     * @return  self
     */ 
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;

        return $this;
    }
}
