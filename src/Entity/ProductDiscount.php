<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProductDiscountRepository;

#[ORM\Entity(repositoryClass: ProductDiscountRepository::class)]
#[ORM\Table(name: 'product_discounts')]
class ProductDiscount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productDiscounts')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    private ?Product $product = null;

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: false)]
    private ?float $discount_percentage = null;

    public function getDiscount_percentage(): ?float
    {
        return $this->discount_percentage;
    }

    public function setDiscount_percentage(float $discount_percentage): self
    {
        $this->discount_percentage = $discount_percentage;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $valid_from = null;

    public function getValid_from(): ?\DateTimeInterface
    {
        return $this->valid_from;
    }

    public function setValid_from(\DateTimeInterface $valid_from): self
    {
        $this->valid_from = $valid_from;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $valid_until = null;

    public function getValid_until(): ?\DateTimeInterface
    {
        return $this->valid_until;
    }

    public function setValid_until(\DateTimeInterface $valid_until): self
    {
        $this->valid_until = $valid_until;
        return $this;
    }

    public function getDiscountPercentage(): ?string
    {
        return $this->discount_percentage;
    }

    public function setDiscountPercentage(string $discount_percentage): static
    {
        $this->discount_percentage = $discount_percentage;

        return $this;
    }

    public function getValidFrom(): ?\DateTimeInterface
    {
        return $this->valid_from;
    }

    public function setValidFrom(\DateTimeInterface $valid_from): static
    {
        $this->valid_from = $valid_from;

        return $this;
    }

    public function getValidUntil(): ?\DateTimeInterface
    {
        return $this->valid_until;
    }

    public function setValidUntil(\DateTimeInterface $valid_until): static
    {
        $this->valid_until = $valid_until;

        return $this;
    }

}
