<?php

namespace App\Controller\Public;

use App\Entity\PropertySubmission;
use App\Entity\ContributorProfile;
use App\Entity\User;
use App\Form\PropertySubmissionType;
use App\Form\ContributorRegistrationType;
use App\Form\ContributorProfileEditType;
use App\Service\SubmissionAiService;
use App\Service\ContributorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/property-crowd', name: 'property_crowd_')]
class PropertyCrowdController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SubmissionAiService $submissionAiService,
        private ContributorService $contributorService
    ) {}

    /**
     * Landing page for PropertyCrowd Europe
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $stats = [
            'total_contributors' => $this->contributorService->getTotalContributors(),
            'total_submissions' => $this->contributorService->getTotalSubmissions(),
            'approved_properties' => $this->contributorService->getApprovedProperties(),
            'countries_covered' => $this->contributorService->getCountriesCovered(),
            'total_earnings' => $this->contributorService->getTotalEarningsDistributed(),
            'success_stories' => $this->contributorService->getSuccessStories(3)
        ];

        return $this->render('property_crowd/index.html.twig', [
            'stats' => $stats,
        ]);
    }

    /**
     * Become a contributor registration
     */
    #[Route('/join', name: 'join')]
    public function join(Request $request): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        
        if ($user && $user->isContributor()) {
            return $this->redirectToRoute('property_crowd_dashboard');
        }

        $contributorProfile = new ContributorProfile();
        
        // If user is logged in, pre-fill some data
        if ($user) {
            $contributorProfile->setUser($user);
            $contributorProfile->setFullName($user->getName());
        }

        $form = $this->createForm(ContributorRegistrationType::class, $contributorProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Generate European ID
                $europeanId = $this->submissionAiService->generateEuropeanId($contributorProfile);
                $contributorProfile->setEuropeanId($europeanId);

                // Set initial verification status
                $contributorProfile->setVerificationStatus(ContributorProfile::STATUS_PENDING);

                $this->entityManager->persist($contributorProfile);
                $this->entityManager->flush();

                // Send verification emails, start KYC process, etc.
                $this->contributorService->initiateVerificationProcess($contributorProfile);

                $this->addFlash('success', 'Welcome to PropertyCrowd Europe! Your European ID is: ' . $europeanId);
                
                return $this->redirectToRoute('property_crowd_verification', ['id' => $contributorProfile->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Registration failed: ' . $e->getMessage());
            }
        }

        return $this->render('property_crowd/join.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Smart property submission wizard
     */
    #[Route('/submit', name: 'submit')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function submitProperty(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $contributor = $user->getContributorProfile();
        
        if (!$contributor || !$contributor->isFullyVerified()) {
            $this->addFlash('warning', 'You must be a verified contributor to submit properties.');
            return $this->redirectToRoute('property_crowd_join');
        }

        $submission = new PropertySubmission();
        $submission->setSubmittedBy($contributor);

        // Generate submission ID
        $submissionId = $this->submissionAiService->generateSubmissionId($submission);
        $submission->setSubmissionId($submissionId);

        $form = $this->createForm(PropertySubmissionType::class, $submission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($submission);
                $this->entityManager->flush();

                // Trigger AI analysis in background
                $this->submissionAiService->analyzeSubmission($submission);

                // Update contributor metrics
                $contributor->setTotalSubmissions($contributor->getTotalSubmissions() + 1);
                $contributor->setLastActivityAt(new \DateTimeImmutable());
                $this->entityManager->flush();

                $this->addFlash('success', 
                    'Property submitted successfully! Submission ID: ' . $submissionId . 
                    '. Our AI is now analyzing your submission.'
                );

                return $this->redirectToRoute('property_crowd_submission_status', [
                    'submissionId' => $submissionId
                ]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Submission failed: ' . $e->getMessage());
            }
        }

        return $this->render('property_crowd/submit.html.twig', [
            'form' => $form->createView(),
            'contributor' => $contributor,
        ]);
    }

    /**
     * AI-powered smart assistance during submission
     */
    #[Route('/api/smart-assist', name: 'smart_assist', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function smartAssist(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $step = $data['step'] ?? '';
        $formData = $data['form_data'] ?? [];

        $response = ['suggestions' => [], 'validations' => [], 'enhancements' => []];

        try {
            switch ($step) {
                case 'location':
                    $response = $this->contributorService->getLocationSuggestions($formData['location'] ?? '');
                    break;

                case 'pricing':
                    $response = $this->contributorService->getPricingSuggestions($formData);
                    break;

                case 'description':
                    $response = $this->contributorService->getDescriptionEnhancements($formData);
                    break;

                case 'images':
                    $response = $this->contributorService->analyzeImages($formData['images'] ?? []);
                    break;

                case 'validation':
                    $response = $this->contributorService->validateSubmissionData($formData);
                    break;
            }

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse($response);
    }

    /**
     * Contributor dashboard
     */
    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function dashboard(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $contributor = $user->getContributorProfile();
        
        if (!$contributor) {
            return $this->redirectToRoute('property_crowd_join');
        }

        $stats = $this->contributorService->getContributorStats($contributor);
        $recentSubmissions = $this->contributorService->getRecentSubmissions($contributor, 10);
        $pendingRewards = $this->contributorService->getPendingRewards($contributor);
        $leaderboardPosition = $this->contributorService->getLeaderboardPosition($contributor);

        return $this->render('property_crowd/dashboard.html.twig', [
            'contributor' => $contributor,
            'stats' => $stats,
            'recent_submissions' => $recentSubmissions,
            'pending_rewards' => $pendingRewards,
            'leaderboard_position' => $leaderboardPosition,
        ]);
    }

    /**
     * Submission status tracking
     */
    #[Route('/submission/{submissionId}', name: 'submission_status')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function submissionStatus(string $submissionId): Response
    {
        $submission = $this->entityManager->getRepository(PropertySubmission::class)
            ->findOneBy(['submissionId' => $submissionId]);

        if (!$submission) {
            throw $this->createNotFoundException('Submission not found');
        }

        // Check if user owns this submission
        if ($submission->getSubmittedBy()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied');
        }

        $timeline = $this->contributorService->getSubmissionTimeline($submission);
        $aiAnalysis = $this->contributorService->getAiAnalysisForSubmission($submission);

        return $this->render('property_crowd/submission_status.html.twig', [
            'submission' => $submission,
            'timeline' => $timeline,
            'ai_analysis' => $aiAnalysis,
        ]);
    }

    /**
     * Community leaderboard
     */
    #[Route('/leaderboard', name: 'leaderboard')]
    public function leaderboard(Request $request): Response
    {
        $period = $request->query->get('period', 'all_time'); // all_time, monthly, yearly
        $country = $request->query->get('country', 'all');
        $tier = $request->query->get('tier', 'all');

        $leaderboard = $this->contributorService->getLeaderboard([
            'period' => $period,
            'country' => $country,
            'tier' => $tier,
            'limit' => 50
        ]);

        $countries = $this->contributorService->getActiveCountries();
        $tiers = ContributorProfile::getTiersList();

        return $this->render('property_crowd/leaderboard.html.twig', [
            'leaderboard' => $leaderboard,
            'countries' => $countries,
            'tiers' => $tiers,
            'filters' => [
                'period' => $period,
                'country' => $country,
                'tier' => $tier
            ]
        ]);
    }

    /**
     * Success stories showcase
     */
    #[Route('/success-stories', name: 'success_stories')]
    public function successStories(): Response
    {
        $stories = $this->contributorService->getSuccessStories();
        $testimonials = $this->contributorService->getTestimonials();
        $stats = $this->contributorService->getPlatformStats();

        return $this->render('property_crowd/success_stories.html.twig', [
            'stories' => $stories,
            'testimonials' => $testimonials,
            'stats' => $stats,
        ]);
    }

    /**
     * Market intelligence for contributors
     */
    #[Route('/market-intelligence', name: 'market_intelligence')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function marketIntelligence(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $contributor = $user->getContributorProfile();
        
        if (!$contributor || $contributor->getTier() === ContributorProfile::TIER_BRONZE) {
            $this->addFlash('info', 'Market Intelligence is available for Silver+ contributors.');
            return $this->redirectToRoute('property_crowd_dashboard');
        }

        $country = $request->query->get('country', $contributor->getGeographicCoverage()[0] ?? 'BG');
        $propertyType = $request->query->get('type', 'all');

        $marketData = $this->contributorService->getMarketIntelligence([
            'country' => $country,
            'property_type' => $propertyType,
            'contributor_tier' => $contributor->getTier()
        ]);

        return $this->render('property_crowd/market_intelligence.html.twig', [
            'market_data' => $marketData,
            'contributor' => $contributor,
            'filters' => [
                'country' => $country,
                'property_type' => $propertyType
            ]
        ]);
    }

    /**
     * Rewards and earnings tracking
     */
    #[Route('/rewards', name: 'rewards')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function rewards(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $contributor = $user->getContributorProfile();
        
        if (!$contributor) {
            return $this->redirectToRoute('property_crowd_join');
        }

        $rewardsHistory = $this->contributorService->getRewardsHistory($contributor);
        $pendingRewards = $this->contributorService->getPendingRewards($contributor);
        $totalEarnings = $this->contributorService->getTotalEarnings($contributor);
        $nextTierRequirements = $this->contributorService->getNextTierRequirements($contributor);

        return $this->render('property_crowd/rewards.html.twig', [
            'contributor' => $contributor,
            'rewards_history' => $rewardsHistory,
            'pending_rewards' => $pendingRewards,
            'total_earnings' => $totalEarnings,
            'next_tier_requirements' => $nextTierRequirements,
        ]);
    }

    /**
     * Contributor profile editing
     */
    #[Route('/profile/edit', name: 'profile_edit')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editProfile(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $contributor = $user->getContributorProfile();
        
        if (!$contributor) {
            return $this->redirectToRoute('property_crowd_join');
        }

        $form = $this->createForm(ContributorProfileEditType::class, $contributor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();

                $this->addFlash('success', 'Профилът ви беше успешно обновен!');
                
                return $this->redirectToRoute('property_crowd_dashboard');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Грешка при обновяване на профила: ' . $e->getMessage());
            }
        }

        return $this->render('property_crowd/profile/edit.html.twig', [
            'form' => $form->createView(),
            'contributor' => $contributor,
        ]);
    }

    /**
     * View contributor profile
     */
    #[Route('/profile', name: 'profile_view')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function viewProfile(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $contributor = $user->getContributorProfile();
        
        if (!$contributor) {
            return $this->redirectToRoute('property_crowd_join');
        }

        $stats = $this->contributorService->getContributorStats($contributor);
        $achievements = $this->contributorService->getContributorAchievements($contributor);
        $recentSubmissions = $this->contributorService->getRecentSubmissions($contributor, 5);

        return $this->render('property_crowd/profile/view.html.twig', [
            'contributor' => $contributor,
            'stats' => $stats,
            'achievements' => $achievements,
            'recent_submissions' => $recentSubmissions,
        ]);
    }

    /**
     * My submissions list
     */
    #[Route('/submissions', name: 'my_submissions')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function mySubmissions(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $contributor = $user->getContributorProfile();
        
        if (!$contributor) {
            return $this->redirectToRoute('property_crowd_join');
        }

        $status = $request->query->get('status', 'all');
        $country = $request->query->get('country', 'all');
        $sort = $request->query->get('sort', 'latest');
        $page = $request->query->getInt('page', 1);

        $filters = [
            'status' => $status,
            'country' => $country,
            'sort' => $sort,
            'page' => $page,
            'limit' => 20
        ];

        $submissions = $this->contributorService->getContributorSubmissions($contributor, $filters);
        $submissionStats = $this->contributorService->getSubmissionStats($contributor);

        return $this->render('property_crowd/submissions/my_submissions.html.twig', [
            'submissions' => $submissions,
            'submission_stats' => $submissionStats,
            'contributor' => $contributor,
            'filters' => $filters,
        ]);
    }

    /**
     * Submission detail view
     */
    #[Route('/submissions/{submissionId}/detail', name: 'submission_detail')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function submissionDetail(string $submissionId): Response
    {
        $submission = $this->entityManager->getRepository(PropertySubmission::class)
            ->findOneBy(['submissionId' => $submissionId]);

        if (!$submission) {
            throw $this->createNotFoundException('Submission не е намерен');
        }

        // Check if user owns this submission
        if ($submission->getSubmittedBy()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Достъпът е отказан');
        }

        $timeline = $this->contributorService->getSubmissionTimeline($submission);
        $aiAnalysis = $this->contributorService->getAiAnalysisForSubmission($submission);
        $reviews = $this->contributorService->getSubmissionReviews($submission);
        $relatedSubmissions = $this->contributorService->getRelatedSubmissions($submission);

        return $this->render('property_crowd/submissions/submission_detail.html.twig', [
            'submission' => $submission,
            'timeline' => $timeline,
            'ai_analysis' => $aiAnalysis,
            'reviews' => $reviews,
            'related_submissions' => $relatedSubmissions,
        ]);
    }

    /**
     * Verification process
     */
    #[Route('/verification/{id}', name: 'verification')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function verification(ContributorProfile $contributorProfile, Request $request): Response
    {
        // Check if user owns this profile
        if ($contributorProfile->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Достъпът е отказан');
        }

        $verificationSteps = $this->contributorService->getVerificationSteps($contributorProfile);
        $requiredDocuments = $this->contributorService->getRequiredDocuments($contributorProfile);
        $verificationStatus = $this->contributorService->getVerificationStatus($contributorProfile);

        // Handle document upload
        if ($request->isMethod('POST') && $request->files->has('verification_document')) {
            try {
                $uploadedFile = $request->files->get('verification_document');
                $documentType = $request->request->get('document_type');
                
                $this->contributorService->uploadVerificationDocument(
                    $contributorProfile,
                    $uploadedFile,
                    $documentType
                );

                $this->addFlash('success', 'Документът беше успешно качен!');
                
                return $this->redirectToRoute('property_crowd_verification', ['id' => $contributorProfile->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Грешка при качване на документа: ' . $e->getMessage());
            }
        }

        return $this->render('property_crowd/verification.html.twig', [
            'contributor' => $contributorProfile,
            'verification_steps' => $verificationSteps,
            'required_documents' => $requiredDocuments,
            'verification_status' => $verificationStatus,
        ]);
    }
}
