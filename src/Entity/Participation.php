<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
#[ORM\Table(name: 'participation')]
#[Assert\Callback('validateAge')]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'participationID', type: 'integer')]
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

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: 'Email cannot be blank.')]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email address.')]
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

    #[ORM\Column(name: 'phoneNumber', type: 'string')]
    #[Assert\NotBlank(message: 'Phone number cannot be blank.')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9]{10,15}$/',
        message: 'The phone number must be valid (e.g., +1234567890 or 1234567890).'
    )]
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

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: 'Gender cannot be blank.')]
    #[Assert\Choice(choices: ['Male', 'Female', 'Other'], message: 'Choose a valid gender.')]
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

    #[ORM\Column(name: 'dateOfBirth', type: 'date')]
    #[Assert\NotBlank(message: 'Date of birth cannot be blank.')]
    #[Assert\LessThanOrEqual('today', message: 'Date of birth must be in the past.')]
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

    #[ORM\Column(name: 'participantType', type: 'string')]
    #[Assert\Choice(choices: ['Student', 'Professional', 'Guest'], message: 'Choose a valid participant type.')]
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

    #[ORM\Column(name: 'numberOfParticipants', type: 'integer')]
    #[Assert\NotBlank(message: 'Number of participants cannot be blank.')]
    #[Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid integer.')]
    #[Assert\Positive(message: 'The number of participants must be greater than zero.')]
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

    #[ORM\Column(name: 'termsAccepted', type: 'boolean')]
    #[Assert\IsTrue(message: 'You must accept the terms and conditions.')]
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

    public function validateAge(ExecutionContextInterface $context): void
    {
        if ($this->dateOfBirth) {
            $today = new \DateTime();
            $age = $today->diff($this->dateOfBirth)->y;
    
            if ($age >= 18) {
                $context->buildViolation('You must be older than 18 years.')
                    ->atPath('dateOfBirth')
                    ->addViolation();
            }
        }
    }
    public function __toString(): string
    {
        return $this->email ?? 'No email provided';    
}
}