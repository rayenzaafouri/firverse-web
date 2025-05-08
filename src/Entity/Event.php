<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EventRepository;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
class Event
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
    #[Assert\NotBlank(message: "Event name cannot be blank.")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Event name must be at least {{ limit }} characters long.",
        maxMessage: "Event name cannot be longer than {{ limit }} characters."
    )]
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'events')]
    #[ORM\JoinColumn(name: 'userID', referencedColumnName: 'id')]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

   

    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotBlank(message: "Description cannot be blank.")]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: "Description must be at least {{ limit }} characters long.",
        maxMessage: "Description cannot be longer than {{ limit }} characters."
    )]
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

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Location cannot be blank.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Location must be at least {{ limit }} characters long.",
        maxMessage: "Location cannot be longer than {{ limit }} characters."
    )]
    private ?string $location = null;

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotBlank(message: "Date cannot be blank.")]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "Event date must be today or in the future."
    )]
    private ?\DateTimeInterface $date = null;
    

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Time cannot be blank.")]
    #[Assert\Regex(
        pattern: "/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/",
        message: "Time must be in HH:MM format."
    )]
    private ?string $time = null;
    

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(string $time): self
    {
        $this->time = $time;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: false)]
    #[Assert\NotBlank(message: "Price cannot be blank.")]
    #[Assert\PositiveOrZero(message: "Price must be zero or positive.")]
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
    private ?string $photo = null;

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'event')]
    private Collection $participations;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        if (!$this->participations instanceof Collection) {
            $this->participations = new ArrayCollection();
        }
        return $this->participations;
    }

    public function addParticipation(Participation $participation): self
    {
        if (!$this->getParticipations()->contains($participation)) {
            $this->getParticipations()->add($participation);
        }
        return $this;
    }

    public function removeParticipation(Participation $participation): self
    {
        $this->getParticipations()->removeElement($participation);
        return $this;
    }

    public function __toString(): string
    {
        return $this->name; 
    }

}
