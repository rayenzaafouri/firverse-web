<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


use App\Repository\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
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
    #[Assert\NotBlank(message: 'First name is required')]
    #[Assert\Length(min: 2, max: 50, minMessage: 'First name must be at least 2 characters long.', maxMessage: 'First name cannot exceed 50 characters.')]

    private ?string $first_name = null;

    public function getFirst_name(): ?string
    {
        return $this->first_name;
    }

    public function setFirst_name(string $first_name): self
    {
        $this->first_name = $first_name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Last name cannot be blank.')]
    #[Assert\Length(min: 2, max: 50, minMessage: 'Last name must be at least 2 characters long.', maxMessage: 'Last name cannot exceed 50 characters.')]

    private ?string $last_name = null;

    public function getLast_name(): ?string
    {
        return $this->last_name;
    }

    public function setLast_name(string $last_name): self
    {
        $this->last_name = $last_name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Email cannot be blank.')]
    #[Assert\Email(message: 'The email is not a valid email.')]

    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Address cannot be blank.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Address must be at least 2 characters long.', maxMessage: 'Address cannot exceed 100 characters.')]

    private ?string $address = null;

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Phone cannot be blank.')]
    #[Assert\Length(min: 8, max: 8, minMessage: 'Phone must be 8 digits long.', maxMessage: 'Phone must be 8 digits long.')]

    private ?string $phone = null;

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]

    private ?string $password = null;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $gender = null;

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $role = null;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotNull(message: 'Birth date cannot be null.')]
    #[Assert\LessThanOrEqual(value: 'today', message: 'Birth date cannot be in the future.')]
    #[Assert\Type(type: \DateTimeInterface::class, message: 'The value is not a valid date.')]
    private ?\DateTimeInterface $birth_date = null;

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birth_date;
    }


    public function setBirthDate(?\DateTimeInterface $birth_date): static
    {
        $this->birth_date = $birth_date;
        return $this;
    }


    #[ORM\Column(type: 'string', nullable: false)]

    private ?string $image = null;

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'user')]
    private Collection $events;

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        if (!$this->events instanceof Collection) {
            $this->events = new ArrayCollection();
        }
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->getEvents()->contains($event)) {
            $this->getEvents()->add($event);
        }
        return $this;
    }

    public function removeEvent(Event $event): self
    {
        $this->getEvents()->removeElement($event);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ExerciseAiConversation::class, mappedBy: 'user')]
    private Collection $exerciseAiConversations;

    /**
     * @return Collection<int, ExerciseAiConversation>
     */
    public function getExerciseAiConversations(): Collection
    {
        if (!$this->exerciseAiConversations instanceof Collection) {
            $this->exerciseAiConversations = new ArrayCollection();
        }
        return $this->exerciseAiConversations;
    }

    public function addExerciseAiConversation(ExerciseAiConversation $exerciseAiConversation): self
    {
        if (!$this->getExerciseAiConversations()->contains($exerciseAiConversation)) {
            $this->getExerciseAiConversations()->add($exerciseAiConversation);
        }
        return $this;
    }

    public function removeExerciseAiConversation(ExerciseAiConversation $exerciseAiConversation): self
    {
        $this->getExerciseAiConversations()->removeElement($exerciseAiConversation);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Membership::class, mappedBy: 'user')]
    private Collection $memberships;

    /**
     * @return Collection<int, Membership>
     */
    public function getMemberships(): Collection
    {
        if (!$this->memberships instanceof Collection) {
            $this->memberships = new ArrayCollection();
        }
        return $this->memberships;
    }

    public function addMembership(Membership $membership): self
    {
        if (!$this->getMemberships()->contains($membership)) {
            $this->getMemberships()->add($membership);
        }
        return $this;
    }

    public function removeMembership(Membership $membership): self
    {
        $this->getMemberships()->removeElement($membership);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        if (!$this->orders instanceof Collection) {
            $this->orders = new ArrayCollection();
        }
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->getOrders()->contains($order)) {
            $this->getOrders()->add($order);
        }
        return $this;
    }

    public function removeOrder(Order $order): self
    {
        $this->getOrders()->removeElement($order);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'user')]
    private Collection $participations;

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

    #[ORM\OneToMany(targetEntity: Recipe::class, mappedBy: 'user')]
    private Collection $recipes;

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipes(): Collection
    {
        if (!$this->recipes instanceof Collection) {
            $this->recipes = new ArrayCollection();
        }
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): self
    {
        if (!$this->getRecipes()->contains($recipe)) {
            $this->getRecipes()->add($recipe);
        }
        return $this;
    }

    public function removeRecipe(Recipe $recipe): self
    {
        $this->getRecipes()->removeElement($recipe);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'user')]
    private Collection $reclamations;

    /**
     * @return Collection<int, Reclamation>
     */
    public function getReclamations(): Collection
    {
        if (!$this->reclamations instanceof Collection) {
            $this->reclamations = new ArrayCollection();
        }
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): self
    {
        if (!$this->getReclamations()->contains($reclamation)) {
            $this->getReclamations()->add($reclamation);
        }
        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): self
    {
        $this->getReclamations()->removeElement($reclamation);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Waterconsumption::class, mappedBy: 'user')]
    private Collection $waterconsumptions;

    /**
     * @return Collection<int, Waterconsumption>
     */
    public function getWaterconsumptions(): Collection
    {
        if (!$this->waterconsumptions instanceof Collection) {
            $this->waterconsumptions = new ArrayCollection();
        }
        return $this->waterconsumptions;
    }

    public function addWaterconsumption(Waterconsumption $waterconsumption): self
    {
        if (!$this->getWaterconsumptions()->contains($waterconsumption)) {
            $this->getWaterconsumptions()->add($waterconsumption);
        }
        return $this;
    }

    public function removeWaterconsumption(Waterconsumption $waterconsumption): self
    {
        $this->getWaterconsumptions()->removeElement($waterconsumption);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'user')]
    #[ORM\JoinTable(
        name: 'wishlist',
        joinColumns: [
            new ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $products;

    /**
     * @var Collection<int, Wishlist>
     */
    #[ORM\OneToMany(targetEntity: Wishlist::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $wishlists;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->exerciseAiConversations = new ArrayCollection();
        $this->memberships = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->participations = new ArrayCollection();
        $this->recipes = new ArrayCollection();
        $this->reclamations = new ArrayCollection();
        $this->waterconsumptions = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->wishlists = new ArrayCollection();
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        if (!$this->products instanceof Collection) {
            $this->products = new ArrayCollection();
        }
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->getProducts()->contains($product)) {
            $this->getProducts()->add($product);
        }
        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->getProducts()->removeElement($product);
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }


    /**
     * @return Collection<int, Wishlist>
     */
    public function getWishlists(): Collection
    {
        return $this->wishlists;
    }

    public function addWishlist(Wishlist $wishlist): static
    {
        if (!$this->wishlists->contains($wishlist)) {
            $this->wishlists->add($wishlist);
            $wishlist->setUserId($this);
        }

        return $this;
    }


    //Interface specific methods ---------

    public function getRoles(): array
{
    $validRoles = [
        'user' => 'ROLE_USER',
        'admin' => 'ROLE_ADMIN',
    ];

    $rolesFromDb = [$this->role ?? ''];

    $mappedRoles = [];

    foreach ($rolesFromDb as $role) {
        if (isset($validRoles[$role])) {
            $mappedRoles[] = $validRoles[$role];
        }
    }

    if (!in_array('ROLE_USER', $mappedRoles)) {
        $mappedRoles[] = 'ROLE_USER';
    }

    return array_unique($mappedRoles);
}




    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        // Usually return username or email
        return $this->email;
    }
}
