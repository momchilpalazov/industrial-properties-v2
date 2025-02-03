<?php

namespace App\Entity;

use App\Repository\PropertyImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PropertyImageRepository::class)]
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
    #[Assert\NotBlank(message: 'Пътят до файла е задължителен')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Пътят до файла не може да бъде повече от {{ limit }} символа'
    )]
    private ?string $path = null;

    #[ORM\Column]
    private bool $isMain = false;

    #[ORM\Column]
    private int $position = 0;

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

    public function __construct()
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

    public function getPosition(): int
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