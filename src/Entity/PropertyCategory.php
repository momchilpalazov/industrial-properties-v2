<?php

namespace App\Entity;

use App\Repository\PropertyCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PropertyCategoryRepository::class)]
#[ORM\Table(name: 'property_categories')]
class PropertyCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля, въведете име на български')]
    private ?string $name = null;

    #[ORM\Column(name: 'name_en', length: 255, nullable: true)]
    private ?string $nameEn = null;

    #[ORM\Column(name: 'name_de', length: 255, nullable: true)]
    private ?string $nameDe = null;

    #[ORM\Column(name: 'name_ru', length: 255, nullable: true)]
    private ?string $nameRu = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'description_en', type: 'text', nullable: true)]
    private ?string $descriptionEn = null;

    #[ORM\Column(name: 'description_de', type: 'text', nullable: true)]
    private ?string $descriptionDe = null;

    #[ORM\Column(name: 'description_ru', type: 'text', nullable: true)]
    private ?string $descriptionRu = null;

    #[ORM\Column(name: 'position', type: 'integer', options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(name: 'is_visible', type: 'boolean', options: ['default' => true])]
    private bool $isVisible = true;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: PropertyType::class)]
    private Collection $propertyTypes;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Property::class)]
    private Collection $properties;

    public function __construct()
    {
        $this->propertyTypes = new ArrayCollection();
        $this->properties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(?string $nameEn): static
    {
        $this->nameEn = $nameEn;
        return $this;
    }

    public function getNameDe(): ?string
    {
        return $this->nameDe;
    }

    public function setNameDe(?string $nameDe): static
    {
        $this->nameDe = $nameDe;
        return $this;
    }

    public function getNameRu(): ?string
    {
        return $this->nameRu;
    }

    public function setNameRu(?string $nameRu): static
    {
        $this->nameRu = $nameRu;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function setDescriptionEn(?string $descriptionEn): static
    {
        $this->descriptionEn = $descriptionEn;
        return $this;
    }

    public function getDescriptionDe(): ?string
    {
        return $this->descriptionDe;
    }

    public function setDescriptionDe(?string $descriptionDe): static
    {
        $this->descriptionDe = $descriptionDe;
        return $this;
    }

    public function getDescriptionRu(): ?string
    {
        return $this->descriptionRu;
    }

    public function setDescriptionRu(?string $descriptionRu): static
    {
        $this->descriptionRu = $descriptionRu;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): static
    {
        $this->isVisible = $isVisible;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return Collection<int, PropertyType>
     */
    public function getPropertyTypes(): Collection
    {
        return $this->propertyTypes;
    }

    public function addPropertyType(PropertyType $propertyType): static
    {
        if (!$this->propertyTypes->contains($propertyType)) {
            $this->propertyTypes->add($propertyType);
            $propertyType->setCategory($this);
        }

        return $this;
    }

    public function removePropertyType(PropertyType $propertyType): static
    {
        if ($this->propertyTypes->removeElement($propertyType)) {
            // set the owning side to null (unless already changed)
            if ($propertyType->getCategory() === $this) {
                $propertyType->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Property>
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function addProperty(Property $property): static
    {
        if (!$this->properties->contains($property)) {
            $this->properties->add($property);
            $property->setCategory($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): static
    {
        if ($this->properties->removeElement($property)) {
            // set the owning side to null (unless already changed)
            if ($property->getCategory() === $this) {
                $property->setCategory(null);
            }
        }

        return $this;
    }    /**
     * Връща локализирано име според езика
     */
    public function getLocalizedName(string $locale): string
    {
        return match($locale) {
            'en' => $this->nameEn ?: $this->name,
            'de' => $this->nameDe ?: $this->name,
            'ru' => $this->nameRu ?: $this->name,
            default => $this->name
        };
    }

    /**
     * Връща локализирано описание според езика
     */
    public function getLocalizedDescription(string $locale): ?string
    {
        return match($locale) {
            'en' => $this->descriptionEn ?: $this->description,
            'de' => $this->descriptionDe ?: $this->description,
            'ru' => $this->descriptionRu ?: $this->description,
            default => $this->description
        };
    }

    public function __toString(): string
    {
        return $this->name;
    }
} 