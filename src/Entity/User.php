<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];


    #[ORM\Column]
    private ?string $nickname = null;
    #[ORM\Column]
    private ?string $firstname = null;
    #[ORM\Column]
    private ?string $lastname = null;
    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[NotBlank]
    #[Length(min: 6)]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $status = false;

    #[ORM\Column(length: 64)]
    private ?string $validationToken = null;

    #[ORM\Column]
    private ?bool $emailNotify = false;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: Rating::class, orphanRemoval: true)]
    private Collection $ratings;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: Order::class, orphanRemoval: true)]
    private Collection $orders;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Mission::class, orphanRemoval: true)]
    private Collection $clientMissions;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: Mission::class)]
    private Collection $agentMissions;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->clientMissions = new ArrayCollection();
        $this->agentMissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function getFirstname(): ?string
    {
        return $this->nickname;
    }

    public function getLastname(): ?string
    {
        return $this->nickname;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string|null $plainPassword
     * @return User
     */
    public function setPlainPassword(?string $plainPassword): User
    {
        $this->plainPassword = $plainPassword;

        if (null !== $plainPassword) {
            $this->updatedAT = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setAgent($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getAgent() === $this) {
                $rating->setAgent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setAgent($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getAgent() === $this) {
                $order->setAgent(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getValidationToken(): ?string
    {
        return $this->validationToken;
    }

    public function setValidationToken(string $validationToken): self
    {
        $this->validationToken = $validationToken;

        return $this;
    }

    public function getEmailNotify(): ?bool
    {
        return $this->emailNotify;
    }

    public function setEmailNotify(bool $emailNotify): self
    {
        $this->emailNotify = $emailNotify;

        return $this;
    }

    /**
     * @return Collection<int, Mission>
     */
    public function getClientMissions(): Collection
    {
        return $this->clientMissions;
    }

    public function addClientMission(Mission $clientMission): self
    {
        if (!$this->clientMissions->contains($clientMission)) {
            $this->clientMissions->add($clientMission);
            $clientMission->setClient($this);
        }

        return $this;
    }

    public function removeClientMission(Mission $clientMission): self
    {
        if ($this->clientMissions->removeElement($clientMission)) {
            // set the owning side to null (unless already changed)
            if ($clientMission->getClient() === $this) {
                $clientMission->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Mission>
     */
    public function getAgentMissions(): Collection
    {
        return $this->agentMissions;
    }

    public function addAgentMission(Mission $agentMission): self
    {
        if (!$this->agentMissions->contains($agentMission)) {
            $this->agentMissions->add($agentMission);
            $agentMission->setAgent($this);
        }

        return $this;
    }

    public function removeAgentMission(Mission $agentMission): self
    {
        if ($this->agentMissions->removeElement($agentMission)) {
            // set the owning side to null (unless already changed)
            if ($agentMission->getAgent() === $this) {
                $agentMission->setAgent(null);
            }
        }

        return $this;
    }
}
