<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity=Mission::class)
	 */
	#[ORM\ManyToOne(targetEntity: Mission::class)]
	private ?Mission $mission;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	#[ORM\Column(type: 'integer')]
	private ?int $amount;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private ?string $stripePaymentId;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	#[ORM\Column(type: 'string', length: 255)]
	private ?string $status;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	#[ORM\Column(type: 'datetime')]
	private ?DateTimeInterface $createdAt;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?DateTimeInterface $updatedAt;
	
	//Getters and setters
	public function getId(): ?int
	{
		return $this->id;
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
	
	public function getAmount(): ?int
	{
		return $this->amount;
	}
	
	public function setAmount(int $amount): self
	{
		$this->amount = $amount;
		
		return $this;
	}
	
	public function getStripePaymentId(): ?string
	{
		return $this->stripePaymentId;
	}
	
	public function setStripePaymentId(string $stripePaymentId): self
	{
		$this->stripePaymentId = $stripePaymentId;
		
		return $this;
	}
	
	public function getStatus(): ?string
	{
		return $this->status;
	}
	
	public function setStatus(string $status): self
	{
		$this->status = $status;
		
		return $this;
	}
	
	public function getCreatedAt(): ?DateTimeInterface
	{
		return $this->createdAt;
	}
	
	public function setCreatedAt(DateTimeInterface $createdAt): self
	{
		$this->createdAt = $createdAt;
		
		return $this;
	}
	
	public function getUpdatedAt(): ?DateTimeInterface
	{
		return $this->updatedAt;
	}
	
	public function setUpdatedAt(?DateTimeInterface $updatedAt): self
	{
		$this->updatedAt = $updatedAt;
		
		return $this;
	}
}