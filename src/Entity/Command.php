<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CommandRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommandRepository::class)
 * @ApiResource()
 * @ORM\HasLifecycleCallbacks()
 */
class Command
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="array")
     * [{id: 'x', howMany: 'x'},{id: 'y',  nb: 'y'}]
     */
    private $products = [];

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="commands")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true)
     */
    private $client;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $moneyReceivedByTheClient;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $moneyReturnedToTheClient;

    /**
     * @ORM\ManyToOne(targetEntity="CodePromo", inversedBy="commands")
     * @ORM\JoinColumn(name="code_promo_id", referencedColumnName="id")
     */
    private $codePromo;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->moneyReturnedToTheClient = 0;
        $this->moneyReceivedByTheClient = 0;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function prePersist(){ 
        //we verify if code Promo is valid!
        if ($this->codePromo != null && !$this->codePromo->isValid() ) {
            $this->codePromo = null;
        }
    }

    

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProducts(): ?array
    {
        return $this->products;
    }

    public function setProducts(array $products): self
    {
        $this->products = $products;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getMoneyReturnedToTheClient(): ?float
    {
        return $this->moneyReturnedToTheClient;
    }

    public function setMoneyReturnedToTheClient(float $moneyReturnedToTheClient): self
    {
        $this->moneyReturnedToTheClient = $moneyReturnedToTheClient;

        return $this;
    }

    public function getMoneyReceivedByTheClient(): ?float
    {
        return $this->moneyReceivedByTheClient;
    }

    public function setMoneyReceivedByTheClient(float $moneyReceivedByTheClient): self
    {
        $this->moneyReceivedByTheClient = $moneyReceivedByTheClient;

        return $this;
    }


    /**
     * Get the value of codePromo
     */ 
    public function getCodePromo()
    {
        return $this->codePromo;
    }

    /**
     * Set the value of codePromo
     *
     * @return  self
     */ 
    public function setCodePromo($codePromo)
    {
        $this->codePromo = $codePromo;

        return $this;
    }
}
