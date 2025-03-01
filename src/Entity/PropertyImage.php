<?php

namespace App\Entity;

use App\Repository\PropertyImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PropertyImageRepository::class)]
#[ORM\Table(name: 'property_images')]
#[ORM\HasLifecycleCallbacks]
class PropertyImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Property $property = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $filename = null;

    #[ORM\Column]
    private bool $isMain = false;

    #[ORM\Column]
    private bool $is360 = false;

    #[ORM\Column]
    private ?int $position = null;

    #[Assert\File(
        maxSize: '5M',
        mimeTypes: [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ],
        mimeTypesMessage: 'Моля качете валидна снимка (JPEG, PNG, GIF или WEBP)',
        maxSizeMessage: 'Снимката не може да бъде по-голяма от {{ limit }}{{ suffix }}'
    )]
    private $file;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProperty(): ?Property
    {
        return $this->property;
    }

    public function setProperty(?Property $property): self
    {
        $this->property = $property;
        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;
        return $this;
    }

    public function is360(): bool
    {
        return $this->is360;
    }

    public function setIs360(bool $is360): self
    {
        $this->is360 = $is360;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file): self
    {
        $this->file = $file;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
} 