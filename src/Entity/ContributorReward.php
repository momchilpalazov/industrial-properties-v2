<?php

namespace App\Entity;

use App\Repository\ContributorRewardRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContributorRewardRepository::class)]
#[ORM\Table(name: 'contributor_rewards')]
#[ORM\HasLifecycleCallbacks]
class ContributorReward
{
    public const TYPE_REVENUE_SHARE = 'revenue_share';
    public const TYPE_REFERRAL_BONUS = 'referral_bonus';
    public const TYPE_PREMIUM_LISTING = 'premium_listing';
    public const TYPE_BADGE_ACHIEVEMENT = 'badge_achievement';
    public const TYPE_VIP_PROMOTION = 'vip_promotion';
    public const TYPE_TIER_UPGRADE = 'tier_upgrade';
    public const TYPE_SPECIAL_BONUS = 'special_bonus';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ContributorProfile::class, inversedBy: 'rewards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ContributorProfile $contributor = null;

    #[ORM\Column(length: 30)]
    #[Assert\Choice(choices: [
        self::TYPE_REVENUE_SHARE,
        self::TYPE_REFERRAL_BONUS,
        self::TYPE_PREMIUM_LISTING,
        self::TYPE_BADGE_ACHIEVEMENT,
        self::TYPE_VIP_PROMOTION,
        self::TYPE_TIER_UPGRADE,
        self::TYPE_SPECIAL_BONUS
    ])]
    private ?string $type = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_PAID,
        self::STATUS_CANCELLED,
        self::STATUS_REJECTED
    ])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 3)]
    #[Assert\Currency]
    private string $currency = 'EUR';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: Property::class)]
    #[ORM\JoinColumn(name: 'related_property_id', referencedColumnName: 'id', nullable: true)]
    private ?Property $relatedProperty = null;

    #[ORM\ManyToOne(targetEntity: PropertySubmission::class)]
    #[ORM\JoinColumn(name: 'related_submission_id', referencedColumnName: 'id', nullable: true)]
    private ?PropertySubmission $relatedSubmission = null;

    #[ORM\ManyToOne(targetEntity: ContributorProfile::class)]
    #[ORM\JoinColumn(name: 'referred_contributor_id', referencedColumnName: 'id', nullable: true)]
    private ?ContributorProfile $referredContributor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transactionId = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = [];

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'approved_by', referencedColumnName: 'id', nullable: true)]
    private ?User $approvedBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $awardedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getContributor(): ?ContributorProfile
    {
        return $this->contributor;
    }

    public function setContributor(?ContributorProfile $contributor): static
    {
        $this->contributor = $contributor;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        
        match($status) {
            self::STATUS_APPROVED => $this->approvedAt = new \DateTimeImmutable(),
            self::STATUS_PAID => $this->paidAt = new \DateTimeImmutable(),
            default => null
        };
        
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getRelatedProperty(): ?Property
    {
        return $this->relatedProperty;
    }

    public function setRelatedProperty(?Property $relatedProperty): static
    {
        $this->relatedProperty = $relatedProperty;
        return $this;
    }

    public function getRelatedSubmission(): ?PropertySubmission
    {
        return $this->relatedSubmission;
    }

    public function setRelatedSubmission(?PropertySubmission $relatedSubmission): static
    {
        $this->relatedSubmission = $relatedSubmission;
        return $this;
    }

    public function getReferredContributor(): ?ContributorProfile
    {
        return $this->referredContributor;
    }

    public function setReferredContributor(?ContributorProfile $referredContributor): static
    {
        $this->referredContributor = $referredContributor;
        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): static
    {
        $this->transactionId = $transactionId;
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

    public function getApprovedBy(): ?User
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?User $approvedBy): static
    {
        $this->approvedBy = $approvedBy;
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

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getAwardedAt(): ?\DateTimeImmutable
    {
        return $this->awardedAt;
    }

    public function setAwardedAt(?\DateTimeImmutable $awardedAt): static
    {
        $this->awardedAt = $awardedAt;
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

    public function getTypeDisplayName(): string
    {
        return match($this->type) {
            self::TYPE_REVENUE_SHARE => 'Revenue Share',
            self::TYPE_REFERRAL_BONUS => 'Referral Bonus',
            self::TYPE_PREMIUM_LISTING => 'Premium Listing',
            self::TYPE_BADGE_ACHIEVEMENT => 'Badge Achievement',
            self::TYPE_VIP_PROMOTION => 'VIP Promotion',
            self::TYPE_TIER_UPGRADE => 'Tier Upgrade',
            self::TYPE_SPECIAL_BONUS => 'Special Bonus',
            default => 'Reward'
        };
    }

    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PAID => 'Paid',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Unknown'
        };
    }

    public function getFormattedAmount(): string
    {
        return number_format((float)$this->amount, 2) . ' ' . $this->currency;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt && $this->expiresAt < new \DateTimeImmutable();
    }

    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_APPROVED && !$this->isExpired();
    }
}
