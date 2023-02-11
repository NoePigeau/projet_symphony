<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;


#[ORM\Entity(repositoryClass: RatingRepository::class)]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[NotBlank()]
    #[ORM\Column]
    private ?int $rate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $opinion = null;

    #[ORM\ManyToOne(inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $agent = null;
	
	#[ORM\ManyToOne(targetEntity: Mission::class, inversedBy: "ratings")]
	#[ORM\JoinColumn(nullable: false)]
	private ?Mission $mission = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getOpinion(): ?string
    {
        return $this->opinion;
    }

    public function setOpinion(?string $opinion): self
    {
        $this->opinion = $opinion;

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
	
	public function getMission(): ?Mission
	{
		return $this->mission;
	}
	
	public function setMission(?Mission $mission): self
	{
		$this->mission = $mission;
		return $this;
	}
}
