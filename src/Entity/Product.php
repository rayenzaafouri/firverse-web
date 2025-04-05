<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProductRepository;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'product')]
class Product
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
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: false)]
    private ?float $price = null;

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $brand = null;

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $stock = null;

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $imageUrl = null;

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    private ?Category $category = null;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: OrderDetail::class, mappedBy: 'product')]
    private Collection $orderDetails;

    /**
     * @return Collection<int, OrderDetail>
     */
    public function getOrderDetails(): Collection
    {
        if (!$this->orderDetails instanceof Collection) {
            $this->orderDetails = new ArrayCollection();
        }
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetail $orderDetail): self
    {
        if (!$this->getOrderDetails()->contains($orderDetail)) {
            $this->getOrderDetails()->add($orderDetail);
        }
        return $this;
    }

    public function removeOrderDetail(OrderDetail $orderDetail): self
    {
        $this->getOrderDetails()->removeElement($orderDetail);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ProductDiscount::class, mappedBy: 'product')]
    private Collection $productDiscounts;

    /**
     * @return Collection<int, ProductDiscount>
     */
    public function getProductDiscounts(): Collection
    {
        if (!$this->productDiscounts instanceof Collection) {
            $this->productDiscounts = new ArrayCollection();
        }
        return $this->productDiscounts;
    }

    public function addProductDiscount(ProductDiscount $productDiscount): self
    {
        if (!$this->getProductDiscounts()->contains($productDiscount)) {
            $this->getProductDiscounts()->add($productDiscount);
        }
        return $this;
    }

    public function removeProductDiscount(ProductDiscount $productDiscount): self
    {
        $this->getProductDiscounts()->removeElement($productDiscount);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'products')]
    #[ORM\JoinTable(
        name: 'wishlist',
        joinColumns: [
            new ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $users;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
        $this->productDiscounts = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        if (!$this->users instanceof Collection) {
            $this->users = new ArrayCollection();
        }
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->getUsers()->contains($user)) {
            $this->getUsers()->add($user);
        }
        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->getUsers()->removeElement($user);
        return $this;
    }

}
