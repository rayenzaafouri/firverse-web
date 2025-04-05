<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CouponRepository;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[ORM\Table(name: 'coupons')]
class Coupon
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $code = null;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
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

    #[ORM\Column(type: 'decimal', nullable: false)]
    private ?float $min_order_amount = null;

    public function getMin_order_amount(): ?float
    {
        return $this->min_order_amount;
    }

    public function setMin_order_amount(float $min_order_amount): self
    {
        $this->min_order_amount = $min_order_amount;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
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

    #[ORM\Column(type: 'datetime', nullable: false)]
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

    #[ORM\Column(type: 'boolean', nullable: false)]
    private ?bool $is_active = null;

    public function is_active(): ?bool
    {
        return $this->is_active;
    }

    public function setIs_active(bool $is_active): self
    {
        $this->is_active = $is_active;
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

    public function getMinOrderAmount(): ?string
    {
        return $this->min_order_amount;
    }

    public function setMinOrderAmount(string $min_order_amount): static
    {
        $this->min_order_amount = $min_order_amount;

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

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

}
