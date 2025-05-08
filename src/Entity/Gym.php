<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\GymRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GymRepository::class)]
#[ORM\Table(name: 'gym')]
class Gym
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'Le nom est requis.')]
    #[Assert\Length(min: 2, minMessage: 'Le nom est trop court.')]
    #[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\s]+$/', message: 'Le nom ne doit contenir que des lettres.')]
    private ?string $name = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'L\'adresse est requise.')]
    private ?string $adresse = null;

    #[ORM\Column(type: 'decimal', nullable: true)]
    #[Assert\NotNull(message: 'La longitude est requise.')]
    #[Assert\Type(type: 'float', message: 'La longitude doit être un nombre réel.')]
    private ?float $longitude = null;

    #[ORM\Column(type: 'decimal', nullable: true)]
    #[Assert\NotNull(message: 'La latitude est requise.')]
    #[Assert\Type(type: 'float', message: 'La latitude doit être un nombre réel.')]
    private ?float $latitude = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone est requis.')]
    #[Assert\Regex(
        pattern: '/^\d{8}$/',
        message: 'Le numéro de téléphone doit contenir exactement 8 chiffres.'
    )]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'L\'email est requis.')]
    #[Assert\Email(message: 'L email nest pas valide , il doit contenir @.')]
    private ?string $email = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotNull(message: 'La capacité est requise.')]
    #[Assert\Positive(message: 'La capacité doit être un nombre positif.')]
    private ?int $capacity = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'Les horaires d\'ouverture sont requis.')]
    private ?string $opening_hours = null;

    #[ORM\Column(type: 'decimal', nullable: true)]
    #[Assert\NotNull(message: 'La note est requise.')]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: 'La note doit être entre 1 et 5.'
    )]
    private ?float $rating = null;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'createdAt')]
    private ?\DateTimeInterface $createdAt = null;


    #[ORM\Column(type: 'string',length: 1000, nullable: true, name: 'image')]
    #[Assert\NotNull(message: 'L image est requise.')]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'gyms')]
    #[ORM\JoinColumn(name: 'partner_id', referencedColumnName: 'id')]
    private ?User $partner = null;

    #[ORM\OneToMany(targetEntity: Membership::class, mappedBy: 'gym')]
    private Collection $memberships;

    public function __construct()
    {
        $this->memberships = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): self { $this->id = $id; return $this; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): self { $this->name = $name; return $this; }

    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): self { $this->adresse = $adresse; return $this; }

    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $longitude): self { $this->longitude = $longitude; return $this; }

    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $latitude): self { $this->latitude = $latitude; return $this; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }

    public function getCapacity(): ?int { return $this->capacity; }
    public function setCapacity(?int $capacity): self { $this->capacity = $capacity; return $this; }

    public function getOpening_hours(): ?string { return $this->opening_hours; }
    public function setOpening_hours(?string $opening_hours): self { $this->opening_hours = $opening_hours; return $this; }

    public function getRating(): ?float { return $this->rating; }
    public function setRating(?float $rating): self { $this->rating = $rating; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }

    public function getPartner(): ?User { return $this->partner; }
    public function setPartner(?User $partner): self { $this->partner = $partner; return $this; }

    public function getMemberships(): Collection { return $this->memberships; }
    public function addMembership(Membership $membership): self {
        if (!$this->memberships->contains($membership)) {
            $this->memberships[] = $membership;
        }
        return $this;
    }

    public function removeMembership(Membership $membership): self {
        $this->memberships->removeElement($membership);
        return $this;
    }

    // Alias pour Twig
    public function getOpeningHours(): ?string { return $this->opening_hours; }
    public function setOpeningHours(?string $opening_hours): static {
        $this->opening_hours = $opening_hours;
        return $this;
    }
}
