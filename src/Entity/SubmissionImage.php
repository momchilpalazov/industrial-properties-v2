<?php

namespace App\Entity;

use App\Repository\SubmissionImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubmissionImageRepository::class)]
#[ORM\Table(name: 'submission_images')]
class SubmissionImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PropertySubmission::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private PropertySubmission $submission;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $filename;

    #[ORM\Column(length: 255)]
    private string $originalName;

    #[ORM\Column(length: 100)]
    private string $mimeType;

    #[ORM\Column]
    private int $fileSize;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['facade', 'interior', 'equipment', 'layout', 'surroundings', 'documentation', 'other'])]
    private string $imageType;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPrimary = false;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata = null; // EXIF data, GPS coordinates, etc.

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $uploadedAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isProcessed = false;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $aiAnalysis = null; // AI-generated image analysis

    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubmission(): PropertySubmission
    {
        return $this->submission;
    }

    public function setSubmission(PropertySubmission $submission): static
    {
        $this->submission = $submission;
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): static
    {
        $this->originalName = $originalName;
        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function getImageType(): string
    {
        return $this->imageType;
    }

    public function setImageType(string $imageType): static
    {
        $this->imageType = $imageType;
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

    public function getIsPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(bool $isPrimary): static
    {
        $this->isPrimary = $isPrimary;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getUploadedAt(): \DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function getIsProcessed(): bool
    {
        return $this->isProcessed;
    }

    public function setIsProcessed(bool $isProcessed): static
    {
        $this->isProcessed = $isProcessed;
        return $this;
    }

    public function getAiAnalysis(): ?array
    {
        return $this->aiAnalysis;
    }

    public function setAiAnalysis(?array $aiAnalysis): static
    {
        $this->aiAnalysis = $aiAnalysis;
        return $this;
    }

    /**
     * Get the full file path for storage
     */
    public function getFilePath(): string
    {
        return 'uploads/submissions/' . $this->submission->getId() . '/' . $this->filename;
    }

    /**
     * Get the web-accessible URL
     */
    public function getWebPath(): string
    {
        return '/uploads/submissions/' . $this->submission->getId() . '/' . $this->filename;
    }

    /**
     * Check if image is a valid type for industrial properties
     */
    public function isValidImageType(): bool
    {
        $validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        return in_array($this->mimeType, $validTypes);
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
