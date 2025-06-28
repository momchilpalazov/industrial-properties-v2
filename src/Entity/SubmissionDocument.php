<?php

namespace App\Entity;

use App\Repository\SubmissionDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubmissionDocumentRepository::class)]
#[ORM\Table(name: 'submission_documents')]
class SubmissionDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PropertySubmission::class, inversedBy: 'documents')]
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
    #[Assert\Choice(choices: [
        'floor_plan', 'technical_drawing', 'permit', 'certificate', 
        'energy_audit', 'environmental_report', 'property_deed', 
        'lease_agreement', 'valuation_report', 'inspection_report',
        'utility_bill', 'maintenance_record', 'other'
    ])]
    private string $documentType;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $documentDate = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $issuedBy = null; // Authority or organization that issued the document

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata = null; // Document properties, OCR text, etc.

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $uploadedAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isProcessed = false;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $aiAnalysis = null; // AI-extracted information

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $extractedText = null; // OCR extracted text

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

    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    public function setDocumentType(string $documentType): static
    {
        $this->documentType = $documentType;
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

    public function getDocumentDate(): ?\DateTimeInterface
    {
        return $this->documentDate;
    }

    public function setDocumentDate(?\DateTimeInterface $documentDate): static
    {
        $this->documentDate = $documentDate;
        return $this;
    }

    public function getIssuedBy(): ?string
    {
        return $this->issuedBy;
    }

    public function setIssuedBy(?string $issuedBy): static
    {
        $this->issuedBy = $issuedBy;
        return $this;
    }

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
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

    public function getExtractedText(): ?string
    {
        return $this->extractedText;
    }

    public function setExtractedText(?string $extractedText): static
    {
        $this->extractedText = $extractedText;
        return $this;
    }

    /**
     * Get the full file path for storage
     */
    public function getFilePath(): string
    {
        return 'uploads/submissions/' . $this->submission->getId() . '/documents/' . $this->filename;
    }

    /**
     * Get the web-accessible URL for download
     */
    public function getDownloadPath(): string
    {
        return '/download/submission-document/' . $this->id;
    }

    /**
     * Check if document is a valid type for industrial properties
     */
    public function isValidDocumentType(): bool
    {
        $validTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/tiff',
            'text/plain'
        ];
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

    /**
     * Get human-readable document type
     */
    public function getFormattedDocumentType(): string
    {
        $types = [
            'floor_plan' => 'Floor Plan',
            'technical_drawing' => 'Technical Drawing',
            'permit' => 'Building Permit',
            'certificate' => 'Certificate',
            'energy_audit' => 'Energy Audit',
            'environmental_report' => 'Environmental Report',
            'property_deed' => 'Property Deed',
            'lease_agreement' => 'Lease Agreement',
            'valuation_report' => 'Valuation Report',
            'inspection_report' => 'Inspection Report',
            'utility_bill' => 'Utility Bill',
            'maintenance_record' => 'Maintenance Record',
            'other' => 'Other Document'
        ];
        
        return $types[$this->documentType] ?? ucfirst(str_replace('_', ' ', $this->documentType));
    }

    /**
     * Check if document contains sensitive information
     */
    public function isSensitiveDocument(): bool
    {
        $sensitiveTypes = ['property_deed', 'lease_agreement', 'valuation_report', 'utility_bill'];
        return in_array($this->documentType, $sensitiveTypes);
    }
}
