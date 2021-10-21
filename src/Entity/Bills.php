<?php

namespace App\Entity;

use App\Repository\BillsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=BillsRepository::class)
 * @Vich\Uploadable()
 * @UniqueEntity("number")
 */
class Bills
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Length(
     *      max = 125,
     *      maxMessage = "Le champ number ne peut pas dépasser {{ limit }} caractéres"
     * )
     * @ORM\Column(type="string", length=125)
     */
    private $number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="bill")
     */
    private $products;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="bill_pdf", fileNameProperty="billPdfname")
     */
    private $billPdfFile;

    /**
     * @var string|null
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $billPdfname;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setBill($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getBill() === $this) {
                $product->setBill(null);
            }
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getBillPdfFile(): ?File
    {
        return $this->billPdfFile;
    }

    /**
     * @param File|null $pdfFile
     */
    public function setBillPdfFile(?File $pdfFile): void
    {
        $this->billPdfFile = $pdfFile;
        if($this->billPdfFile instanceof UploadedFile){
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * @return string|null
     */
    public function getBillPdfname(): ?string
    {
        return $this->billPdfname;
    }

    /**
     * @param string|null $billPdfname
     */
    public function setBillPdfname(?string $billPdfname): void
    {
        $this->billPdfname = $billPdfname;
    }
}
