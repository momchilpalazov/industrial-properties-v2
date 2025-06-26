<?php

namespace App\Entity;

use App\Repository\ContactSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactSettingsRepository::class)]
#[ORM\Table(name: 'contact_settings')]
class ContactSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Title and subtitle for each language
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title_bg = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title_en = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title_de = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title_ru = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $subtitle_bg = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $subtitle_en = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $subtitle_de = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $subtitle_ru = null;

    // Contact information
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $company_name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $working_hours = null;

    // Map coordinates
    #[ORM\Column(type: 'decimal', precision: 10, scale: 8, nullable: true)]
    private ?string $map_lat = null;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private ?string $map_lng = null;

    // Social media links
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $social_media = null;

    // Meta data for SEO
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $meta_title_bg = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $meta_title_en = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $meta_title_de = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $meta_title_ru = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $meta_description_bg = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $meta_description_en = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $meta_description_de = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $meta_description_ru = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitleBg(): ?string
    {
        return $this->title_bg;
    }

    public function setTitleBg(?string $title_bg): static
    {
        $this->title_bg = $title_bg;
        return $this;
    }

    public function getTitleEn(): ?string
    {
        return $this->title_en;
    }

    public function setTitleEn(?string $title_en): static
    {
        $this->title_en = $title_en;
        return $this;
    }

    public function getTitleDe(): ?string
    {
        return $this->title_de;
    }

    public function setTitleDe(?string $title_de): static
    {
        $this->title_de = $title_de;
        return $this;
    }

    public function getTitleRu(): ?string
    {
        return $this->title_ru;
    }

    public function setTitleRu(?string $title_ru): static
    {
        $this->title_ru = $title_ru;
        return $this;
    }

    public function getSubtitleBg(): ?string
    {
        return $this->subtitle_bg;
    }

    public function setSubtitleBg(?string $subtitle_bg): static
    {
        $this->subtitle_bg = $subtitle_bg;
        return $this;
    }

    public function getSubtitleEn(): ?string
    {
        return $this->subtitle_en;
    }

    public function setSubtitleEn(?string $subtitle_en): static
    {
        $this->subtitle_en = $subtitle_en;
        return $this;
    }

    public function getSubtitleDe(): ?string
    {
        return $this->subtitle_de;
    }

    public function setSubtitleDe(?string $subtitle_de): static
    {
        $this->subtitle_de = $subtitle_de;
        return $this;
    }

    public function getSubtitleRu(): ?string
    {
        return $this->subtitle_ru;
    }

    public function setSubtitleRu(?string $subtitle_ru): static
    {
        $this->subtitle_ru = $subtitle_ru;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    public function setCompanyName(?string $company_name): static
    {
        $this->company_name = $company_name;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getWorkingHours(): ?string
    {
        return $this->working_hours;
    }

    public function setWorkingHours(?string $working_hours): static
    {
        $this->working_hours = $working_hours;
        return $this;
    }

    public function getMapLat(): ?string
    {
        return $this->map_lat;
    }

    public function setMapLat(?string $map_lat): static
    {
        $this->map_lat = $map_lat;
        return $this;
    }

    public function getMapLng(): ?string
    {
        return $this->map_lng;
    }

    public function setMapLng(?string $map_lng): static
    {
        $this->map_lng = $map_lng;
        return $this;
    }

    public function getSocialMedia(): ?array
    {
        return $this->social_media;
    }

    public function setSocialMedia(?array $social_media): static
    {
        $this->social_media = $social_media;
        return $this;
    }

    public function getMetaTitleBg(): ?string
    {
        return $this->meta_title_bg;
    }

    public function setMetaTitleBg(?string $meta_title_bg): static
    {
        $this->meta_title_bg = $meta_title_bg;
        return $this;
    }

    public function getMetaTitleEn(): ?string
    {
        return $this->meta_title_en;
    }

    public function setMetaTitleEn(?string $meta_title_en): static
    {
        $this->meta_title_en = $meta_title_en;
        return $this;
    }

    public function getMetaTitleDe(): ?string
    {
        return $this->meta_title_de;
    }

    public function setMetaTitleDe(?string $meta_title_de): static
    {
        $this->meta_title_de = $meta_title_de;
        return $this;
    }

    public function getMetaTitleRu(): ?string
    {
        return $this->meta_title_ru;
    }

    public function setMetaTitleRu(?string $meta_title_ru): static
    {
        $this->meta_title_ru = $meta_title_ru;
        return $this;
    }

    public function getMetaDescriptionBg(): ?string
    {
        return $this->meta_description_bg;
    }

    public function setMetaDescriptionBg(?string $meta_description_bg): static
    {
        $this->meta_description_bg = $meta_description_bg;
        return $this;
    }

    public function getMetaDescriptionEn(): ?string
    {
        return $this->meta_description_en;
    }

    public function setMetaDescriptionEn(?string $meta_description_en): static
    {
        $this->meta_description_en = $meta_description_en;
        return $this;
    }

    public function getMetaDescriptionDe(): ?string
    {
        return $this->meta_description_de;
    }

    public function setMetaDescriptionDe(?string $meta_description_de): static
    {
        $this->meta_description_de = $meta_description_de;
        return $this;
    }

    public function getMetaDescriptionRu(): ?string
    {
        return $this->meta_description_ru;
    }

    public function setMetaDescriptionRu(?string $meta_description_ru): static
    {
        $this->meta_description_ru = $meta_description_ru;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get map coordinates as array
     */
    public function getMapCoordinates(): ?array
    {
        if ($this->map_lat && $this->map_lng) {
            return [
                'lat' => (float) $this->map_lat,
                'lng' => (float) $this->map_lng
            ];
        }
        return null;
    }

    /**
     * Set map coordinates from array
     */
    public function setMapCoordinates(?array $coordinates): static
    {
        if ($coordinates && isset($coordinates['lat'], $coordinates['lng'])) {
            $this->map_lat = (string) $coordinates['lat'];
            $this->map_lng = (string) $coordinates['lng'];
        } else {
            $this->map_lat = null;
            $this->map_lng = null;
        }
        return $this;
    }
}
