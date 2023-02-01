<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    const STATUS_IN_DEMAND = 'in_demand';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REFUSED = 'refused';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $agent = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Mission $mission = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipment $equipment= null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(length: 30)]
    private ?string $status = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMission(): ?Mission
    {
        return $this->mission;
    }

    public function setMission(?Mission $mission): self
    {
        $this->mission = $mission;

        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(?Equipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, [self::STATUS_IN_DEMAND, self::STATUS_ACCEPTED, self::STATUS_REFUSED])) {
            throw new \InvalidArgumentException("Status '$status' not recognized.");
        }

        $this->status = $status;

        return $this;
    }
}
