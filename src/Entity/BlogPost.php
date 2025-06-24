<?php

namespace App\Entity;

use App\Repository\BlogPostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
#[ORM\Table(name: 'blog_posts')]
#[ORM\HasLifecycleCallbacks]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете заглавие на български')]
    private ?string $titleBg = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете заглавие на английски')]
    private ?string $titleEn = null;

    #[ORM\Column(name: 'title_de', length: 255, nullable: true)]
    private ?string $titleDe = null;

    #[ORM\Column(name: 'title_ru', length: 255, nullable: true)]
    private ?string $titleRu = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Моля въведете съдържание на български')]
    private ?string $contentBg = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Моля въведете съдържание на английски')]
    private ?string $contentEn = null;

    #[ORM\Column(name: 'content_de', type: Types::TEXT, nullable: true)]
    private ?string $contentDe = null;

    #[ORM\Column(name: 'content_ru', type: Types::TEXT, nullable: true)]
    private ?string $contentRu = null;

    #[ORM\ManyToOne(targetEntity: BlogCategory::class, inversedBy: 'blogPosts')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true)]
    #[Assert\NotNull(message: 'Моля изберете категория')]
    private ?BlogCategory $category = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $excerptBg = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $excerptEn = null;

    #[ORM\Column(name: 'excerpt_de', length: 500, nullable: true)]
    private ?string $excerptDe = null;

    #[ORM\Column(name: 'excerpt_ru', length: 500, nullable: true)]
    private ?string $excerptRu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank(message: 'Моля изберете език')]
    #[Assert\Choice(choices: ['bg', 'en', 'de', 'ru'], message: 'Моля изберете валиден език')]
    private ?string $language = 'bg';

    private ?File $imageFile = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->publishedAt = new \DateTimeImmutable();
        $this->isPublished = false;
        $this->language = 'bg';
    }

    // Removed PrePersist - slug generation moved to Controller/Service level
    #[ORM\PreUpdate]
    public function updateSlug(): void
    {
        if (empty($this->titleBg) && empty($this->titleEn)) {
            return;
        }

        $slugger = new AsciiSlugger();
        $title = $this->language === 'bg' ? $this->titleBg : $this->titleEn;
        $this->slug = strtolower($slugger->slug($title));
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
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

    public function getContentBg(): ?string
    {
        return $this->contentBg;
    }

    public function setContentBg(string $contentBg): self
    {
        $this->contentBg = $contentBg;
        return $this;
    }

    public function getContentEn(): ?string
    {
        return $this->contentEn;
    }

    public function setContentEn(string $contentEn): self
    {
        $this->contentEn = $contentEn;
        return $this;
    }

    public function getCategory(): ?BlogCategory
    {
        return $this->category;
    }

    public function setCategory(?BlogCategory $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;
        return $this;
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

    public function getExcerptBg(): ?string
    {
        return $this->excerptBg;
    }

    public function setExcerptBg(?string $excerptBg): self
    {
        $this->excerptBg = $excerptBg;
        return $this;
    }

    public function getExcerptEn(): ?string
    {
        return $this->excerptEn;
    }

    public function setExcerptEn(?string $excerptEn): self
    {
        $this->excerptEn = $excerptEn;
        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function getTitle(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->titleEn,
            'de' => $this->titleDe,
            'ru' => $this->titleRu,
            default => $this->titleBg,
        };
    }

    public function getContent(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->contentEn,
            'de' => $this->contentDe,
            'ru' => $this->contentRu,
            default => $this->contentBg,
        };
    }

    public function getExcerpt(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->excerptEn,
            'de' => $this->excerptDe,
            'ru' => $this->excerptRu,
            default => $this->excerptBg,
        };
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;
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

    public function getContentDe(): ?string
    {
        return $this->contentDe;
    }

    public function setContentDe(?string $contentDe): self
    {
        $this->contentDe = $contentDe;
        return $this;
    }

    public function getContentRu(): ?string
    {
        return $this->contentRu;
    }

    public function setContentRu(?string $contentRu): self
    {
        $this->contentRu = $contentRu;
        return $this;
    }

    public function getExcerptDe(): ?string
    {
        return $this->excerptDe;
    }

    public function setExcerptDe(?string $excerptDe): self
    {
        $this->excerptDe = $excerptDe;
        return $this;
    }

    public function getExcerptRu(): ?string
    {
        return $this->excerptRu;
    }

    public function setExcerptRu(?string $excerptRu): self
    {
        $this->excerptRu = $excerptRu;
        return $this;
    }

    /**
     * Връща локализираното заглавие според езика
     */
    public function getLocalizedTitle(string $locale): ?string
    {
        return match ($locale) {
            'en' => $this->titleEn ?? $this->titleBg,
            'de' => $this->titleDe ?? $this->titleBg,
            'ru' => $this->titleRu ?? $this->titleBg,
            default => $this->titleBg,
        };
    }

    /**
     * Връща локализираното съдържание според езика
     */
    public function getLocalizedContent(string $locale): ?string
    {
        return match ($locale) {
            'en' => $this->contentEn ?? $this->contentBg,
            'de' => $this->contentDe ?? $this->contentBg,
            'ru' => $this->contentRu ?? $this->contentBg,
            default => $this->contentBg,
        };
    }

    /**
     * Връща локализираното кратко описание според езика
     */
    public function getLocalizedExcerpt(string $locale): ?string
    {
        return match ($locale) {
            'en' => $this->excerptEn ?? $this->excerptBg,
            'de' => $this->excerptDe ?? $this->excerptBg,
            'ru' => $this->excerptRu ?? $this->excerptBg,
            default => $this->excerptBg,
        };
    }
} 