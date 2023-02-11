<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[Vich\Uploadable]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[Assert\NotBlank()]
    #[Vich\UploadableField(mapping: 'documents', fileNameProperty: 'path')]
    #[Assert\File(
        maxSize: '4M',
        mimeTypes: ['image/png', 'image/jpeg', 'application/pdf'],
        mimeTypesMessage: 'type files: PNG, JPEG or PDF',
    )]
    private ?File $document = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $submitedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocument(): ?File
    {
        return $this->document;
    }

    public function setDocument(?File $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getSubmitedBy(): ?User
    {
        return $this->submitedBy;
    }

    public function setSubmitedBy(?User $submitedBy): self
    {
        $this->submitedBy = $submitedBy;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;
        return $this;
    }
}
