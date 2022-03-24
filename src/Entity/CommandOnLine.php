<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CommandOnLineRepository;


/**
 * @ORM\Entity(repositoryClass=CommandOnLineRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class CommandOnLine
{

    CONST CMD_STATUS_CREATED  = "Créée" ;
    CONST CMD_STATUS_WAITING_DELIVERY  = "En cours de livraison" ;
    CONST CMD_STATUS_CANCELED  = "Annulée" ;
    CONST CMD_STATUS_COMPLETED  = "Terminée" ;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=55)
     */
    private $status;

    /**
     * @ORM\Column(type="array")
     */
    private $cmdDetails = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $shippingPrice;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=55)
     */
    private $firstName;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=55)
     */
    private $lastName;

    /**
     * @Assert\NotBlank
     * 
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "limite de 255 caractères."
     * )
     *
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "limite de 255 caractères."
     * )
     */
    private $addressComplement;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=12)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email(
     *     message = "Email '{{ value }}' est invalide."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "limite de 512 caractères."
     * )
     */
    private $moreDetails;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isContacted;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->isContacted = false;
        $this->status = CommandOnLine::CMD_STATUS_CREATED;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getTotalCommand(){
        $total = 0;
        foreach ($this->getCmdDetails() as $cmdDetail){
            if (array_key_exists('hasPromo', $cmdDetail)) { //hasPromo
                $currentPrice = $cmdDetail['promo']['price_after_promo'];
            } else {
                $currentPrice = $cmdDetail['price'];
            }
            $total += $currentPrice * $cmdDetail['qty'];
        }
        return $total;
                             
    }

    public function getFullAddress(){
        return $this->getAddress() . " " . $this->getAddressComplement();
    }

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCmdDetails(): ?array
    {
        return $this->cmdDetails;
    }

    public function setCmdDetails(array $cmdDetails): self
    {
        $this->cmdDetails = $cmdDetails;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddressComplement(): ?string
    {
        return $this->addressComplement;
    }

    public function setAddressComplement(string $addressComplement): self
    {
        $this->addressComplement = $addressComplement;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getShippingPrice(): ?int
    {
        return $this->shippingPrice;
    }

    public function setShippingPrice(?int $shippingPrice): self
    {
        $this->shippingPrice = $shippingPrice;

        return $this;
    }

    public function getMoreDetails(): ?string
    {
        return $this->moreDetails;
    }

    public function setMoreDetails(?string $moreDetails): self
    {
        $this->moreDetails = $moreDetails;

        return $this;
    }

    public function getIsContacted(): ?bool
    {
        return $this->isContacted;
    }

    public function setIsContacted(bool $isContacted): self
    {
        $this->isContacted = $isContacted;

        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @ORM\PreUpdate
     */
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTime();
    }


}
