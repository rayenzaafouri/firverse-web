<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ExerciseAiConversationRepository;

#[ORM\Entity(repositoryClass: ExerciseAiConversationRepository::class)]
#[ORM\Table(name: 'exercise_ai_conversation')]
class ExerciseAiConversation
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'exerciseAiConversations')]
    #[ORM\JoinColumn(name: 'userId', referencedColumnName: 'id')]
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

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $conversation = null;

    public function getConversation(): ?int
    {
        return $this->conversation;
    }

    public function setConversation(?int $conversation): self
    {
        $this->conversation = $conversation;
        return $this;
    }

}
