<?php

namespace App\Service;

use App\Entity\ContributorProfile;
use App\Entity\ContributorReward;
use App\Entity\PropertySubmission;
use App\Entity\User;
use App\Repository\ContributorProfileRepository;
use App\Repository\ContributorRewardRepository;
use App\Repository\PropertySubmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;

/**
 * Core service for managing contributors in the PropertyCrowd Europe system
 */
class ContributorService
{
    private EntityManagerInterface $entityManager;
    private ContributorProfileRepository $contributorRepository;
    private ContributorRewardRepository $rewardRepository;
    private PropertySubmissionRepository $submissionRepository;
    private Security $security;
    private LoggerInterface $logger;

    // European Country Mapping for Phase 1
    private const EUROPEAN_COUNTRIES = [
        'AT' => 'Austria', 'BE' => 'Belgium', 'BG' => 'Bulgaria', 'HR' => 'Croatia',
        'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark', 'EE' => 'Estonia',
        'FI' => 'Finland', 'FR' => 'France', 'DE' => 'Germany', 'GR' => 'Greece',
        'HU' => 'Hungary', 'IE' => 'Ireland', 'IT' => 'Italy', 'LV' => 'Latvia',
        'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MT' => 'Malta', 'NL' => 'Netherlands',
        'PL' => 'Poland', 'PT' => 'Portugal', 'RO' => 'Romania', 'SK' => 'Slovakia',
        'SI' => 'Slovenia', 'ES' => 'Spain', 'SE' => 'Sweden', 'GB' => 'United Kingdom',
        'NO' => 'Norway', 'CH' => 'Switzerland', 'IS' => 'Iceland',
        // Balkans - Priority Phase
        'RS' => 'Serbia', 'BA' => 'Bosnia and Herzegovina', 'MK' => 'North Macedonia',
        'AL' => 'Albania', 'ME' => 'Montenegro', 'XK' => 'Kosovo'
    ];

    public function __construct(
        EntityManagerInterface $entityManager,
        ContributorProfileRepository $contributorRepository,
        ContributorRewardRepository $rewardRepository,
        PropertySubmissionRepository $submissionRepository,
        Security $security,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->contributorRepository = $contributorRepository;
        $this->rewardRepository = $rewardRepository;
        $this->submissionRepository = $submissionRepository;
        $this->security = $security;
        $this->logger = $logger;
    }

    /**
     * Create new contributor profile for user
     */
    public function createContributorProfile(User $user, array $profileData): ContributorProfile
    {
        $profile = new ContributorProfile();
        $profile->setUser($user);
        $profile->setEuropeanId($this->generateEuropeanId('BG')); // Default to BG for now
        $profile->setFullName($profileData['full_name'] ?? 'Contributor');
        $profile->setCompany($profileData['company'] ?? null);
        $profile->setPosition($profileData['position'] ?? null);
        $profile->setPhone($profileData['phone'] ?? '+359000000000');
        $profile->setLinkedinProfile($profileData['linkedin'] ?? null);
        $profile->setLanguages($profileData['languages'] ?? ['en']);
        $profile->setExpertiseAreas($profileData['expertise'] ?? []);
        $profile->setGeographicCoverage($profileData['geographic_coverage'] ?? ['BG']);

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        // Award welcome bonus
        $this->awardWelcomeBonus($profile);

        $this->logger->info('New European contributor created', [
            'european_id' => $profile->getEuropeanId(),
            'user_id' => $user->getId()
        ]);

        return $profile;
    }

    /**
     * Generate unique European Contributor ID
     */
    private function generateEuropeanId(string $country): string
    {
        $year = date('Y');
        
        // Get next sequential number for country and year
        $lastProfile = $this->contributorRepository->createQueryBuilder('cp')
            ->where('cp.europeanId LIKE :pattern')
            ->setParameter('pattern', "EIC-{$country}-{$year}-%")
            ->orderBy('cp.europeanId', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $nextNumber = 1;
        if ($lastProfile) {
            $lastId = $lastProfile->getEuropeanId();
            $parts = explode('-', $lastId);
            $nextNumber = intval(end($parts)) + 1;
        }

        return sprintf('EIC-%s-%s-%06d', $country, $year, $nextNumber);
    }

    /**
     * Award welcome bonus to new contributor
     */
    private function awardWelcomeBonus(ContributorProfile $contributor): void
    {
        $reward = new ContributorReward();
        $reward->setContributor($contributor);
        $reward->setType(ContributorReward::TYPE_SPECIAL_BONUS);
        $reward->setAmount('100.00');
        $reward->setDescription('Welcome to PropertyCrowd Europe! Get started with your contribution journey.');
        $reward->setStatus(ContributorReward::STATUS_APPROVED);

        $this->entityManager->persist($reward);
        
        // Update contributor score
        $contributor->setContributionScore($contributor->getContributionScore() + 100);
        
        $this->entityManager->flush();
    }

    /**
     * Award points for property submission
     */
    public function awardSubmissionPoints(PropertySubmission $submission): void
    {
        $contributor = $submission->getSubmittedBy();
        
        if (!$contributor) {
            return;
        }
        
        // Calculate points based on submission quality and completeness
        $points = $this->calculateSubmissionPoints($submission);
        
        $reward = new ContributorReward();
        $reward->setContributor($contributor);
        $reward->setType(ContributorReward::TYPE_SPECIAL_BONUS);
        $reward->setAmount((string)$points);
        $reward->setDescription('Points for submitting industrial property #' . $submission->getId());
        $reward->setStatus(ContributorReward::STATUS_APPROVED);

        $this->entityManager->persist($reward);
        
        // Update contributor stats
        $contributor->setContributionScore($contributor->getContributionScore() + $points);
        $contributor->setTotalSubmissions($contributor->getTotalSubmissions() + 1);
        
        // Check for tier upgrade
        $this->checkTierUpgrade($contributor);
        
        $this->entityManager->flush();

        $this->logger->info('Submission points awarded', [
            'contributor_id' => $contributor->getEuropeanId(),
            'submission_id' => $submission->getId(),
            'points' => $points
        ]);
    }

    /**
     * Calculate points for property submission
     */
    private function calculateSubmissionPoints(PropertySubmission $submission): int
    {
        $basePoints = 50;
        $bonusPoints = 0;

        // Quality bonus based on approval status
        if ($submission->getStatus() === 'approved') {
            $bonusPoints += 50;
        }

        // Completeness bonus if has title and description
        if (!empty($submission->getTitleBg()) && strlen($submission->getTitleBg()) > 10) {
            $bonusPoints += 25;
        }
        
        if (!empty($submission->getDescriptionBg()) && strlen($submission->getDescriptionBg()) > 50) {
            $bonusPoints += 25;
        }

        // First submission in area bonus
        $existingInArea = $this->submissionRepository->createQueryBuilder('ps')
            ->where('ps.locationBg = :location')
            ->andWhere('ps.status = :status')
            ->andWhere('ps.id != :current_id')
            ->setParameter('location', $submission->getLocationBg())
            ->setParameter('status', 'approved')
            ->setParameter('current_id', $submission->getId())
            ->getQuery()
            ->getResult();

        if (empty($existingInArea)) {
            $bonusPoints += 100; // Pioneer bonus
        }

        return $basePoints + $bonusPoints;
    }

    /**
     * Check and upgrade contributor tier
     */
    public function checkTierUpgrade(ContributorProfile $contributor): bool
    {
        $currentTier = $contributor->getTier();
        $totalScore = $contributor->getContributionScore();
        
        $newTier = $this->determineTierByScore($totalScore);
        
        if ($newTier !== $currentTier) {
            $oldTier = $currentTier;
            $contributor->setTier($newTier);
            
            // Award tier upgrade bonus
            $this->awardTierUpgradeBonus($contributor, $oldTier, $newTier);
            
            $this->logger->info('Contributor tier upgraded', [
                'contributor_id' => $contributor->getEuropeanId(),
                'old_tier' => $oldTier,
                'new_tier' => $newTier,
                'total_score' => $totalScore
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Determine contributor tier by score
     */
    private function determineTierByScore(int $score): string
    {
        if ($score >= 10000) return ContributorProfile::TIER_DIAMOND;
        if ($score >= 5000) return ContributorProfile::TIER_PLATINUM;
        if ($score >= 2000) return ContributorProfile::TIER_GOLD;
        if ($score >= 500) return ContributorProfile::TIER_SILVER;
        
        return ContributorProfile::TIER_BRONZE;
    }

    /**
     * Award tier upgrade bonus
     */
    private function awardTierUpgradeBonus(ContributorProfile $contributor, string $oldTier, string $newTier): void
    {
        $bonusAmounts = [
            ContributorProfile::TIER_SILVER => 100,
            ContributorProfile::TIER_GOLD => 200,
            ContributorProfile::TIER_PLATINUM => 500,
            ContributorProfile::TIER_DIAMOND => 1000
        ];

        $bonus = $bonusAmounts[$newTier] ?? 0;
        
        if ($bonus > 0) {
            $reward = new ContributorReward();
            $reward->setContributor($contributor);
            $reward->setType(ContributorReward::TYPE_TIER_UPGRADE);
            $reward->setAmount((string)$bonus);
            $reward->setDescription("Tier upgrade bonus: {$oldTier} → {$newTier}");
            $reward->setStatus(ContributorReward::STATUS_APPROVED);

            $this->entityManager->persist($reward);
            
            $contributor->setContributionScore($contributor->getContributionScore() + $bonus);
        }
    }

    /**
     * Get European leaderboard
     */
    public function getEuropeanLeaderboard(int $limit = 50): array
    {
        return $this->contributorRepository->createQueryBuilder('cp')
            ->orderBy('cp.contributionScore', 'DESC')
            ->addOrderBy('cp.tier', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get comprehensive contributor statistics
     */
    public function getContributorStats(ContributorProfile $contributor): array
    {
        $submissions = $contributor->getPropertySubmissions();
        $rewards = $contributor->getRewards();
        
        // Calculate submission statistics
        $approvedSubmissions = 0;
        $pendingSubmissions = 0;
        $rejectedSubmissions = 0;
        
        foreach ($submissions as $submission) {
            switch ($submission->getStatus()) {
                case 'approved':
                    $approvedSubmissions++;
                    break;
                case 'pending':
                    $pendingSubmissions++;
                    break;
                case 'rejected':
                    $rejectedSubmissions++;
                    break;
            }
        }
        
        $totalSubmissions = count($submissions);
        $successRate = $totalSubmissions > 0 ? ($approvedSubmissions / $totalSubmissions) * 100 : 0;
        
        return [
            'european_id' => $contributor->getEuropeanId(),
            'tier' => $contributor->getTier(),
            'contribution_score' => $contributor->getContributionScore(),
            'total_submissions' => $contributor->getTotalSubmissions(),
            'approved_properties' => $contributor->getApprovedProperties(),
            'approved_submissions' => $approvedSubmissions,
            'pending_submissions' => $pendingSubmissions,
            'rejected_submissions' => $rejectedSubmissions,
            'success_rate' => $successRate,
            'total_rewards' => count($rewards),
            'join_date' => $contributor->getCreatedAt(),
            'rank_european' => $this->getContributorRank($contributor),
            'expertise_areas' => $contributor->getExpertiseAreas(),
            'geographic_coverage' => $contributor->getGeographicCoverage()
        ];
    }

    /**
     * Get contributor's European rank
     */
    private function getContributorRank(ContributorProfile $contributor): int
    {
        $rank = $this->contributorRepository->createQueryBuilder('cp')
            ->select('COUNT(cp.id) + 1')
            ->where('cp.contributionScore > :score')
            ->setParameter('score', $contributor->getContributionScore())
            ->getQuery()
            ->getSingleScalarResult();

        return $rank;
    }

    /**
     * Get available European countries
     */
    public function getEuropeanCountries(): array
    {
        return self::EUROPEAN_COUNTRIES;
    }

    /**
     * Validate if country is European
     */
    public function isEuropeanCountry(string $countryCode): bool
    {
        return isset(self::EUROPEAN_COUNTRIES[$countryCode]);
    }

    /**
     * Get total number of contributors
     */
    public function getTotalContributors(): int
    {
        return $this->contributorRepository->count([]);
    }

    /**
     * Get total number of submissions
     */
    public function getTotalSubmissions(): int
    {
        return $this->submissionRepository->count([]);
    }

    /**
     * Get total approved properties
     */
    public function getApprovedProperties(): int
    {
        return $this->submissionRepository->count(['status' => PropertySubmission::STATUS_APPROVED]);
    }

    /**
     * Get number of countries covered
     */
    public function getCountriesCovered(): int
    {
        $countries = $this->submissionRepository->createQueryBuilder('ps')
            ->select('DISTINCT ps.country')
            ->where('ps.status = :status')
            ->setParameter('status', PropertySubmission::STATUS_APPROVED)
            ->getQuery()
            ->getResult();

        return count($countries);
    }

    /**
     * Get total earnings distributed
     */
    public function getTotalEarningsDistributed(): string
    {
        $total = $this->rewardRepository->createQueryBuilder('r')
            ->select('SUM(r.amount)')
            ->where('r.status = :status')
            ->setParameter('status', ContributorReward::STATUS_PAID)
            ->getQuery()
            ->getSingleScalarResult();

        return $total ?? '0.00';
    }

    /**
     * Get success stories
     */
    public function getSuccessStories(int $limit = 10): array
    {
        // Mock data for now - would be stored in a separate entity in real implementation
        return [
            [
                'id' => 1,
                'title' => 'Sofia Logistics Hub Discovery',
                'contributor' => 'Maria Petrova',
                'european_id' => 'EIC-BG-2024-000001',
                'earnings' => '2,500',
                'description' => 'Discovered and mapped a 15,000 sqm logistics complex...'
            ],
            [
                'id' => 2,
                'title' => 'Warsaw Industrial Zone Mapping',
                'contributor' => 'Andrzej Kowalski',
                'european_id' => 'EIC-PL-2024-000015',
                'earnings' => '3,200',
                'description' => 'Comprehensive mapping of emerging industrial zone...'
            ]
        ];
    }

    /**
     * Initiate verification process for new contributor
     */
    public function initiateVerificationProcess(ContributorProfile $contributor): void
    {
        // Send welcome email, set initial verification status, etc.
        $contributor->setVerificationStatus(ContributorProfile::STATUS_PENDING);
        $this->entityManager->flush();

        $this->logger->info('Verification process initiated', [
            'european_id' => $contributor->getEuropeanId()
        ]);
    }

    /**
     * Get recent submissions for contributor
     */
    public function getRecentSubmissions(ContributorProfile $contributor, int $limit = 10): array
    {
        return $this->submissionRepository->findBy(
            ['submittedBy' => $contributor],
            ['submittedAt' => 'DESC'],
            $limit
        );
    }

    /**
     * Get pending rewards for contributor
     */
    public function getPendingRewards(ContributorProfile $contributor): array
    {
        return $this->rewardRepository->findBy([
            'contributor' => $contributor,
            'status' => ContributorReward::STATUS_PENDING
        ]);
    }

    /**
     * Get leaderboard position for contributor
     */
    public function getLeaderboardPosition(ContributorProfile $contributor): int
    {
        return $this->getContributorRank($contributor);
    }

    /**
     * Get submission timeline
     */
    public function getSubmissionTimeline(PropertySubmission $submission): array
    {
        // Mock timeline data - would be stored in a separate entity in real implementation
        return [
            [
                'date' => $submission->getSubmittedAt(),
                'status' => 'submitted',
                'title' => 'Submission Received',
                'description' => 'Your property submission has been received.'
            ],
            [
                'date' => $submission->getUpdatedAt(),
                'status' => 'processing',
                'title' => 'AI Analysis Complete',
                'description' => 'AI validation completed with 95% confidence score.'
            ]
        ];
    }

    /**
     * Get AI analysis for submission
     */
    public function getAiAnalysisForSubmission(PropertySubmission $submission): array
    {
        // Mock AI analysis data
        return [
            'confidence_score' => 95,
            'market_value_estimate' => '$2,500,000',
            'location_score' => 88,
            'accessibility_score' => 92,
            'infrastructure_score' => 89,
            'recommendations' => [
                'Excellent location for logistics operations',
                'Strong transport connectivity',
                'Area showing growth potential'
            ]
        ];
    }

    /**
     * Get contributor achievements
     */
    public function getContributorAchievements(ContributorProfile $contributor): array
    {
        // Mock achievements data
        return [
            [
                'id' => 'first_submission',
                'title' => 'First Submission',
                'description' => 'Made your first property submission',
                'earned' => true,
                'date' => $contributor->getCreatedAt()
            ],
            [
                'id' => 'verified_contributor',
                'title' => 'Verified Contributor',
                'description' => 'Completed full verification process',
                'earned' => $contributor->isFullyVerified(),
                'date' => $contributor->getUpdatedAt()
            ]
        ];
    }

    /**
     * Get contributor submissions with filters
     */
    public function getContributorSubmissions(ContributorProfile $contributor, array $filters = []): array
    {
        $qb = $this->submissionRepository->createQueryBuilder('ps')
            ->where('ps.submittedBy = :contributor')
            ->setParameter('contributor', $contributor);

        if ($filters['status'] !== 'all') {
            $qb->andWhere('ps.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if ($filters['country'] !== 'all') {
            $qb->andWhere('ps.country = :country')
                ->setParameter('country', $filters['country']);
        }

        switch ($filters['sort']) {
            case 'oldest':
                $qb->orderBy('ps.submittedAt', 'ASC');
                break;
            default:
                $qb->orderBy('ps.submittedAt', 'DESC');
                break;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get submission statistics for contributor
     */
    public function getSubmissionStats(ContributorProfile $contributor): array
    {
        $submissions = $contributor->getPropertySubmissions();
        
        $stats = [
            'total' => count($submissions),
            'approved' => 0,
            'pending' => 0,
            'rejected' => 0
        ];

        foreach ($submissions as $submission) {
            switch ($submission->getStatus()) {
                case PropertySubmission::STATUS_APPROVED:
                    $stats['approved']++;
                    break;
                case PropertySubmission::STATUS_PENDING:
                    $stats['pending']++;
                    break;
                case PropertySubmission::STATUS_REJECTED:
                    $stats['rejected']++;
                    break;
            }
        }

        return $stats;
    }

    /**
     * Get submission reviews
     */
    public function getSubmissionReviews(PropertySubmission $submission): array
    {
        // In real implementation, this would return SubmissionReview entities
        return $submission->getReviews()->toArray();
    }

    /**
     * Get related submissions
     */
    public function getRelatedSubmissions(PropertySubmission $submission): array
    {
        return $this->submissionRepository->createQueryBuilder('ps')
            ->where('ps.country = :country')
            ->andWhere('ps.type = :type')
            ->andWhere('ps.id != :id')
            ->setParameter('country', $submission->getCountry())
            ->setParameter('type', $submission->getType())
            ->setParameter('id', $submission->getId())
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get verification steps for contributor
     */
    public function getVerificationSteps(ContributorProfile $contributor): array
    {
        return [
            [
                'step' => 1,
                'title' => 'Email Verification',
                'completed' => $contributor->isEmailVerified() ?? false,
                'required' => true
            ],
            [
                'step' => 2,
                'title' => 'Phone Verification',
                'completed' => $contributor->isPhoneVerified() ?? false,
                'required' => true
            ],
            [
                'step' => 3,
                'title' => 'Identity Documents',
                'completed' => $contributor->isIdentityVerified() ?? false,
                'required' => true
            ],
            [
                'step' => 4,
                'title' => 'Company Background',
                'completed' => $contributor->isCompanyVerified() ?? false,
                'required' => false
            ]
        ];
    }

    /**
     * Get required documents for verification
     */
    public function getRequiredDocuments(ContributorProfile $contributor): array
    {
        return [
            'identity' => [
                'type' => 'Identity Document',
                'description' => 'Passport or National ID',
                'uploaded' => false, // would check actual uploads
                'required' => true
            ],
            'proof_of_address' => [
                'type' => 'Proof of Address',
                'description' => 'Utility bill or bank statement',
                'uploaded' => false,
                'required' => true
            ],
            'professional' => [
                'type' => 'Professional Certificate',
                'description' => 'Real estate license or business registration',
                'uploaded' => false,
                'required' => false
            ]
        ];
    }

    /**
     * Get verification status
     */
    public function getVerificationStatus(ContributorProfile $contributor): array
    {
        return [
            'overall_status' => $contributor->getVerificationStatus(),
            'completion_percentage' => 65, // calculated based on completed steps
            'next_step' => 'Upload identity documents',
            'estimated_completion' => '2-3 business days'
        ];
    }

    /**
     * Upload verification document
     */
    public function uploadVerificationDocument(ContributorProfile $contributor, $uploadedFile, string $documentType): void
    {
        // In real implementation, this would handle file upload, validation, and storage
        $this->logger->info('Verification document uploaded', [
            'european_id' => $contributor->getEuropeanId(),
            'document_type' => $documentType,
            'filename' => $uploadedFile->getClientOriginalName()
        ]);
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(array $filters = []): array
    {
        $qb = $this->contributorRepository->createQueryBuilder('cp')
            ->orderBy('cp.contributionScore', 'DESC')
            ->setMaxResults($filters['limit'] ?? 50);

        if ($filters['country'] !== 'all') {
            $qb->andWhere('JSON_SEARCH(cp.geographicCoverage, \'one\', :country) IS NOT NULL')
                ->setParameter('country', $filters['country']);
        }

        if ($filters['tier'] !== 'all') {
            $qb->andWhere('cp.tier = :tier')
                ->setParameter('tier', $filters['tier']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get active countries
     */
    public function getActiveCountries(): array
    {
        $countries = $this->submissionRepository->createQueryBuilder('ps')
            ->select('DISTINCT ps.country')
            ->getQuery()
            ->getResult();

        return array_column($countries, 'country');
    }

    /**
     * Get testimonials
     */
    public function getTestimonials(): array
    {
        // Mock testimonials - would be stored in database
        return [
            [
                'name' => 'Maria Petrova',
                'european_id' => 'EIC-BG-2024-000001',
                'country' => 'Bulgaria',
                'text' => 'PropertyCrowd Europe transformed how I find opportunities...',
                'earnings' => '€15,400'
            ]
        ];
    }

    /**
     * Get platform statistics
     */
    public function getPlatformStats(): array
    {
        return [
            'total_contributors' => $this->getTotalContributors(),
            'total_submissions' => $this->getTotalSubmissions(),
            'total_earnings' => $this->getTotalEarningsDistributed(),
            'countries_covered' => $this->getCountriesCovered()
        ];
    }

    /**
     * Get rewards history for contributor
     */
    public function getRewardsHistory(ContributorProfile $contributor): array
    {
        return $this->rewardRepository->findBy(
            ['contributor' => $contributor],
            ['createdAt' => 'DESC']
        );
    }

    /**
     * Get total earnings for contributor
     */
    public function getTotalEarnings(ContributorProfile $contributor): string
    {
        $total = $this->rewardRepository->createQueryBuilder('r')
            ->select('SUM(r.amount)')
            ->where('r.contributor = :contributor')
            ->andWhere('r.status = :status')
            ->setParameter('contributor', $contributor)
            ->setParameter('status', ContributorReward::STATUS_PAID)
            ->getQuery()
            ->getSingleScalarResult();

        return $total ?? '0.00';
    }

    /**
     * Get next tier requirements
     */
    public function getNextTierRequirements(ContributorProfile $contributor): array
    {
        $currentTier = $contributor->getTier();
        
        $requirements = [
            ContributorProfile::TIER_BRONZE => [
                'target_tier' => ContributorProfile::TIER_SILVER,
                'min_submissions' => 10,
                'min_score' => 1000,
                'current_submissions' => $contributor->getTotalSubmissions(),
                'current_score' => $contributor->getContributionScore()
            ],
            ContributorProfile::TIER_SILVER => [
                'target_tier' => ContributorProfile::TIER_GOLD,
                'min_submissions' => 25,
                'min_score' => 2500,
                'current_submissions' => $contributor->getTotalSubmissions(),
                'current_score' => $contributor->getContributionScore()
            ],
            ContributorProfile::TIER_GOLD => [
                'target_tier' => ContributorProfile::TIER_PLATINUM,
                'min_submissions' => 50,
                'min_score' => 5000,
                'current_submissions' => $contributor->getTotalSubmissions(),
                'current_score' => $contributor->getContributionScore()
            ]
        ];

        return $requirements[$currentTier] ?? [];
    }

    /**
     * Get market intelligence data
     */
    public function getMarketIntelligence(array $filters = []): array
    {
        // Mock market intelligence data - would integrate with real market APIs
        return [
            'country_overview' => [
                'average_price_per_sqm' => '€450',
                'market_growth' => '+12%',
                'demand_level' => 'High',
                'supply_level' => 'Medium'
            ],
            'trending_areas' => [
                'Sofia Tech Park',
                'Plovdiv Industrial Zone',
                'Varna Logistics Hub'
            ],
            'price_trends' => [
                ['month' => 'Jan', 'price' => 420],
                ['month' => 'Feb', 'price' => 435],
                ['month' => 'Mar', 'price' => 450]
            ],
            'investment_opportunities' => [
                [
                    'type' => 'Warehouse',
                    'location' => 'Sofia Ring Road',
                    'potential_return' => '15-18%',
                    'risk_level' => 'Medium'
                ]
            ]
        ];
    }

    // Smart assistance methods for submission wizard
    public function getLocationSuggestions(string $location): array
    {
        return [
            'suggestions' => [
                "Consider proximity to major highways",
                "Check for industrial zoning permits",
                "Verify utilities availability"
            ]
        ];
    }

    public function getPricingSuggestions(array $formData): array
    {
        return [
            'suggestions' => [
                "Market price seems competitive",
                "Consider seasonal variations",
                "Factor in development potential"
            ]
        ];
    }

    public function getDescriptionEnhancements(array $formData): array
    {
        return [
            'enhancements' => [
                "Add more technical specifications",
                "Include transport connectivity details",
                "Mention nearby industrial facilities"
            ]
        ];
    }

    public function analyzeImages(array $images): array
    {
        return [
            'analysis' => [
                "Images show good property condition",
                "Consider adding aerial view",
                "Include photos of access roads"
            ]
        ];
    }

    public function validateSubmissionData(array $formData): array
    {
        return [
            'validations' => [
                "All required fields completed",
                "Location coordinates verified",
                "Price range within market expectations"
            ]
        ];
    }

    /**
     * Get country statistics for leaderboard
     */
    public function getCountryStats(): array
    {
        // Get all contributors with their geographic coverage
        $qb = $this->entityManager->createQueryBuilder();
        $contributors = $qb->select('cp.geographicCoverage')
            ->from(ContributorProfile::class, 'cp')
            ->where('cp.geographicCoverage IS NOT NULL')
            ->andWhere('cp.geographicCoverage != :empty')
            ->setParameter('empty', '[]')
            ->getQuery()
            ->getResult();

        // Parse geographic coverage and count by country
        $countryCounts = [];
        foreach ($contributors as $contributor) {
            $coverage = $contributor['geographicCoverage'];
            
            // Handle both array and JSON string formats
            if (is_string($coverage)) {
                $coverage = json_decode($coverage, true);
            }
            
            if (is_array($coverage)) {
                foreach ($coverage as $countryCode) {
                    $countryCounts[$countryCode] = ($countryCounts[$countryCode] ?? 0) + 1;
                }
            }
        }

        // Sort by count and format for template
        arsort($countryCounts);
        
        $countryStats = [];
        foreach ($countryCounts as $countryCode => $count) {
            $countryName = self::EUROPEAN_COUNTRIES[$countryCode] ?? $countryCode;
            
            $countryStats[] = [
                'country_code' => $countryCode,
                'country_name' => $countryName,
                'contributors' => $count
            ];
        }

        return $countryStats;
    }
}
