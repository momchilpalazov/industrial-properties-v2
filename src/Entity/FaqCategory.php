<?php

namespace App\Entity;

use App\Repository\FaqCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FaqCategoryRepository::class)]
#[ORM\Table(name: 'faq_category')]
class FaqCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'name_bg', length: 255)]
    #[Assert\NotBlank(message: 'Моля, въведете име на български')]
    private ?string $nameBg = null;

    #[ORM\Column(name: 'name_en', length: 255)]
    #[Assert\NotBlank(message: 'Моля, въведете име на английски')]
    private ?string $nameEn = null;

    #[ORM\Column(name: 'name_de', length: 255)]
    #[Assert\NotBlank(message: 'Моля, въведете име на немски')]
    private ?string $nameDe = null;

    #[ORM\Column(name: 'name_ru', length: 255)]
    #[Assert\NotBlank(message: 'Моля, въведете име на руски')]
    private ?string $nameRu = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Slug е задължителен')]
    private ?string $slug = null;

    #[ORM\Column(name: 'position', type: 'integer', options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(name: 'is_visible', type: 'boolean', options: ['default' => true])]
    private bool $isVisible = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Faq::class, cascade: ['persist'])]
    private Collection $faqs;

    public function __construct()
    {
        $this->faqs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameBg(): ?string
    {
        return $this->nameBg;
    }

    public function setNameBg(string $nameBg): self
    {
        $this->nameBg = $nameBg;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): self
    {
        $this->nameEn = $nameEn;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getNameDe(): ?string
    {
        return $this->nameDe;
    }

    public function setNameDe(string $nameDe): self
    {
        $this->nameDe = $nameDe;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getNameRu(): ?string
    {
        return $this->nameRu;
    }

    public function setNameRu(string $nameRu): self
    {
        $this->nameRu = $nameRu;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): self
    {
        $this->isVisible = $isVisible;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection<int, Faq>
     */
    public function getFaqs(): Collection
    {
        return $this->faqs;
    }

    public function addFaq(Faq $faq): self
    {
        if (!$this->faqs->contains($faq)) {
            $this->faqs->add($faq);
            $faq->setCategory($this);
        }
        return $this;
    }

    public function removeFaq(Faq $faq): self
    {
        if ($this->faqs->removeElement($faq)) {
            if ($faq->getCategory() === $this) {
                $faq->setCategory(null);
            }
        }
        return $this;
    }

    /**
     * Връща името на категорията за дадена локализация
     */
    public function getName(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->nameEn,
            'de' => $this->nameDe,
            'ru' => $this->nameRu,
            default => $this->nameBg,
        };
    }

    /**
     * Брой активни FAQ въпроси в тази категория
     */
    public function getActiveFaqsCount(): int
    {
        return $this->faqs->filter(fn(Faq $faq) => $faq->isActive())->count();
    }

    public function __toString(): string
    {
        return $this->nameBg ?? '';
    }
}