<?php

namespace App\Entity;

use App\Repository\ContributorProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContributorProfileRepository::class)]
#[ORM\Table(name: 'contributor_profiles')]
#[ORM\HasLifecycleCallbacks]
class ContributorProfile
{
    public const TIER_BRONZE = 'bronze';
    public const TIER_SILVER = 'silver';
    public const TIER_GOLD = 'gold';
    public const TIER_PLATINUM = 'platinum';
    public const TIER_DIAMOND = 'diamond';

    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'contributorProfile', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $europeanId = null; // EIC-BG-2025-001247

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $position = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Regex('/^\+[1-9]\d{1,14}$/')]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedinProfile = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $professionalBackground = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $experience = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motivation = null;

    #[ORM\Column]
    private bool $agreeToTerms = false;

    #[ORM\Column]
    private bool $subscribeNewsletter = false;

    #[ORM\Column(type: Types::JSON)]
    private array $languages = [];

    #[ORM\Column(type: Types::JSON)]
    private array $expertiseAreas = [];

    #[ORM\Column(type: Types::JSON)]
    private array $geographicCoverage = [];

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_VERIFIED, self::STATUS_SUSPENDED, self::STATUS_REJECTED])]
    private string $verificationStatus = self::STATUS_PENDING;

    #[ORM\Column]
    private bool $identityVerified = false;

    #[ORM\Column]
    private bool $companyVerified = false;

    #[ORM\Column]
    private bool $phoneVerified = false;

    #[ORM\Column]
    private bool $emailVerified = false;

    #[ORM\Column]
    private int $professionalReferences = 0;

    #[ORM\Column]
    private int $totalSubmissions = 0;

    #[ORM\Column]
    private int $approvedProperties = 0;

    #[ORM\Column]
    private int $rejectedProperties = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $accuracyRate = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 1)]
    private string $avgProcessingTime = '0.0'; // days

    #[ORM\Column]
    private int $contributionScore = 0;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TIER_BRONZE, self::TIER_SILVER, self::TIER_GOLD, self::TIER_PLATINUM, self::TIER_DIAMOND])]
    private string $tier = self::TIER_BRONZE;

    #[ORM\Column(type: Types::JSON)]
    private array $badges = [];

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $totalEarnings = '0.00';

    #[ORM\Column]
    private int $freeVipPromotions = 0;

    #[ORM\OneToMany(mappedBy: 'submittedBy', targetEntity: PropertySubmission::class)]
    private Collection $propertySubmissions;

    #[ORM\OneToMany(mappedBy: 'contributor', targetEntity: ContributorReward::class)]
    private Collection $rewards;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $verifiedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastActivityAt = null;

    public function __construct()
    {
        $this->propertySubmissions = new ArrayCollection();
        $this->rewards = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getEuropeanId(): ?string
    {
        return $this->europeanId;
    }

    public function setEuropeanId(string $europeanId): static
    {
        $this->europeanId = $europeanId;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getLinkedinProfile(): ?string
    {
        return $this->linkedinProfile;
    }

    public function setLinkedinProfile(?string $linkedinProfile): static
    {
        $this->linkedinProfile = $linkedinProfile;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getProfessionalBackground(): ?string
    {
        return $this->professionalBackground;
    }

    public function setProfessionalBackground(?string $professionalBackground): static
    {
        $this->professionalBackground = $professionalBackground;
        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): static
    {
        $this->experience = $experience;
        return $this;
    }

    public function getMotivation(): ?string
    {
        return $this->motivation;
    }

    public function setMotivation(?string $motivation): static
    {
        $this->motivation = $motivation;
        return $this;
    }

    public function isAgreeToTerms(): bool
    {
        return $this->agreeToTerms;
    }

    public function setAgreeToTerms(bool $agreeToTerms): static
    {
        $this->agreeToTerms = $agreeToTerms;
        return $this;
    }

    public function isSubscribeNewsletter(): bool
    {
        return $this->subscribeNewsletter;
    }

    public function setSubscribeNewsletter(bool $subscribeNewsletter): static
    {
        $this->subscribeNewsletter = $subscribeNewsletter;
        return $this;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function setLanguages(array $languages): static
    {
        $this->languages = $languages;
        return $this;
    }

    public function getExpertiseAreas(): array
    {
        return $this->expertiseAreas;
    }

    public function setExpertiseAreas(array $expertiseAreas): static
    {
        $this->expertiseAreas = $expertiseAreas;
        return $this;
    }

    public function getGeographicCoverage(): array
    {
        return $this->geographicCoverage;
    }

    public function setGeographicCoverage(array $geographicCoverage): static
    {
        $this->geographicCoverage = $geographicCoverage;
        return $this;
    }

    public function getVerificationStatus(): string
    {
        return $this->verificationStatus;
    }

    public function setVerificationStatus(string $verificationStatus): static
    {
        $this->verificationStatus = $verificationStatus;
        return $this;
    }

    public function isIdentityVerified(): bool
    {
        return $this->identityVerified;
    }

    public function setIdentityVerified(bool $identityVerified): static
    {
        $this->identityVerified = $identityVerified;
        return $this;
    }

    public function isCompanyVerified(): bool
    {
        return $this->companyVerified;
    }

    public function setCompanyVerified(bool $companyVerified): static
    {
        $this->companyVerified = $companyVerified;
        return $this;
    }

    public function isPhoneVerified(): bool
    {
        return $this->phoneVerified;
    }

    public function setPhoneVerified(bool $phoneVerified): static
    {
        $this->phoneVerified = $phoneVerified;
        return $this;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool $emailVerified): static
    {
        $this->emailVerified = $emailVerified;
        return $this;
    }

    public function getProfessionalReferences(): int
    {
        return $this->professionalReferences;
    }

    public function setProfessionalReferences(int $professionalReferences): static
    {
        $this->professionalReferences = $professionalReferences;
        return $this;
    }

    public function getTotalSubmissions(): int
    {
        return $this->totalSubmissions;
    }

    public function setTotalSubmissions(int $totalSubmissions): static
    {
        $this->totalSubmissions = $totalSubmissions;
        return $this;
    }

    public function getApprovedProperties(): int
    {
        return $this->approvedProperties;
    }

    public function setApprovedProperties(int $approvedProperties): static
    {
        $this->approvedProperties = $approvedProperties;
        $this->updateTier();
        $this->updateAccuracyRate();
        return $this;
    }

    public function getRejectedProperties(): int
    {
        return $this->rejectedProperties;
    }

    public function setRejectedProperties(int $rejectedProperties): static
    {
        $this->rejectedProperties = $rejectedProperties;
        $this->updateAccuracyRate();
        return $this;
    }

    public function getAccuracyRate(): string
    {
        return $this->accuracyRate;
    }

    private function updateAccuracyRate(): void
    {
        $total = $this->approvedProperties + $this->rejectedProperties;
        if ($total > 0) {
            $this->accuracyRate = number_format(($this->approvedProperties / $total) * 100, 2);
        }
    }

    public function getAvgProcessingTime(): string
    {
        return $this->avgProcessingTime;
    }

    public function setAvgProcessingTime(string $avgProcessingTime): static
    {
        $this->avgProcessingTime = $avgProcessingTime;
        return $this;
    }

    public function getContributionScore(): int
    {
        return $this->contributionScore;
    }

    public function setContributionScore(int $contributionScore): static
    {
        $this->contributionScore = $contributionScore;
        return $this;
    }

    public function getTier(): string
    {
        return $this->tier;
    }

    public function setTier(string $tier): static
    {
        $this->tier = $tier;
        return $this;
    }

    private function updateTier(): void
    {
        if ($this->approvedProperties >= 51) {
            $this->tier = self::TIER_PLATINUM;
        } elseif ($this->approvedProperties >= 26) {
            $this->tier = self::TIER_GOLD;
        } elseif ($this->approvedProperties >= 11) {
            $this->tier = self::TIER_SILVER;
        } else {
            $this->tier = self::TIER_BRONZE;
        }
    }

    public function getBadges(): array
    {
        return $this->badges;
    }

    public function setBadges(array $badges): static
    {
        $this->badges = $badges;
        return $this;
    }

    public function addBadge(string $badge): static
    {
        if (!in_array($badge, $this->badges)) {
            $this->badges[] = $badge;
        }
        return $this;
    }

    public function getTotalEarnings(): string
    {
        return $this->totalEarnings;
    }

    public function setTotalEarnings(string $totalEarnings): static
    {
        $this->totalEarnings = $totalEarnings;
        return $this;
    }

    public function getFreeVipPromotions(): int
    {
        return $this->freeVipPromotions;
    }

    public function setFreeVipPromotions(int $freeVipPromotions): static
    {
        $this->freeVipPromotions = $freeVipPromotions;
        return $this;
    }

    /**
     * @return Collection<int, PropertySubmission>
     */
    public function getPropertySubmissions(): Collection
    {
        return $this->propertySubmissions;
    }

    public function addPropertySubmission(PropertySubmission $propertySubmission): static
    {
        if (!$this->propertySubmissions->contains($propertySubmission)) {
            $this->propertySubmissions->add($propertySubmission);
            $propertySubmission->setSubmittedBy($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, ContributorReward>
     */
    public function getRewards(): Collection
    {
        return $this->rewards;
    }

    public function addReward(ContributorReward $reward): static
    {
        if (!$this->rewards->contains($reward)) {
            $this->rewards->add($reward);
            $reward->setContributor($this);
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

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?\DateTimeImmutable $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;
        return $this;
    }

    public function getLastActivityAt(): ?\DateTimeImmutable
    {
        return $this->lastActivityAt;
    }

    public function setLastActivityAt(?\DateTimeImmutable $lastActivityAt): static
    {
        $this->lastActivityAt = $lastActivityAt;
        return $this;
    }

    public function isFullyVerified(): bool
    {
        return $this->identityVerified && 
               $this->companyVerified && 
               $this->phoneVerified && 
               $this->emailVerified &&
               $this->verificationStatus === self::STATUS_VERIFIED;
    }

    public function getTierDisplayName(): string
    {
        return match($this->tier) {
            self::TIER_BRONZE => 'Bronze Contributor',
            self::TIER_SILVER => 'Silver Expert',
            self::TIER_GOLD => 'Gold Specialist',
            self::TIER_PLATINUM => 'Platinum Authority',
            self::TIER_DIAMOND => 'Diamond Pioneer',
            default => 'Contributor'
        };
    }

    public function getVipPromotionsForTier(): int
    {
        return match($this->tier) {
            self::TIER_BRONZE => 1,
            self::TIER_SILVER => 3,
            self::TIER_GOLD => 6,
            self::TIER_PLATINUM => 12,
            self::TIER_DIAMOND => 24,
            default => 0
        };
    }

    /**
     * Get all available tiers as array
     */
    public static function getTiersList(): array
    {
        return [
            self::TIER_BRONZE => 'Bronze Contributor',
            self::TIER_SILVER => 'Silver Expert',
            self::TIER_GOLD => 'Gold Specialist',
            self::TIER_PLATINUM => 'Platinum Authority',
            self::TIER_DIAMOND => 'Diamond Pioneer'
        ];
    }

    /**
     * Get email from the associated User entity
     */
    public function getEmail(): ?string
    {
        return $this->user?->getEmail();
    }

    /**
     * Set email on the associated User entity
     */
    public function setEmail(string $email): static
    {
        if ($this->user) {
            $this->user->setEmail($email);
        }
        return $this;
    }
}
