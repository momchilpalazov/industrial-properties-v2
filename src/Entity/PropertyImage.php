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
    #[Assert\NotNull(message: 'Снимката трябва да бъде свързана с имот')]
    private ?Property $property = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'property_image.path.not_blank')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Пътят до файла не може да бъде повече от {{ limit }} символа'
    )]
    private ?string $path = null;

    #[ORM\Column]
    private bool $isMain = false;

    #[ORM\Column]
    private int $sortOrder = 0;

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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
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