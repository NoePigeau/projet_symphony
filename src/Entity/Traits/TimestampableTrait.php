<?php

namespace App\Entity\Traits;

use Gedmo\Mapping\Annotation\Timestampable;
use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Timestampable(on: 'create')]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Timestampable(on: 'update')]
    private ?\DateTime $updatedAT = null;

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return self
     */
    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAT(): ?\DateTime
    {
        return $this->updatedAT;
    }

    /**
     * @param \DateTime|null $updatedAT
     * @return self
     */
    public function setUpdatedAT(?\DateTime $updatedAT): self
    {
        $this->updatedAT = $updatedAT;
        return $this;
    }

}