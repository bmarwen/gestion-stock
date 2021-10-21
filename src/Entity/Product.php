<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use App\Repository\ProductRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @UniqueEntity("name")
 * @Vich\Uploadable()
 * @ApiResource(attributes={"pagination_items_per_page"=1000000})
 * @ApiFilter(RangeFilter::class, properties = {"howMany"})
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotNull
     * @Assert\Length(
     *      max = 125,
     *      maxMessage = "Le champ nom ne peut pas dépasser {{ limit }} caractéres"
     * )
     * @ORM\Column(type="string", length=125)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @Assert\NotNull
     * @ORM\Column(type="integer")
     */
    private $howMany;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="product_image", fileNameProperty="filename")
     */
    private $imageFile;

    /**
     * @var string|null
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $filename;

    /**
     * @ORM\ManyToOne(targetEntity=Provider::class, inversedBy="products")
     */
    private $provider;

    /**
     * @Assert\Length(
     *      max = 35,
     *      maxMessage = "Le champ code ne peut pas dépasser {{ limit }} caractéres"
     * )
     * @ORM\Column(type="string", length=35, nullable=false)
     */
    private $code;

     /**
     * @Assert\Length(
     *      max = 125,
     *      maxMessage = "Le champ mark ne peut pas dépasser {{ limit }} caractéres"
     * )
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    private $mark;

    /**
     * @ORM\Column(type="float")
     */
    private $purchacePriceUnHt;

    /**
     * @Assert\LessThanOrEqual(
     *     value=100,
     *     message="Max 100%",
     * )
     * @Assert\GreaterThanOrEqual(
     *     value=0,
     *     message="Min 0%"
     * )
     * @ORM\Column(type="smallint")
     */
    private $tva;

    /**
     * @Assert\LessThanOrEqual(
     *     value=100,
     *     message="Max 100%",
     * )
     * @Assert\GreaterThanOrEqual(
     *     value=0,
     *     message="Min 0%"
     * )
     * @ORM\Column(type="smallint")
     */
    private $gain;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $expirationDate;

    /**
     * @ORM\ManyToOne(targetEntity=Bills::class, inversedBy="relation",cascade={"persist"})
     */
    private $bill;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getSlug(): ?string
    {
        return (new Slugify())->slugify($this->name);
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        $priceTTC = $this->purchacePriceUnHt + ($this->purchacePriceUnHt * $this->tva)/100;
        $priceTTCAndGain = $priceTTC + ($priceTTC * $this->gain) / 100;
        return round($priceTTCAndGain,2);
    }

    public function getPriceHtWithGain(): ?float
    {
        $priceTTCAndGain = $this->purchacePriceUnHt + ($this->purchacePriceUnHt * $this->gain) / 100;
        return round($priceTTCAndGain,2);
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @param File|null $imageFile
     */
    public function setImageFile(?File $imageFile): void
    {
        $this->imageFile = $imageFile;
        if($this->imageFile instanceof UploadedFile){
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     */
    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
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
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function setProvider(?Provider $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getMark(): ?string
    {
        return $this->mark;
    }

    public function setMark(?string $mark): self
    {
        $this->mark = $mark;

        return $this;
    }

    public function getPurchacePriceUnHt(): ?float
    {
        return $this->purchacePriceUnHt;
    }

    public function setPurchacePriceUnHt(float $purchacePriceUnHt): self
    {
        $this->purchacePriceUnHt = $purchacePriceUnHt;

        return $this;
    }

    public function getTva(): ?int
    {
        return $this->tva;
    }

    public function setTva(int $tva): self
    {
        $this->tva = $tva;

        return $this;
    }

    public function getGain(): ?int
    {
        return $this->gain;
    }

    public function setGain(int $gain): self
    {
        $this->gain = $gain;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getBill(): ?Bills
    {
        return $this->bill;
    }

    public function setBill(?Bills $bill): self
    {
        $this->bill = $bill;

        return $this;
    }

}
