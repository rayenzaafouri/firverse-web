<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\PartnerRepository;

#[ORM\Entity(repositoryClass: PartnerRepository::class)]
#[ORM\Table(name: 'partner')]
class Partner
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

    #[ORM\Column(type: 'string', nullable: false)]
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
    private ?string $imgUrl = null;

    public function getImgUrl(): ?string
    {
        return $this->imgUrl;
    }

    public function setImgUrl(string $imgUrl): self
    {
        $this->imgUrl = $imgUrl;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Gym::class, mappedBy: 'partner')]
    private Collection $gyms;

    public function __construct()
    {
        $this->gyms = new ArrayCollection();
    }

    /**
     * @return Collection<int, Gym>
     */
    public function getGyms(): Collection
    {
        if (!$this->gyms instanceof Collection) {
            $this->gyms = new ArrayCollection();
        }
        return $this->gyms;
    }

    public function addGym(Gym $gym): self
    {
        if (!$this->getGyms()->contains($gym)) {
            $this->getGyms()->add($gym);
        }
        return $this;
    }

    public function removeGym(Gym $gym): self
    {
        $this->getGyms()->removeElement($gym);
        return $this;
    }

}
