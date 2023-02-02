<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;

trait SlugableTrait 
{
    #[ORM\Column(length: 105)]
    #[Slug(fields: ['name', 'createdAt'])]
    private ?string $slug = null;

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     */
    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }
}