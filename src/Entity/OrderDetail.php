<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\OrderDetailRepository;

#[ORM\Entity(repositoryClass: OrderDetailRepository::class)]
#[ORM\Table(name: 'order_details')]
class OrderDetail
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

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderDetails')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id')]
    private ?Order $order = null;

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'orderDetails')]
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

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $quantity = null;

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: false)]
    private ?float $price_at_purchase = null;

    public function getPrice_at_purchase(): ?float
    {
        return $this->price_at_purchase;
    }

    public function setPrice_at_purchase(float $price_at_purchase): self
    {
        $this->price_at_purchase = $price_at_purchase;
        return $this;
    }

    public function getPriceAtPurchase(): ?string
    {
        return $this->price_at_purchase;
    }

    public function setPriceAtPurchase(string $price_at_purchase): static
    {
        $this->price_at_purchase = $price_at_purchase;

        return $this;
    }

}
