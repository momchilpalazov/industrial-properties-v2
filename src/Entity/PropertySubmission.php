<?php

namespace App\Entity;

use App\Repository\PropertySubmissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PropertySubmissionRepository::class)]
#[ORM\Table(name: 'property_submissions')]
#[ORM\HasLifecycleCallbacks]
class PropertySubmission
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_AI_REVIEW = 'ai_review';
    public const STATUS_AI_APPROVED = 'ai_approved';
    public const STATUS_COMMUNITY_REVIEW = 'community_review';
    public const STATUS_ADMIN_REVIEW = 'admin_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_NEEDS_REVISION = 'needs_revision';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ContributorProfile::class, inversedBy: 'propertySubmissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ContributorProfile $submittedBy = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $submissionId = null; // PSB-2025-001247

    #[ORM\Column(length: 30)]
    #[Assert\Choice(choices: [
        self::STATUS_PENDING, 
        self::STATUS_AI_REVIEW,
        self::STATUS_AI_APPROVED, 
        self::STATUS_COMMUNITY_REVIEW,
        self::STATUS_ADMIN_REVIEW, 
        self::STATUS_APPROVED, 
        self::STATUS_REJECTED,
        self::STATUS_NEEDS_REVISION
    ])]
    private string $status = self::STATUS_PENDING;

    // Property data fields (same as Property entity)
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете заглавие на български')]
    private ?string $titleBg = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titleEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titleDe = null;

    #[ORM\Column(length: 255, nullable: true)]
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
    private ?string $locationBg = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $locationEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $locationDe = null;

    #[ORM\Column(length: 255, nullable: true)]
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

    #[ORM\ManyToOne(targetEntity: PropertyType::class)]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id', nullable: true)]
    private ?PropertyType $type = null;

    #[ORM\ManyToOne(targetEntity: PropertyCategory::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true)]
    private ?PropertyCategory $category = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 8, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank]
    #[Assert\Country]
    private ?string $country = null; // ISO 3166-1 alpha-2

    // AI Analysis fields
    #[ORM\Column(nullable: true)]
    private ?int $aiConfidenceScore = null; // 0-100

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $aiSuggestions = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $aiEnhancements = []; // AI generated content

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $aiSuggestedPrice = null;

    #[ORM\Column]
    private bool $duplicateDetected = false;

    #[ORM\Column(nullable: true)]
    private ?int $contentQualityScore = null; // 0-100

    // Community review fields
    #[ORM\Column]
    private int $communityUpvotes = 0;

    #[ORM\Column]
    private int $communityDownvotes = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, nullable: true)]
    private ?string $communityRating = null; // 1.00-5.00

    #[ORM\OneToMany(mappedBy: 'submission', targetEntity: SubmissionReview::class, cascade: ['remove'])]
    private Collection $reviews;

    // Admin review fields
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'reviewed_by', referencedColumnName: 'id', nullable: true)]
    private ?User $reviewedBy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adminNotes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $requiredRevisions = [];

    // Converted property reference
    #[ORM\OneToOne(targetEntity: Property::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'converted_property_id', referencedColumnName: 'id', nullable: true)]
    private ?Property $convertedProperty = null;

    // Images and files
    #[ORM\OneToMany(mappedBy: 'submission', targetEntity: SubmissionImage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'submission', targetEntity: SubmissionDocument::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $documents;

    // Timestamps
    #[ORM\Column]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $aiProcessedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $communityReviewStartedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $adminReviewStartedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $rejectedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->submittedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getSubmittedBy(): ?ContributorProfile
    {
        return $this->submittedBy;
    }

    public function setSubmittedBy(?ContributorProfile $submittedBy): static
    {
        $this->submittedBy = $submittedBy;
        return $this;
    }

    public function getSubmissionId(): ?string
    {
        return $this->submissionId;
    }

    public function setSubmissionId(string $submissionId): static
    {
        $this->submissionId = $submissionId;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        
        // Auto-set timestamps based on status
        match($status) {
            self::STATUS_AI_REVIEW => $this->aiProcessedAt = new \DateTimeImmutable(),
            self::STATUS_COMMUNITY_REVIEW => $this->communityReviewStartedAt = new \DateTimeImmutable(),
            self::STATUS_ADMIN_REVIEW => $this->adminReviewStartedAt = new \DateTimeImmutable(),
            self::STATUS_APPROVED => $this->approvedAt = new \DateTimeImmutable(),
            self::STATUS_REJECTED => $this->rejectedAt = new \DateTimeImmutable(),
            default => null
        };
        
        return $this;
    }

    public function getTitleBg(): ?string
    {
        return $this->titleBg;
    }

    public function setTitleBg(string $titleBg): static
    {
        $this->titleBg = $titleBg;
        return $this;
    }

    public function getTitleEn(): ?string
    {
        return $this->titleEn;
    }

    public function setTitleEn(?string $titleEn): static
    {
        $this->titleEn = $titleEn;
        return $this;
    }

    public function getTitleDe(): ?string
    {
        return $this->titleDe;
    }

    public function setTitleDe(?string $titleDe): static
    {
        $this->titleDe = $titleDe;
        return $this;
    }

    public function getTitleRu(): ?string
    {
        return $this->titleRu;
    }

    public function setTitleRu(?string $titleRu): static
    {
        $this->titleRu = $titleRu;
        return $this;
    }

    public function getDescriptionBg(): ?string
    {
        return $this->descriptionBg;
    }

    public function setDescriptionBg(?string $descriptionBg): static
    {
        $this->descriptionBg = $descriptionBg;
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

    public function getLocationBg(): ?string
    {
        return $this->locationBg;
    }

    public function setLocationBg(string $locationBg): static
    {
        $this->locationBg = $locationBg;
        return $this;
    }

    public function getLocationEn(): ?string
    {
        return $this->locationEn;
    }

    public function setLocationEn(?string $locationEn): static
    {
        $this->locationEn = $locationEn;
        return $this;
    }

    public function getLocationDe(): ?string
    {
        return $this->locationDe;
    }

    public function setLocationDe(?string $locationDe): static
    {
        $this->locationDe = $locationDe;
        return $this;
    }

    public function getLocationRu(): ?string
    {
        return $this->locationRu;
    }

    public function setLocationRu(?string $locationRu): static
    {
        $this->locationRu = $locationRu;
        return $this;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(?string $area): static
    {
        $this->area = $area;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getPricePerSqm(): ?string
    {
        return $this->pricePerSqm;
    }

    public function setPricePerSqm(?string $pricePerSqm): static
    {
        $this->pricePerSqm = $pricePerSqm;
        return $this;
    }

    public function getYearBuilt(): ?int
    {
        return $this->yearBuilt;
    }

    public function setYearBuilt(?int $yearBuilt): static
    {
        $this->yearBuilt = $yearBuilt;
        return $this;
    }

    public function getAvailableFrom(): ?\DateTimeImmutable
    {
        return $this->availableFrom;
    }

    public function setAvailableFrom(?\DateTimeImmutable $availableFrom): static
    {
        $this->availableFrom = $availableFrom;
        return $this;
    }

    public function getType(): ?PropertyType
    {
        return $this->type;
    }

    public function setType(?PropertyType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getCategory(): ?PropertyCategory
    {
        return $this->category;
    }

    public function setCategory(?PropertyCategory $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;
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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getAiConfidenceScore(): ?int
    {
        return $this->aiConfidenceScore;
    }

    public function setAiConfidenceScore(?int $aiConfidenceScore): static
    {
        $this->aiConfidenceScore = $aiConfidenceScore;
        return $this;
    }

    public function getAiSuggestions(): ?array
    {
        return $this->aiSuggestions;
    }

    public function setAiSuggestions(?array $aiSuggestions): static
    {
        $this->aiSuggestions = $aiSuggestions;
        return $this;
    }

    public function getAiEnhancements(): ?array
    {
        return $this->aiEnhancements;
    }

    public function setAiEnhancements(?array $aiEnhancements): static
    {
        $this->aiEnhancements = $aiEnhancements;
        return $this;
    }

    public function getAiSuggestedPrice(): ?string
    {
        return $this->aiSuggestedPrice;
    }

    public function setAiSuggestedPrice(?string $aiSuggestedPrice): static
    {
        $this->aiSuggestedPrice = $aiSuggestedPrice;
        return $this;
    }

    public function isDuplicateDetected(): bool
    {
        return $this->duplicateDetected;
    }

    public function setDuplicateDetected(bool $duplicateDetected): static
    {
        $this->duplicateDetected = $duplicateDetected;
        return $this;
    }

    public function getContentQualityScore(): ?int
    {
        return $this->contentQualityScore;
    }

    public function setContentQualityScore(?int $contentQualityScore): static
    {
        $this->contentQualityScore = $contentQualityScore;
        return $this;
    }

    public function getCommunityUpvotes(): int
    {
        return $this->communityUpvotes;
    }

    public function setCommunityUpvotes(int $communityUpvotes): static
    {
        $this->communityUpvotes = $communityUpvotes;
        $this->updateCommunityRating();
        return $this;
    }

    public function getCommunityDownvotes(): int
    {
        return $this->communityDownvotes;
    }

    public function setCommunityDownvotes(int $communityDownvotes): static
    {
        $this->communityDownvotes = $communityDownvotes;
        $this->updateCommunityRating();
        return $this;
    }

    public function getCommunityRating(): ?string
    {
        return $this->communityRating;
    }

    private function updateCommunityRating(): void
    {
        $total = $this->communityUpvotes + $this->communityDownvotes;
        if ($total > 0) {
            $rating = (($this->communityUpvotes * 5) + ($this->communityDownvotes * 1)) / $total;
            $this->communityRating = number_format($rating, 2);
        }
    }

    /**
     * @return Collection<int, SubmissionReview>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function getReviewedBy(): ?User
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?User $reviewedBy): static
    {
        $this->reviewedBy = $reviewedBy;
        return $this;
    }

    public function getAdminNotes(): ?string
    {
        return $this->adminNotes;
    }

    public function setAdminNotes(?string $adminNotes): static
    {
        $this->adminNotes = $adminNotes;
        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;
        return $this;
    }

    public function getRequiredRevisions(): ?array
    {
        return $this->requiredRevisions;
    }

    public function setRequiredRevisions(?array $requiredRevisions): static
    {
        $this->requiredRevisions = $requiredRevisions;
        return $this;
    }

    public function getConvertedProperty(): ?Property
    {
        return $this->convertedProperty;
    }

    public function setConvertedProperty(?Property $convertedProperty): static
    {
        $this->convertedProperty = $convertedProperty;
        return $this;
    }

    /**
     * @return Collection<int, SubmissionImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @return Collection<int, SubmissionDocument>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function getAiProcessedAt(): ?\DateTimeImmutable
    {
        return $this->aiProcessedAt;
    }

    public function getCommunityReviewStartedAt(): ?\DateTimeImmutable
    {
        return $this->communityReviewStartedAt;
    }

    public function getAdminReviewStartedAt(): ?\DateTimeImmutable
    {
        return $this->adminReviewStartedAt;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function getRejectedAt(): ?\DateTimeImmutable
    {
        return $this->rejectedAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_AI_REVIEW => 'AI Processing',
            self::STATUS_AI_APPROVED => 'AI Approved',
            self::STATUS_COMMUNITY_REVIEW => 'Community Review',
            self::STATUS_ADMIN_REVIEW => 'Admin Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_NEEDS_REVISION => 'Needs Revision',
            default => 'Unknown'
        };
    }

    public function getProcessingTimeInDays(): float
    {
        $endTime = $this->approvedAt ?? $this->rejectedAt ?? new \DateTimeImmutable();
        $diff = $endTime->diff($this->submittedAt);
        return $diff->days + ($diff->h / 24) + ($diff->i / 1440);
    }

    public function canBeApproved(): bool
    {
        return in_array($this->status, [
            self::STATUS_AI_APPROVED,
            self::STATUS_COMMUNITY_REVIEW,
            self::STATUS_ADMIN_REVIEW
        ]);
    }

    public function needsAiProcessing(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isReadyForCommunityReview(): bool
    {
        return $this->status === self::STATUS_AI_APPROVED && 
               $this->aiConfidenceScore >= 75;
    }
}
