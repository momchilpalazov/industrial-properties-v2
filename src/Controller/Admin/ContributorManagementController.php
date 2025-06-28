<?php

namespace App\Controller\Admin;

use App\Entity\ContributorProfile;
use App\Entity\ContributorReward;
use App\Entity\PropertySubmission;
use App\Entity\User;
use App\Service\ContributorService;
use App\Repository\ContributorProfileRepository;
use App\Repository\PropertySubmissionRepository;
use App\Repository\ContributorRewardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Admin controller for managing PropertyCrowd Europe contributors
 */
#[Route('/admin/contributors', name: 'admin_contributors_')]
class ContributorManagementController extends AbstractController
{
    private ContributorService $contributorService;
    private ContributorProfileRepository $contributorRepository;
    private PropertySubmissionRepository $submissionRepository;
    private ContributorRewardRepository $rewardRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ContributorService $contributorService,
        ContributorProfileRepository $contributorRepository,
        PropertySubmissionRepository $submissionRepository,
        ContributorRewardRepository $rewardRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->contributorService = $contributorService;
        $this->contributorRepository = $contributorRepository;
        $this->submissionRepository = $submissionRepository;
        $this->rewardRepository = $rewardRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Main contributor dashboard
     */
    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        // Get overall statistics
        $stats = [
            'total_contributors' => $this->contributorRepository->createQueryBuilder('cp')
                ->select('COUNT(cp.id)')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'pending_submissions' => $this->submissionRepository->createQueryBuilder('ps')
                ->select('COUNT(ps.id)')
                ->where('ps.status = :status')
                ->setParameter('status', PropertySubmission::STATUS_PENDING)
                ->getQuery()
                ->getSingleScalarResult(),
                
            'approved_submissions_today' => $this->submissionRepository->createQueryBuilder('ps')
                ->select('COUNT(ps.id)')
                ->where('ps.status = :status')
                ->andWhere('ps.reviewedAt >= :today')
                ->setParameter('status', PropertySubmission::STATUS_APPROVED)
                ->setParameter('today', new \DateTime('today'))
                ->getQuery()
                ->getSingleScalarResult(),
                
            'pending_rewards' => $this->rewardRepository->createQueryBuilder('cr')
                ->select('COUNT(cr.id)')
                ->where('cr.status = :status')
                ->setParameter('status', ContributorReward::STATUS_PENDING)
                ->getQuery()
                ->getSingleScalarResult()
        ];

        // Get top contributors
        $topContributors = $this->contributorService->getEuropeanLeaderboard(10);

        // Get recent submissions
        $recentSubmissions = $this->submissionRepository->createQueryBuilder('ps')
            ->orderBy('ps.submittedAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('admin/contributors/dashboard.html.twig', [
            'stats' => $stats,
            'top_contributors' => $topContributors,
            'recent_submissions' => $recentSubmissions
        ]);
    }

    /**
     * List all contributors
     */
    #[Route('/list', name: 'list')]
    public function listContributors(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;

        // Build query with filters
        $qb = $this->contributorRepository->createQueryBuilder('cp');

        if ($tier = $request->query->get('tier')) {
            $qb->andWhere('cp.tier = :tier')
               ->setParameter('tier', $tier);
        }

        if ($search = $request->query->get('search')) {
            $qb->andWhere('cp.fullName LIKE :search OR cp.europeanId LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $contributors = $qb->orderBy('cp.contributionScore', 'DESC')
                          ->setFirstResult($offset)
                          ->setMaxResults($limit)
                          ->getQuery()
                          ->getResult();

        $total = $qb->select('COUNT(cp.id)')
                    ->setFirstResult(0)
                    ->setMaxResults(null)
                    ->getQuery()
                    ->getSingleScalarResult();

        return $this->render('admin/contributors/list.html.twig', [
            'contributors' => $contributors,
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'filters' => $request->query->all()
        ]);
    }

    /**
     * View contributor details
     */
    #[Route('/{id}', name: 'view')]
    public function viewContributor(ContributorProfile $contributor): Response
    {
        $stats = $this->contributorService->getContributorStats($contributor);
        
        $submissions = $contributor->getPropertySubmissions();
        $rewards = $contributor->getRewards();

        return $this->render('admin/contributors/view.html.twig', [
            'contributor' => $contributor,
            'stats' => $stats,
            'submissions' => $submissions,
            'rewards' => $rewards
        ]);
    }

    /**
     * European leaderboard
     */
    #[Route('/leaderboard', name: 'leaderboard')]
    public function leaderboard(): Response
    {
        $topContributors = $this->contributorService->getEuropeanLeaderboard(100);

        // Group by countries
        $countryStats = [];
        foreach ($topContributors as $contributor) {
            $coverage = $contributor->getGeographicCoverage();
            foreach ($coverage as $country) {
                if (!isset($countryStats[$country])) {
                    $countryStats[$country] = 0;
                }
                $countryStats[$country]++;
            }
        }

        return $this->render('admin/contributors/leaderboard.html.twig', [
            'top_contributors' => $topContributors,
            'country_stats' => $countryStats
        ]);
    }

    /**
     * Property submissions management
     */
    #[Route('/submissions', name: 'submissions')]
    public function submissions(Request $request): Response
    {
        $status = $request->query->get('status', PropertySubmission::STATUS_PENDING);
        
        $submissions = $this->submissionRepository->createQueryBuilder('ps')
            ->where('ps.status = :status')
            ->setParameter('status', $status)
            ->orderBy('ps.submittedAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/contributors/submissions.html.twig', [
            'submissions' => $submissions,
            'current_status' => $status
        ]);
    }

    /**
     * Approve property submission
     */
    #[Route('/submissions/{id}/approve', name: 'submission_approve', methods: ['POST'])]
    public function approveSubmission(PropertySubmission $submission): Response
    {
        $submission->setStatus(PropertySubmission::STATUS_APPROVED);

        $this->entityManager->flush();

        // Award points to contributor
        $this->contributorService->awardSubmissionPoints($submission);

        $this->addFlash('success', 'Property submission approved successfully!');

        return $this->redirectToRoute('admin_contributors_submissions');
    }

    /**
     * Reject property submission
     */
    #[Route('/submissions/{id}/reject', name: 'submission_reject', methods: ['POST'])]
    public function rejectSubmission(Request $request, PropertySubmission $submission): Response
    {
        $reason = $request->request->get('reason', 'Quality standards not met');
        
        $submission->setStatus(PropertySubmission::STATUS_REJECTED);
        $submission->setRejectionReason($reason);

        $this->entityManager->flush();

        $this->addFlash('success', 'Property submission rejected.');

        return $this->redirectToRoute('admin_contributors_submissions');
    }

    /**
     * Rewards management
     */
    #[Route('/rewards', name: 'rewards')]
    public function rewards(): Response
    {
        $pendingRewards = $this->rewardRepository->findByStatus(ContributorReward::STATUS_PENDING);
        $recentRewards = $this->rewardRepository->createQueryBuilder('cr')
            ->where('cr.status = :status')
            ->setParameter('status', ContributorReward::STATUS_APPROVED)
            ->orderBy('cr.awardedAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        return $this->render('admin/contributors/rewards.html.twig', [
            'pending_rewards' => $pendingRewards,
            'recent_rewards' => $recentRewards
        ]);
    }

    /**
     * Award manual reward
     */
    #[Route('/rewards/award', name: 'reward_award', methods: ['POST'])]
    public function awardReward(Request $request): Response
    {
        $contributorId = $request->request->get('contributor_id');
        $type = $request->request->get('type');
        $amount = $request->request->get('amount');
        $description = $request->request->get('description');

        $contributor = $this->contributorRepository->find($contributorId);
        
        if (!$contributor) {
            $this->addFlash('error', 'Contributor not found');
            return $this->redirectToRoute('admin_contributors_rewards');
        }

        $reward = new ContributorReward();
        $reward->setContributor($contributor);
        $reward->setType($type);
        $reward->setAmount($amount);
        $reward->setDescription($description);
        $reward->setStatus(ContributorReward::STATUS_APPROVED);

        $this->entityManager->persist($reward);

        // Update contributor score
        if (is_numeric($amount)) {
            $contributor->setContributionScore($contributor->getContributionScore() + (int)$amount);
            $this->contributorService->checkTierUpgrade($contributor);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Reward awarded successfully!');

        return $this->redirectToRoute('admin_contributors_rewards');
    }

    /**
     * Approve reward
     */
    #[Route('/rewards/{id}/approve', name: 'reward_approve', methods: ['POST'])]
    public function approveReward(ContributorReward $reward): Response
    {
        $reward->setStatus(ContributorReward::STATUS_APPROVED);
        $reward->setAwardedAt(new \DateTimeImmutable());

        // Update contributor score
        $contributor = $reward->getContributor();
        $contributor->setContributionScore($contributor->getContributionScore() + $reward->getAmount());
        
        // Check for tier upgrade
        $this->contributorService->checkTierUpgrade($contributor);

        $this->entityManager->flush();

        $this->addFlash('success', 'Reward approved successfully!');

        return $this->redirectToRoute('admin_contributors_rewards');
    }

    /**
     * Reject reward
     */
    #[Route('/rewards/{id}/reject', name: 'reward_reject', methods: ['POST'])]
    public function rejectReward(Request $request, ContributorReward $reward): Response
    {
        $reason = $request->request->get('reason', 'Does not meet criteria');
        
        $reward->setStatus(ContributorReward::STATUS_REJECTED);
        $reward->setRejectionReason($reason);
        $reward->setAwardedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        $this->addFlash('success', 'Reward rejected.');

        return $this->redirectToRoute('admin_contributors_rewards');
    }

    /**
     * European market analytics
     */
    #[Route('/analytics', name: 'analytics')]
    public function analytics(): Response
    {
        // Country coverage analysis
        $countryCoverage = $this->contributorRepository->createQueryBuilder('cp')
            ->select('gc.country, COUNT(cp.id) as contributor_count')
            ->join('cp.geographicCoverage', 'gc') // This needs to be adjusted based on actual structure
            ->groupBy('gc.country')
            ->orderBy('contributor_count', 'DESC')
            ->getQuery()
            ->getResult();

        // Monthly growth analysis
        $monthlyGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = new \DateTime("first day of -{$i} months");
            $nextDate = new \DateTime("first day of -" . ($i - 1) . " months");
            
            $count = $this->contributorRepository->createQueryBuilder('cp')
                ->select('COUNT(cp.id)')
                ->where('cp.createdAt >= :start')
                ->andWhere('cp.createdAt < :end')
                ->setParameter('start', $date)
                ->setParameter('end', $nextDate)
                ->getQuery()
                ->getSingleScalarResult();
                
            $monthlyGrowth[] = [
                'month' => $date->format('Y-m'),
                'count' => $count
            ];
        }

        // Tier distribution
        $tierDistribution = $this->contributorRepository->createQueryBuilder('cp')
            ->select('cp.tier, COUNT(cp.id) as count')
            ->groupBy('cp.tier')
            ->getQuery()
            ->getResult();

        return $this->render('admin/contributors/analytics.html.twig', [
            'country_coverage' => $countryCoverage,
            'monthly_growth' => $monthlyGrowth,
            'tier_distribution' => $tierDistribution
        ]);
    }

    /**
     * Export contributors data
     */
    #[Route('/export', name: 'export')]
    public function exportContributors(): Response
    {
        $contributors = $this->contributorRepository->findAll();

        $csv = "European ID,Full Name,Company,Tier,Score,Submissions,Country Coverage,Join Date\n";
        
        foreach ($contributors as $contributor) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%d,%d,%s,%s\n",
                $contributor->getEuropeanId(),
                '"' . $contributor->getFullName() . '"',
                '"' . ($contributor->getCompany() ?: 'N/A') . '"',
                $contributor->getTier(),
                $contributor->getContributionScore(),
                $contributor->getTotalSubmissions(),
                '"' . implode(';', $contributor->getGeographicCoverage()) . '"',
                $contributor->getCreatedAt()->format('Y-m-d')
            );
        }

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="property_crowd_contributors_' . date('Y_m_d') . '.csv"');

        return $response;
    }
}
