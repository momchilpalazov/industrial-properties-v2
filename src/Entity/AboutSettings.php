<?php

namespace App\Entity;

use App\Repository\AboutSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AboutSettingsRepository::class)]
#[ORM\Table(name: 'about_settings')]
class AboutSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Заглавна секция
    #[ORM\Column(length: 255)]
    private ?string $titleBg = null;

    #[ORM\Column(length: 255)]
    private ?string $titleEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titleDe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titleRu = null;

    #[ORM\Column(length: 255)]
    private ?string $subtitleBg = null;

    #[ORM\Column(length: 255)]
    private ?string $subtitleEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subtitleDe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subtitleRu = null;

    // История секция
    #[ORM\Column(type: 'text')]
    private ?string $contentBg = null;

    #[ORM\Column(type: 'text')]
    private ?string $contentEn = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contentDe = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contentRu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyImage = null;

    // Ценности секция
    #[ORM\Column(type: 'json')]
    private array $valuesBg = [];

    #[ORM\Column(type: 'json')]
    private array $valuesEn = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $valuesDe = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $valuesRu = [];

    // Екип секция
    #[ORM\Column(type: 'json')]
    private array $team = [];

    // Meta данни
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $metaTitleBg = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $metaTitleEn = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $metaDescriptionBg = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $metaDescriptionEn = null;

    public function __construct()
    {
        $this->valuesBg = [];
        $this->valuesEn = [];
        $this->valuesDe = [];
        $this->valuesRu = [];
        $this->team = [];
    }

    // Getters and setters
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

    public function getSubtitleBg(): ?string
    {
        return $this->subtitleBg;
    }

    public function setSubtitleBg(string $subtitleBg): self
    {
        $this->subtitleBg = $subtitleBg;
        return $this;
    }

    public function getSubtitleEn(): ?string
    {
        return $this->subtitleEn;
    }

    public function setSubtitleEn(string $subtitleEn): self
    {
        $this->subtitleEn = $subtitleEn;
        return $this;
    }

    public function getSubtitleDe(): ?string
    {
        return $this->subtitleDe;
    }

    public function setSubtitleDe(?string $subtitleDe): self
    {
        $this->subtitleDe = $subtitleDe;
        return $this;
    }

    public function getSubtitleRu(): ?string
    {
        return $this->subtitleRu;
    }

    public function setSubtitleRu(?string $subtitleRu): self
    {
        $this->subtitleRu = $subtitleRu;
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

    public function getCompanyImage(): ?string
    {
        return $this->companyImage;
    }

    public function setCompanyImage(?string $companyImage): self
    {
        $this->companyImage = $companyImage;
        return $this;
    }

    public function getValuesBg(): array
    {
        return $this->valuesBg;
    }

    public function setValuesBg(array $valuesBg): self
    {
        $this->valuesBg = $valuesBg;
        return $this;
    }

    public function getValuesEn(): array
    {
        return $this->valuesEn;
    }

    public function setValuesEn(array $valuesEn): self
    {
        $this->valuesEn = $valuesEn;
        return $this;
    }

    public function getValuesDe(): array
    {
        return $this->valuesDe;
    }

    public function setValuesDe(?array $valuesDe): self
    {
        $this->valuesDe = $valuesDe;
        return $this;
    }

    public function getValuesRu(): array
    {
        return $this->valuesRu;
    }

    public function setValuesRu(?array $valuesRu): self
    {
        $this->valuesRu = $valuesRu;
        return $this;
    }

    public function getTeam(): array
    {
        return $this->team;
    }

    public function setTeam(array $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function getMetaTitleBg(): ?string
    {
        return $this->metaTitleBg;
    }

    public function setMetaTitleBg(?string $metaTitleBg): self
    {
        $this->metaTitleBg = $metaTitleBg;
        return $this;
    }

    public function getMetaTitleEn(): ?string
    {
        return $this->metaTitleEn;
    }

    public function setMetaTitleEn(?string $metaTitleEn): self
    {
        $this->metaTitleEn = $metaTitleEn;
        return $this;
    }

    public function getMetaDescriptionBg(): ?string
    {
        return $this->metaDescriptionBg;
    }

    public function setMetaDescriptionBg(?string $metaDescriptionBg): self
    {
        $this->metaDescriptionBg = $metaDescriptionBg;
        return $this;
    }

    public function getMetaDescriptionEn(): ?string
    {
        return $this->metaDescriptionEn;
    }

    public function setMetaDescriptionEn(?string $metaDescriptionEn): self
    {
        $this->metaDescriptionEn = $metaDescriptionEn;
        return $this;
    }
} 