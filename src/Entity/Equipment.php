<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\EquipmentRepository;
use COM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
#[UniqueEntity(fields: ['name'])]
#[Vich\Uploadable]
class Equipment
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[NotBlank(null, 'a stock is required.')]
    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[NotBlank(null, 'an image is required.')]
    #[Vich\UploadableField(mapping: 'equipments', fileNameProperty: 'image')]
    #[Assert\Image(
        maxSize: '2M',
        mimeTypes: ['image/png', 'image/jpeg'],
        mimeTypesMessage: 'type image authorize: png, jpeg',
    )]
    private ?File $imageFile = null;

    #[ORM\Column(length: 105)]
    #[Slug(fields: ['name', 'createdAt'])]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'equipment', targetEntity: Order::class, orphanRemoval: true)]
    private Collection $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

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
     * @return Equipment
     */
    public function setImage(?string $image): Equipment
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
     * @return Equipment
     */
    public function setImageFile(?File $imageFile): Equipment
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAT = new \DateTime('now');
        }

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
     * @return Equipment
     */
    public function setSlug(?string $slug): Equipment
    {
        $this->slug = $slug;
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
            $order->setEquipment($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            $order->setEquipment(null);
        }

        return $this;
    }
}
