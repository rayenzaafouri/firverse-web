<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\FormError;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'product')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Please enter a product name.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Product name must be at least {{ limit }} characters long.',
        maxMessage: 'Product name cannot exceed {{ limit }} characters.'
    )]
    private ?string $name = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Please enter a price.')]
    #[Assert\Positive(message: 'Price must be greater than 0.')]
    #[Assert\Range(
        min: 0.01,
        max: 999999.99,
        notInRangeMessage: 'Price must be between {{ min }} and {{ max }} TND.'
    )]
    private ?string $price = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: 'Please enter a brand name.')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Brand name must be at least {{ limit }} characters long.',
        maxMessage: 'Brand name cannot exceed {{ limit }} characters.'
    )]
    private ?string $brand = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Please enter a description.')]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: 'Description must be at least {{ limit }} characters long.',
        maxMessage: 'Description cannot exceed {{ limit }} characters.'
    )]
    private ?string $description = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: 'Please enter stock quantity.')]
    #[Assert\GreaterThanOrEqual(
        value: 0,
        message: 'Stock cannot be negative.'
    )]
    private ?int $stock = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Please provide an image URL.')]
    #[Assert\Url(message: 'Please enter a valid URL.')]
    private ?string $imageUrl = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true)]
    #[Assert\NotNull(message: 'Please select a category.')]
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
    #[ORM\OneToMany(targetEntity: OrderDetail::class, mappedBy: 'product')]
    private Collection $orderDetails;

    public function getOrderDetails(): Collection
    {
        if (!$this->orderDetails instanceof Collection) {
            $this->orderDetails = new ArrayCollection();
        }
        return $this->orderDetails;
    }

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
