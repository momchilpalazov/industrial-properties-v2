<?php

namespace App\Entity;

use App\Repository\PropertyTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PropertyTypeRepository::class)]
#[ORM\Table(name: 'property_types')]
class PropertyType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля, въведете име на български')]
    private ?string $name = null;

    #[ORM\Column(name: 'name_en', length: 255, nullable: true)]
    private ?string $nameEn = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'description_en', type: 'text', nullable: true)]
    private ?string $descriptionEn = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Property::class)]
    private Collection $properties;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true)]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['persist'])]
    private Collection $children;

    #[ORM\Column(name: 'level', type: 'integer', options: ['default' => 0])]
    private int $level = 0;

    #[ORM\Column(name: 'position', type: 'integer', options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(name: 'is_visible', type: 'boolean', options: ['default' => true])]
    private bool $isVisible = true;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
        $this->children = new ArrayCollection();
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
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(?string $nameEn): static
    {
        $this->nameEn = $nameEn;
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
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function setDescriptionEn(?string $descriptionEn): static
    {
        $this->descriptionEn = $descriptionEn;
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
            $property->setType($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): static
    {
        if ($this->properties->removeElement($property)) {
            if ($property->getType() === $this) {
                $property->setType(null);
            }
        }

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;
        
        // Автоматично задаваме нивото на категорията
        if ($parent) {
            $this->level = $parent->getLevel() + 1;
        } else {
            $this->level = 0;
        }
        
        return $this;
    }

    /**
     * @return Collection<int, PropertyType>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * Връща само директните деца, сортирани по позиция
     */
    public function getVisibleChildren(): Collection
    {
        $criteria = \Doctrine\Common\Collections\Criteria::create()
            ->where(\Doctrine\Common\Collections\Criteria::expr()->eq('isVisible', true))
            ->orderBy(['position' => 'ASC']);
            
        return $this->children->matching($criteria);
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }

    public function isChildOf(self $propertyType): bool
    {
        return $this->parent === $propertyType;
    }

    /**
     * Проверява дали категорията е потомък (на всяко ниво) на дадена категория
     */
    public function isDescendantOf(self $propertyType): bool
    {
        $parent = $this->parent;
        
        while ($parent !== null) {
            if ($parent === $propertyType) {
                return true;
            }
            $parent = $parent->getParent();
        }
        
        return false;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;
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

    /**
     * Връща пълния път до категорията (всички родители)
     * @return PropertyType[]
     */
    public function getPath(): array
    {
        $path = [];
        $current = $this;
        
        while ($current !== null) {
            array_unshift($path, $current);
            $current = $current->getParent();
        }
        
        return $path;
    }

    /**
     * Връща форматирано име с отстъп според нивото
     */
    public function getIndentedName(): string
    {
        $prefix = str_repeat('— ', $this->level);
        return $prefix . $this->name;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getLocalizedName(string $locale): string
    {
        return $locale === 'en' ? ($this->nameEn ?? $this->name) : $this->name;
    }
} 