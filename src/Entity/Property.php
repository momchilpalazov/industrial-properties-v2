<?php

namespace App\Entity;

use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['slug'], message: 'Вече съществува имот с това заглавие')]
class Property
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете заглавие на български')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Заглавието трябва да бъде поне {{ limit }} символа',
        maxMessage: 'Заглавието не може да бъде повече от {{ limit }} символа'
    )]
    private ?string $titleBg = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете заглавие на английски')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Заглавието трябва да бъде поне {{ limit }} символа',
        maxMessage: 'Заглавието не може да бъде повече от {{ limit }} символа'
    )]
    private ?string $titleEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Заглавието не може да бъде повече от {{ limit }} символа'
    )]
    private ?string $titleDe = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Заглавието не може да бъде повече от {{ limit }} символа'
    )]
    private ?string $titleRu = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Моля въведете описание на български')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Описанието трябва да бъде поне {{ limit }} символа'
    )]
    private ?string $descriptionBg = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Моля въведете описание на английски')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Описанието трябва да бъде поне {{ limit }} символа'
    )]
    private ?string $descriptionEn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionDe = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionRu = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете локация на български')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Локацията трябва да бъде поне {{ limit }} символа',
        maxMessage: 'Локацията не може да бъде повече от {{ limit }} символа'
    )]
    private ?string $locationBg = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете локация на английски')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Локацията трябва да бъде поне {{ limit }} символа',
        maxMessage: 'Локацията не може да бъде повече от {{ limit }} символа'
    )]
    private ?string $locationEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Локацията не може да бъде повече от {{ limit }} символа'
    )]
    private ?string $locationDe = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Локацията не може да бъде повече от {{ limit }} символа'
    )]
    private ?string $locationRu = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Моля въведете площ')]
    #[Assert\Positive(message: 'Площта трябва да бъде положително число')]
    #[Assert\LessThan(
        value: 1000000,
        message: 'Площта не може да бъде повече от {{ compared_value }} кв.м.'
    )]
    private ?float $area = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Моля въведете цена')]
    #[Assert\Positive(message: 'Цената трябва да бъде положително число')]
    #[Assert\LessThan(
        value: 1000000000,
        message: 'Цената не може да бъде повече от {{ compared_value }} €'
    )]
    private ?float $price = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Моля изберете тип имот')]
    #[Assert\Choice(
        choices: ['industrial_land', 'industrial_building', 'logistics_center', 'warehouse', 'production_facility'],
        message: 'Моля изберете валиден тип имот'
    )]
    private ?string $type = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isFeatured = false;

    #[ORM\OneToMany(mappedBy: 'property', targetEntity: PropertyImage::class, orphanRemoval: true, cascade: ['persist'])]
    #[Assert\Valid]
    private Collection $images;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitleBg(): ?string
    {
        return $this->titleBg;
    }

    public function setTitleBg(string $titleBg): self
    {
        $this->titleBg = $titleBg;
        return $this;
    }

    public function getTitleEn(): ?string
    {
        return $this->titleEn;
    }

    public function setTitleEn(string $titleEn): self
    {
        $this->titleEn = $titleEn;
        return $this;
    }

    public function getTitleDe(): ?string
    {
        return $this->titleDe;
    }

    public function setTitleDe(?string $titleDe): self
    {
        $this->titleDe = $titleDe;
        return $this;
    }

    public function getTitleRu(): ?string
    {
        return $this->titleRu;
    }

    public function setTitleRu(?string $titleRu): self
    {
        $this->titleRu = $titleRu;
        return $this;
    }

    public function getTitle(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->titleEn,
            'de' => $this->titleDe ?? $this->titleEn,
            'ru' => $this->titleRu ?? $this->titleEn,
            default => $this->titleBg,
        };
    }

    public function getDescriptionBg(): ?string
    {
        return $this->descriptionBg;
    }

    public function setDescriptionBg(string $descriptionBg): self
    {
        $this->descriptionBg = $descriptionBg;
        return $this;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function setDescriptionEn(string $descriptionEn): self
    {
        $this->descriptionEn = $descriptionEn;
        return $this;
    }

    public function getDescriptionDe(): ?string
    {
        return $this->descriptionDe;
    }

    public function setDescriptionDe(?string $descriptionDe): self
    {
        $this->descriptionDe = $descriptionDe;
        return $this;
    }

    public function getDescriptionRu(): ?string
    {
        return $this->descriptionRu;
    }

    public function setDescriptionRu(?string $descriptionRu): self
    {
        $this->descriptionRu = $descriptionRu;
        return $this;
    }

    public function getDescription(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->descriptionEn,
            'de' => $this->descriptionDe ?? $this->descriptionEn,
            'ru' => $this->descriptionRu ?? $this->descriptionEn,
            default => $this->descriptionBg,
        };
    }

    public function getLocationBg(): ?string
    {
        return $this->locationBg;
    }

    public function setLocationBg(string $locationBg): self
    {
        $this->locationBg = $locationBg;
        return $this;
    }

    public function getLocationEn(): ?string
    {
        return $this->locationEn;
    }

    public function setLocationEn(string $locationEn): self
    {
        $this->locationEn = $locationEn;
        return $this;
    }

    public function getLocationDe(): ?string
    {
        return $this->locationDe;
    }

    public function setLocationDe(?string $locationDe): self
    {
        $this->locationDe = $locationDe;
        return $this;
    }

    public function getLocationRu(): ?string
    {
        return $this->locationRu;
    }

    public function setLocationRu(?string $locationRu): self
    {
        $this->locationRu = $locationRu;
        return $this;
    }

    public function getLocation(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->locationEn,
            'de' => $this->locationDe ?? $this->locationEn,
            'ru' => $this->locationRu ?? $this->locationEn,
            default => $this->locationBg,
        };
    }

    public function getArea(): ?float
    {
        return $this->area;
    }

    public function setArea(float $area): self
    {
        $this->area = $area;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): self
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    /**
     * @return Collection<int, PropertyImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(PropertyImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProperty($this);
        }
        return $this;
    }

    public function removeImage(PropertyImage $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getProperty() === $this) {
                $image->setProperty(null);
            }
        }
        return $this;
    }

    public function getMainImage(): ?PropertyImage
    {
        foreach ($this->images as $image) {
            if ($image->isMain()) {
                return $image;
            }
        }
        return $this->images->first() ?: null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }
} 