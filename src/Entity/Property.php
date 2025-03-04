<?php

namespace App\Entity;

use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\PropertyPdf;
use App\Entity\PropertyInquiry;
use App\Entity\PropertyType;
use App\Entity\Promotion;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
#[ORM\Table(name: 'properties')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['slug'], message: 'Вече съществува имот с това заглавие')]
class Property
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_SOLD = 'sold';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_RENTED = 'rented';
    public const STATUS_PENDING = 'pending';

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

    #[ORM\Column(length: 255, nullable: true)]
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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionBg = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
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

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
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

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $area = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $pricePerSqm = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $yearBuilt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $availableFrom = null;

    #[ORM\ManyToOne(targetEntity: PropertyType::class, inversedBy: 'properties')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Моля изберете тип имот')]
    private ?PropertyType $type = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['available', 'sold', 'reserved', 'rented', 'pending'], message: 'Моля изберете валиден статус')]
    private ?string $status = self::STATUS_AVAILABLE;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isFeatured = false;

    #[ORM\Column]
    private bool $isVip = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $vipExpiration = null;

    #[ORM\OneToMany(mappedBy: 'property', targetEntity: PropertyImage::class, orphanRemoval: true, cascade: ['persist'])]
    #[Assert\Valid]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'property', targetEntity: PropertyFeature::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $features;

    #[ORM\OneToMany(mappedBy: 'property', targetEntity: PropertyPdf::class, orphanRemoval: true)]
    private Collection $pdfs;

    #[ORM\OneToMany(mappedBy: 'property', targetEntity: PropertyInquiry::class)]
    private Collection $inquiries;

    #[ORM\OneToMany(mappedBy: 'property', targetEntity: Promotion::class, orphanRemoval: true)]
    private Collection $promotions;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 8, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $referenceNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $soldAt = null;

    #[ORM\OneToMany(mappedBy: 'property', targetEntity: PropertyView::class, cascade: ['remove'])]
    private Collection $views;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->features = new ArrayCollection();
        $this->pdfs = new ArrayCollection();
        $this->inquiries = new ArrayCollection();
        $this->promotions = new ArrayCollection();
        $this->views = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        
        if (!$this->referenceNumber) {
            $this->generateReferenceNumber();
        }
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

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(string $area): self
    {
        $this->area = $area;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getPricePerSqm(): ?string
    {
        return $this->pricePerSqm;
    }

    public function setPricePerSqm(?string $pricePerSqm): self
    {
        $this->pricePerSqm = $pricePerSqm;
        return $this;
    }

    public function getYearBuilt(): ?int
    {
        return $this->yearBuilt;
    }

    public function setYearBuilt(?int $yearBuilt): self
    {
        $this->yearBuilt = $yearBuilt;
        return $this;
    }

    public function getAvailableFrom(): ?\DateTimeImmutable
    {
        return $this->availableFrom;
    }

    public function setAvailableFrom(?\DateTimeImmutable $availableFrom): self
    {
        $this->availableFrom = $availableFrom;
        return $this;
    }

    public function getType(): ?PropertyType
    {
        return $this->type;
    }

    public function setType(?PropertyType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
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

    public function isVip(): bool
    {
        if (!$this->isVip || !$this->vipExpiration) {
            return false;
        }
        return $this->vipExpiration > new \DateTimeImmutable();
    }

    public function setIsVip(bool $isVip): self
    {
        $this->isVip = $isVip;
        return $this;
    }

    public function getVipExpiration(): ?\DateTimeImmutable
    {
        return $this->vipExpiration;
    }

    public function setVipExpiration(?\DateTimeImmutable $vipExpiration): self
    {
        $this->vipExpiration = $vipExpiration;
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

    /**
     * @return Collection<int, PropertyFeature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(PropertyFeature $feature): self
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->setProperty($this);
        }
        return $this;
    }

    public function removeFeature(PropertyFeature $feature): self
    {
        if ($this->features->removeElement($feature)) {
            if ($feature->getProperty() === $this) {
                $feature->setProperty(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, PropertyPdf>
     */
    public function getPdfs(): Collection
    {
        return $this->pdfs;
    }

    public function addPdf(PropertyPdf $pdf): self
    {
        if (!$this->pdfs->contains($pdf)) {
            $this->pdfs->add($pdf);
            $pdf->setProperty($this);
        }

        return $this;
    }

    public function removePdf(PropertyPdf $pdf): self
    {
        if ($this->pdfs->removeElement($pdf)) {
            // set the owning side to null (unless already changed)
            if ($pdf->getProperty() === $this) {
                $pdf->setProperty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PropertyInquiry>
     */
    public function getInquiries(): Collection
    {
        return $this->inquiries;
    }

    public function addInquiry(PropertyInquiry $inquiry): self
    {
        if (!$this->inquiries->contains($inquiry)) {
            $this->inquiries->add($inquiry);
            $inquiry->setProperty($this);
        }
        return $this;
    }

    public function removeInquiry(PropertyInquiry $inquiry): self
    {
        if ($this->inquiries->removeElement($inquiry)) {
            if ($inquiry->getProperty() === $this) {
                $inquiry->setProperty(null);
            }
        }
        return $this;
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

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getReferenceNumber(): ?string
    {
        return $this->referenceNumber;
    }

    public function setReferenceNumber(string $referenceNumber): self
    {
        $this->referenceNumber = $referenceNumber;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getSoldAt(): ?\DateTimeInterface
    {
        return $this->soldAt;
    }

    public function setSoldAt(?\DateTimeInterface $soldAt): self
    {
        $this->soldAt = $soldAt;
        return $this;
    }

    public function getViews(): Collection
    {
        return $this->views;
    }

    public function addView(PropertyView $view): self
    {
        if (!$this->views->contains($view)) {
            $this->views[] = $view;
            $view->setProperty($this);
        }
        return $this;
    }

    private function generateReferenceNumber(): void
    {
        // Формат: IP-{ТИП}-{ГОДИНА}{МЕСЕЦ}-{СЛУЧАЕН_КОД}
        $typeMap = [
            'industrial_land' => 'IL',
            'industrial_building' => 'IB',
            'logistics_center' => 'LC',
            'warehouse' => 'WH',
            'production_facility' => 'PF'
        ];

        $typeCode = $typeMap[strtolower($this->type?->getName() ?? '')] ?? 'XX';
        $date = date('ym');
        $random = strtoupper(substr(uniqid(), -4));

        $this->referenceNumber = "IP-{$typeCode}-{$date}-{$random}";
    }

    /**
     * @return Collection<int, Promotion>
     */
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotion(Promotion $promotion): self
    {
        if (!$this->promotions->contains($promotion)) {
            $this->promotions->add($promotion);
            $promotion->setProperty($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): self
    {
        if ($this->promotions->removeElement($promotion)) {
            if ($promotion->getProperty() === $this) {
                $promotion->setProperty(null);
            }
        }

        return $this;
    }

    public function hasActivePromotion(string $type): bool
    {
        foreach ($this->promotions as $promotion) {
            if ($promotion->getType() === $type && $promotion->isActive()) {
                return true;
            }
        }
        return false;
    }

    public function getActiveVipPromotion(): ?Promotion
    {
        foreach ($this->promotions as $promotion) {
            if ($promotion->getType() === 'vip' && $promotion->isActive()) {
                return $promotion;
            }
        }
        return null;
    }

    public function getActiveFeaturedPromotion(): ?Promotion
    {
        foreach ($this->promotions as $promotion) {
            if ($promotion->getType() === 'featured' && $promotion->isActive()) {
                return $promotion;
            }
        }
        return null;
    }
} 