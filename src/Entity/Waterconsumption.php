<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\WaterconsumptionRepository;

#[ORM\Entity(repositoryClass: WaterconsumptionRepository::class)]
#[ORM\Table(name: 'waterconsumption')]
class Waterconsumption
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'waterconsumptions')]
    #[ORM\JoinColumn(name: 'UserID', referencedColumnName: 'id')]
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

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $ConsumptionDate = null;

    public function getConsumptionDate(): ?\DateTimeInterface
    {
        return $this->ConsumptionDate;
    }

    public function setConsumptionDate(\DateTimeInterface $ConsumptionDate): self
    {
        $this->ConsumptionDate = $ConsumptionDate;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $AmountConsumed = null;

    public function getAmountConsumed(): ?float
    {
        return $this->AmountConsumed;
    }

    public function setAmountConsumed(?float $AmountConsumed): self
    {
        $this->AmountConsumed = $AmountConsumed;
        return $this;
    }

}
