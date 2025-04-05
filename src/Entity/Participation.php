<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ParticipationRepository;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
#[ORM\Table(name: 'participation')]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $participationID = null;

    public function getParticipationID(): ?int
    {
        return $this->participationID;
    }

    public function setParticipationID(int $participationID): self
    {
        $this->participationID = $participationID;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'participations')]
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

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'participations')]
    #[ORM\JoinColumn(name: 'eventID', referencedColumnName: 'id')]
    private ?Event $event = null;

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $phoneNumber = null;

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $gender = null;

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateOfBirth = null;

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $participantType = null;

    public function getParticipantType(): ?string
    {
        return $this->participantType;
    }

    public function setParticipantType(?string $participantType): self
    {
        $this->participantType = $participantType;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $numberOfParticipants = null;

    public function getNumberOfParticipants(): ?int
    {
        return $this->numberOfParticipants;
    }

    public function setNumberOfParticipants(?int $numberOfParticipants): self
    {
        $this->numberOfParticipants = $numberOfParticipants;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $termsAccepted = null;

    public function isTermsAccepted(): ?bool
    {
        return $this->termsAccepted;
    }

    public function setTermsAccepted(?bool $termsAccepted): self
    {
        $this->termsAccepted = $termsAccepted;
        return $this;
    }

}
