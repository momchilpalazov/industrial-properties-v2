<?php

namespace App\Entity;

use App\Repository\PropertyFeatureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PropertyFeatureRepository::class)]
#[ORM\Table(name: 'property_features')]
#[ORM\HasLifecycleCallbacks]
class PropertyFeature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'features')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Property $property = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'property_feature.feature_bg.not_blank')]
    private ?string $featureBg = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featureEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featureDe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featureRu = null;

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

    public function getFeature(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->featureEn ?? $this->featureBg,
            'de' => $this->featureDe ?? $this->featureBg,
            'ru' => $this->featureRu ?? $this->featureBg,
            default => $this->featureBg,
        };
    }

    public function getFeatureBg(): ?string
    {
        return $this->featureBg;
    }

    public function setFeatureBg(string $featureBg): self
    {
        $this->featureBg = $featureBg;
        return $this;
    }

    public function getFeatureEn(): ?string
    {
        return $this->featureEn;
    }

    public function setFeatureEn(?string $featureEn): self
    {
        $this->featureEn = $featureEn;
        return $this;
    }

    public function getFeatureDe(): ?string
    {
        return $this->featureDe;
    }

    public function setFeatureDe(?string $featureDe): self
    {
        $this->featureDe = $featureDe;
        return $this;
    }

    public function getFeatureRu(): ?string
    {
        return $this->featureRu;
    }

    public function setFeatureRu(?string $featureRu): self
    {
        $this->featureRu = $featureRu;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
} 