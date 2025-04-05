<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ExerciceRepository;

#[ORM\Entity(repositoryClass: ExerciceRepository::class)]
#[ORM\Table(name: 'exercice')]
class Exercice
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
    private ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $steps = null;

    public function getSteps(): ?string
    {
        return $this->steps;
    }

    public function setSteps(string $steps): self
    {
        $this->steps = $steps;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $sets = null;

    public function getSets(): ?int
    {
        return $this->sets;
    }

    public function setSets(int $sets): self
    {
        $this->sets = $sets;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $reps = null;

    public function getReps(): ?int
    {
        return $this->reps;
    }

    public function setReps(int $reps): self
    {
        $this->reps = $reps;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $grips = null;

    public function getGrips(): ?string
    {
        return $this->grips;
    }

    public function setGrips(string $grips): self
    {
        $this->grips = $grips;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $difficulty = null;

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): self
    {
        $this->difficulty = $difficulty;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $body_map_front = null;

    public function getBody_map_front(): ?string
    {
        return $this->body_map_front;
    }

    public function setBody_map_front(string $body_map_front): self
    {
        $this->body_map_front = $body_map_front;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $body_map_back = null;

    public function getBody_map_back(): ?string
    {
        return $this->body_map_back;
    }

    public function setBody_map_back(string $body_map_back): self
    {
        $this->body_map_back = $body_map_back;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $video_url = null;

    public function getVideo_url(): ?string
    {
        return $this->video_url;
    }

    public function setVideo_url(?string $video_url): self
    {
        $this->video_url = $video_url;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $m_primary = null;

    public function isM_primary(): ?bool
    {
        return $this->m_primary;
    }

    public function setM_primary(?bool $m_primary): self
    {
        $this->m_primary = $m_primary;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $m_secondary = null;

    public function isM_secondary(): ?bool
    {
        return $this->m_secondary;
    }

    public function setM_secondary(?bool $m_secondary): self
    {
        $this->m_secondary = $m_secondary;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $m_tertiary = null;

    public function isM_tertiary(): ?bool
    {
        return $this->m_tertiary;
    }

    public function setM_tertiary(?bool $m_tertiary): self
    {
        $this->m_tertiary = $m_tertiary;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $equipment = null;

    public function getEquipment(): ?string
    {
        return $this->equipment;
    }

    public function setEquipment(?string $equipment): self
    {
        $this->equipment = $equipment;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: false)]
    private ?bool $push = null;

    public function isPush(): ?bool
    {
        return $this->push;
    }

    public function setPush(bool $push): self
    {
        $this->push = $push;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: false)]
    private ?bool $pull = null;

    public function isPull(): ?bool
    {
        return $this->pull;
    }

    public function setPull(bool $pull): self
    {
        $this->pull = $pull;
        return $this;
    }

    public function getBodyMapFront(): ?string
    {
        return $this->body_map_front;
    }

    public function setBodyMapFront(string $body_map_front): static
    {
        $this->body_map_front = $body_map_front;

        return $this;
    }

    public function getBodyMapBack(): ?string
    {
        return $this->body_map_back;
    }

    public function setBodyMapBack(string $body_map_back): static
    {
        $this->body_map_back = $body_map_back;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->video_url;
    }

    public function setVideoUrl(?string $video_url): static
    {
        $this->video_url = $video_url;

        return $this;
    }

    public function isMPrimary(): ?bool
    {
        return $this->m_primary;
    }

    public function setMPrimary(?bool $m_primary): static
    {
        $this->m_primary = $m_primary;

        return $this;
    }

    public function isMSecondary(): ?bool
    {
        return $this->m_secondary;
    }

    public function setMSecondary(?bool $m_secondary): static
    {
        $this->m_secondary = $m_secondary;

        return $this;
    }

    public function isMTertiary(): ?bool
    {
        return $this->m_tertiary;
    }

    public function setMTertiary(?bool $m_tertiary): static
    {
        $this->m_tertiary = $m_tertiary;

        return $this;
    }

}
