<?php

namespace App\Entity;

use App\Repository\BlogCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BlogCategoryRepository::class)]
#[ORM\Table(name: 'blog_categories')]
class BlogCategory
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

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(name: 'is_visible', type: 'boolean', options: ['default' => true])]
    private bool $isVisible = true;

    #[ORM\Column(name: 'position', type: 'integer', options: ['default' => 0])]
    private int $position = 0;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: BlogPost::class)]
    private Collection $blogPosts;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->blogPosts = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->isVisible = true;
        $this->position = 0;
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

    /**
     * Връща името на категорията според зададения локал
     */
    public function name(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->nameEn ?: $this->name,
            'de' => $this->nameDe ?: $this->name,
            'ru' => $this->nameRu ?: $this->name,
            default => $this->name,
        };
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return Collection<int, BlogPost>
     */
    public function getBlogPosts(): Collection
    {
        return $this->blogPosts;
    }

    public function addBlogPost(BlogPost $blogPost): static
    {
        if (!$this->blogPosts->contains($blogPost)) {
            $this->blogPosts->add($blogPost);
            $blogPost->setCategory($this);
        }

        return $this;
    }

    public function removeBlogPost(BlogPost $blogPost): static
    {
        if ($this->blogPosts->removeElement($blogPost)) {
            if ($blogPost->getCategory() === $this) {
                $blogPost->setCategory(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Връща локализираното име според езика
     */
    public function getLocalizedName(string $locale): string
    {
        return match ($locale) {
            'en' => $this->nameEn ?? $this->name,
            'de' => $this->nameDe ?? $this->name,
            'ru' => $this->nameRu ?? $this->name,
            default => $this->name,
        };
    }

    /**
     * Връща локализираното описание според езика
     */
    public function getLocalizedDescription(string $locale): ?string
    {
        return match ($locale) {
            'en' => $this->descriptionEn ?? $this->description,
            'de' => $this->descriptionDe ?? $this->description,
            'ru' => $this->descriptionRu ?? $this->description,
            default => $this->description,
        };
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
