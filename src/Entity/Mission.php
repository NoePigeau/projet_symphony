<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\MissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Mapping\Annotation\SortablePosition;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: MissionRepository::class)]
#[UniqueEntity(fields: ['name'])]
#[Vich\Uploadable]
class Mission
{
    const STATUS_IN_DEMAND = 'in_demand';
    const STATUS_REFUSED = 'refused';
    const STATUS_FREE = 'free';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_FINISHED = 'finished';


    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Cette valeur ne peut pas être vide.')]
    #[Assert\Type('string', message: 'Cette valeur doit être une chaine de caractère.')]
    #[Assert\Length(min: 10, max: 100, minMessage: 'Vous devez avoir au moins 10 caractères.', maxMessage: 'Vous devez avoir maximum 100 caractères.')]
    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 105)]
    #[Slug(fields: ['name', 'createdAt'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'missions')]
    private ?Type $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'missions', fileNameProperty: 'image')]
    #[Assert\Image(
        maxSize: '2M',
        mimeTypes: ['image/png', 'image/jpeg'],
        mimeTypesMessage: 'coucou, tu peux mettre que des png ou jpeg',
    )]
    private ?File $imageFile = null;

    #[ORM\OneToMany(mappedBy: 'mission', targetEntity: Step::class, orphanRemoval: true)]
    private Collection $steps;

    #[ORM\OneToMany(mappedBy: 'mission', targetEntity: Order::class, orphanRemoval: true)]
    private Collection $orders;

    #[ORM\OneToMany(mappedBy: 'missionId', targetEntity: Message::class)]
    private Collection $messages;

    #[ORM\ManyToOne(inversedBy: 'clientMissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\ManyToOne(inversedBy: 'agentMissions')]
    private ?User $agent = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column]
    private ?float $reward = null;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public static function getAllStatus()
    {
        return [self::STATUS_IN_DEMAND , self::STATUS_REFUSED, self::STATUS_FREE, self::STATUS_IN_PROGRESS, self::STATUS_FINISHED];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     * @return Mission
     */
    public function setSlug(?string $slug): Mission
    {
        $this->slug = $slug;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     * @return Mission
     */
    public function setImage(?string $image): Mission
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @param File|null $imageFile
     * @return Mission
     */
    public function setImageFile(?File $imageFile): Mission
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAT = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return Collection<int, Step>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Step $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setMission($this);
        }

        return $this;
    }

    public function removeStep(Step $step): self
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getMission() === $this) {
                $step->setMission(null);
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
            $order->setMission($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getMission() === $this) {
                $order->setMission(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setMissionId($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getMissionId() === $this) {
                $message->setMissionId(null);
            }
        }
        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getAgent(): ?User
    {
        return $this->agent;
    }

    public function setAgent(?User $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, self::getAllStatus())) {
            throw new \InvalidArgumentException("Status '$status' not recognized.");
        }
        
        $this->status = $status;

        return $this;
    }

    public function getReward(): ?float
    {
        return $this->reward;
    }

    public function setReward(float $reward): self
    {
        $this->reward = $reward;
        
        return $this;
    }
}
