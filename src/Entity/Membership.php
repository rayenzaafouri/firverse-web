<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MembershipRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MembershipRepository::class)]
#[ORM\Table(name: 'membership')]
class Membership
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Gym::class, inversedBy: 'memberships')]
    #[ORM\JoinColumn(name: 'gymId', referencedColumnName: 'id')]
    #[Assert\NotNull(message: "Le gymnase est requis.")]
    private ?Gym $gym = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'memberships')]
    #[ORM\JoinColumn(name: 'userId', referencedColumnName: 'id')]
    #[Assert\NotNull(message: "L'utilisateur est requis.")]
    private ?User $user = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\NotNull(message: "La date de début est requise.")]
    #[Assert\LessThan(propertyPath: "end_date", message: "La date de début doit être avant la date de fin.")]
    private ?\DateTimeInterface $start_date = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\NotNull(message: "La date de fin est requise.")]
    #[Assert\GreaterThan(propertyPath: "start_date", message: "La date de fin doit être après la date de début.")]
    private ?\DateTimeInterface $end_date = null;

    #[ORM\Column(type: 'decimal', nullable: true)]
    #[Assert\NotNull(message: "Le prix est requis.")]
    #[Assert\Positive(message: "Le prix doit être positif.")]
    private ?float $price = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Assert\NotNull(message: "Veuillez indiquer si le coaching est inclus.")]
    private ?bool $coaching = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "Le statut est requis.")]
    #[Assert\Choice(choices: ["Active", "Inactive", "Expired"], message: "Le statut doit être 'Active', 'Inactive' ou 'Expired'.")]
    private ?string $status = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getGym(): ?Gym
    {
        return $this->gym;
    }

    public function setGym(?Gym $gym): self
    {
        $this->gym = $gym;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getStart_date(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStart_date(?\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;
        return $this;
    }

    public function getEnd_date(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEnd_date(?\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function isCoaching(): ?bool
    {
        return $this->coaching;
    }

    public function setCoaching(?bool $coaching): self
    {
        $this->coaching = $coaching;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    // Alias pour noms en camelCase
    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(?\DateTimeInterface $start_date): static
    {
        $this->start_date = $start_date;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTimeInterface $end_date): static
    {
        $this->end_date = $end_date;
        return $this;
    }
}
