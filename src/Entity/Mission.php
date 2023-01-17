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
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column()]
    #[SortablePosition]
    private ?int $position = null;

    #[Assert\NotBlank(message: 'Cette valeur ne peut pas être vide.')]
    #[Assert\Type('string', message: 'Cette valeur doit être une chaine de caractère.')]
    #[Assert\Length(min: 10, max: 100, minMessage: 'Vous devez avoir au moins 10 caractères.', maxMessage: 'Vous devez avoir maximum 100 caractères.')]
    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 105)]
    #[Slug(fields: ['name', 'startDate'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan('now', message: 'Merci de choisir une date dans le futur')]
    #[Assert\LessThan('+2 years', message: 'Merci de choisir une date dans les 2 prochaines années')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'missions')]
    private ?Type $type = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'missions')]
    private Collection $participants;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'missions', fileNameProperty: 'image')]
    #[Assert\Image(
        maxSize: '2M',
        mimeTypes: ['image/png', 'image/jpeg'],
        maxRatio: '1',
        mimeTypesMessage: 'coucou, tu peux mettre que des png ou jpeg',
        maxRatioMessage: 'Votre ratio d\'image est de {{ ratio }}, celui accepté est {{ max_ratio }}'
    )]
    private ?File $imageFile = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     * @return Mission
     */
    public function setPosition(?int $position): Mission
    {
        $this->position = $position;
        return $this;
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

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
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        $this->participants->removeElement($participant);

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
}
