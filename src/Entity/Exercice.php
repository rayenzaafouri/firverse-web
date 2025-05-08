<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;



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
    #[Assert\NotBlank(message: "Exercise title is required")]
    #[Assert\Length(
        min: 5,
        max: 100,
        minMessage: "Exercise title must be at least {{ limit }} characters long",
        maxMessage: "Exercise title cannot be longer than {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\s\-_,\.;:()]+$/',
        message: "Exercise title can only contain letters, numbers, spaces and basic punctuation"
    )]

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
    #[Assert\NotBlank(message: "Exercise steps are required")]
    #[Assert\Json(message: "Steps must be a valid JSON string")]
    #[Assert\Callback([self::class, 'validateSteps'])]
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
    #[Assert\NotBlank(message: "Number of sets is required")]
    #[Assert\Type(
        type: 'integer',
        message: "The value {{ value }} is not a valid integer."
    )]
    #[Assert\Positive(
        message: "Number of sets must be a positive number (1 or more)"
    )]
    #[Assert\LessThanOrEqual(
        value: 100,
        message: "Number of sets cannot exceed {{ compared_value }}"
    )]
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
    #[Assert\NotBlank(message: "Number of reps is required")]
    #[Assert\Type(
        type: 'integer',
        message: "The value {{ value }} is not a valid integer."
    )]
    #[Assert\Positive(
        message: "Number of reps must be a positive number (1 or more)"
    )]
    #[Assert\LessThanOrEqual(
        value: 100,
        message: "Number of reps cannot exceed {{ compared_value }}"
    )]
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

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Type(
        type: 'string',
        message: "Grip must be a text value"
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\s\-_,\.;:()]*$/',
        message: "Grip can only contain letters, numbers, spaces and basic punctuation (-_,.;:())"
    )]
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
    #[Assert\NotBlank(message: "Difficulty level is required")]
    #[Assert\Choice(
        choices: ["beginner", "intermediate", "advanced", "novice"],
        message: "Invalid difficulty level. Must be one of: Beginner, Intermediate, Advanced, Novice"
    )]
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
    #[Assert\NotBlank(message: "Front body map URL is required")]
    #[Assert\Url(
        message: "The front body map must be a valid URL",
        protocols: ["http", "https"],
        relativeProtocol: false
    )]
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
    #[Assert\NotBlank(message: "Rear body map URL is required")]
    #[Assert\Url(
        message: "The rear body map must be a valid URL",
        protocols: ["http", "https"],
        relativeProtocol: false
    )]
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
    #[Assert\NotBlank(message: "Youtube embed URL is required")]
    #[Assert\Url(
        message: "Youtube embed URL must be a valid URL",
        protocols: ["http", "https"],
        relativeProtocol: false
    )]
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
    #[Assert\Callback([self::class, 'validateEquipment'])]
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





    // JSON Steps custom validator

    public static function validateSteps($steps, ExecutionContextInterface $context, $payload)
    {
        if (null === $steps || '' === $steps) {
            return;
        }

        $decoded = json_decode($steps, true);

        if (!is_array($decoded)) {
            $context->buildViolation('Steps must be a JSON array of objects')
                ->atPath('steps')
                ->addViolation();
            return;
        }

        foreach ($decoded as $index => $step) {
            if (!is_array($step)) {
                $context->buildViolation(sprintf('Step #%d must be an object', $index + 1))
                    ->atPath('steps')
                    ->addViolation();
                continue;
            }

            // Validate text property
            if (!array_key_exists('text', $step)) {
                $context->buildViolation(sprintf('Step #%d is missing required "text" property', $index + 1))
                    ->atPath('steps')
                    ->addViolation();
            } elseif (empty($step['text'])) {
                $context->buildViolation(sprintf('Step #%d text cannot be empty', $index + 1))
                    ->atPath('steps')
                    ->addViolation();
            } elseif (!is_string($step['text'])) {
                $context->buildViolation(sprintf('Step #%d text must be a string', $index + 1))
                    ->atPath('steps')
                    ->addViolation();
            }

            // Validate video property
            if (!array_key_exists('video', $step)) {
                $context->buildViolation(sprintf('Step #%d is missing required "video" property', $index + 1))
                    ->atPath('steps')
                    ->addViolation();
            } elseif (!filter_var($step['video'], FILTER_VALIDATE_URL)) {
                $context->buildViolation(sprintf('Step #%d video must be a valid URL', $index + 1))
                    ->atPath('steps')
                    ->addViolation();
            }

            // Reject additional properties
            $allowedProperties = ['text', 'video'];
            $extraProperties = array_diff(array_keys($step), $allowedProperties);
            if (!empty($extraProperties)) {
                $context->buildViolation(sprintf(
                    'Step #%d contains invalid properties: %s. Only "text" and "video" are allowed',
                    $index + 1,
                    implode(', ', $extraProperties)
                ))
                    ->atPath('steps')
                    ->addViolation();
            }
        }
    }



    // JSON Equipment custom validator
    public static function validateEquipment($equipment, ExecutionContextInterface $context): void
    {
        // Allow null/empty
        if (null === $equipment || '' === $trimmed = trim($equipment)) {
            return;
        }

        // Handle raw JSON (e.g., `["1","2","3"]`) 
        // or JS-style strings (e.g., `"['1','2','3']"`)
        $decoded = null;

        // Case 1: Input is already valid JSON (no outer quotes)
        if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
            $decoded = json_decode($trimmed, true);
        }
        // Case 2: Input is a quoted JSON string (e.g., `"["1","2","3"]"`)
        elseif (str_starts_with($trimmed, '"[') && str_ends_with($trimmed, ']"')) {
            $unquoted = substr($trimmed, 1, -1); // Remove outer quotes
            $decoded = json_decode($unquoted, true);
        }
        // Case 3: JavaScript-style array (e.g., `"['1','2','3']"`)
        elseif (preg_match('/^\[.*\]$/', trim($trimmed, "'\""))) {
            $jsArray = trim($trimmed, "'\""); // Remove outer quotes
            $decoded = json_decode(str_replace("'", '"', $jsArray), true);
        }

        // Validate JSON decoding
        if (!is_array($decoded)) {
            $context->buildViolation('Equipment must be a valid array format: ["1","2","3"] or \'["1","2","3"]\'')
                ->atPath('equipment')
                ->addViolation();
            return;
        }

        // Validate numbers (1-17)
        foreach ($decoded as $index => $item) {
            if (!ctype_digit($item)) {
                $context->buildViolation(sprintf('Item #%d must be an integer (got "%s")', $index + 1, $item))
                    ->atPath('equipment')
                    ->addViolation();
            } elseif (($num = (int)$item) < 1 || $num > 17) {
                $context->buildViolation(sprintf('Item #%d must be between 1-17 (got %d)', $index + 1, $num))
                    ->atPath('equipment')
                    ->addViolation();
            }
        }
    }
}
