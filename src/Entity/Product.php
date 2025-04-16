<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'product')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $brand = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'integer')]
    private ?int $stock = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $imageUrl = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true)]
    private ?Category $category = null;

    #[ORM\OneToMany(targetEntity: Wishlist::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $wishlists;

    public function __construct()
    {
        $this->wishlists = new ArrayCollection();
    }
    #[ORM\OneToMany(targetEntity: ProductDiscount::class, mappedBy: 'product')]
    private Collection $productDiscounts;

    /**
     * @return Collection<int, ProductDiscount>
     */
    
    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getPrice(): ?string { return $this->price; }
    public function setPrice(float $price): static { $this->price = $price; return $this; }

    public function getBrand(): ?string { return $this->brand; }
    public function setBrand(string $brand): static { $this->brand = $brand; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getStock(): ?int { return $this->stock; }
    public function setStock(int $stock): static { $this->stock = $stock; return $this; }

    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(string $imageUrl): static { $this->imageUrl = $imageUrl; return $this; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): static { $this->category = $category; return $this; }

    public function getWishlists(): Collection { return $this->wishlists; }
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
    public function __toString(): string
    {
        return $this->name ?? '';
    }

}
