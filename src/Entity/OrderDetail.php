<?php

namespace App\Entity;

use App\Repository\OrderDetailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderDetailRepository::class)]
#[ORM\Table(name: 'order_details')]
class OrderDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderDetails')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id')]
    private ?Order $order = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'orderDetails')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    private ?Product $product = null;

    #[ORM\Column(type: 'integer')]
    private ?int $quantity = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $price_at_purchase = null;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPriceAtPurchase(): ?float
    {
        return $this->price_at_purchase;
    }

    public function setPriceAtPurchase(float $price_at_purchase): self
    {
        $this->price_at_purchase = $price_at_purchase;
        return $this;
    }
}
