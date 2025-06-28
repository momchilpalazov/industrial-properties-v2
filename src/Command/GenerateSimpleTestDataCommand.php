<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\ContributorProfile;
use App\Entity\PropertySubmission;
use App\Entity\ContributorReward;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:generate-test-data',
    description: 'Generate test data for PropertyCrowd Europe',
)]
class GenerateSimpleTestDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generating test data for PropertyCrowd Europe');

        try {
            $this->generateUsers($io);
            $this->entityManager->flush(); // Flush after users
            
            $this->generatePropertySubmissions($io);
            $this->entityManager->flush(); // Flush after submissions
            
            $this->generateRewards($io);
            $this->entityManager->flush(); // Final flush

            $io->success('Test data generated successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error generating test data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function generateUsers(SymfonyStyle $io): void
    {
        $io->section('Creating Users and Contributors');

        $countries = ['BG', 'DE', 'FR', 'IT', 'ES', 'PL', 'RO', 'NL', 'BE', 'AT'];
        $cities = [
            'BG' => ['Sofia', 'Plovdiv', 'Varna'],
            'DE' => ['Berlin', 'Munich', 'Hamburg'],
            'FR' => ['Paris', 'Lyon', 'Marseille'],
            'IT' => ['Rome', 'Milan', 'Naples'],
            'ES' => ['Madrid', 'Barcelona', 'Valencia']
        ];

        $tiers = ['BRONZE', 'SILVER', 'GOLD', 'PLATINUM', 'DIAMOND'];
        $backgrounds = ['Real Estate', 'Construction', 'Architecture', 'Investment', 'Engineering'];

        // Check how many contributors already exist to avoid email duplicates
        $existingCount = $this->entityManager->getRepository(ContributorProfile::class)->count([]);
        $startIndex = $existingCount + 1;

        for ($i = $startIndex; $i <= $startIndex + 19; $i++) {
            // Create User
            $user = new User();
            $user->setEmail("contributor{$i}@propertycrowd.eu");
            $user->setName("Contributor {$i}");
            $user->setRoles(['ROLE_USER']);
            
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);

            // Create ContributorProfile
            $country = $countries[array_rand($countries)];
            $cityList = $cities[$country] ?? ['Unknown City'];
            $city = $cityList[array_rand($cityList)];

            $profile = new ContributorProfile();
            $profile->setUser($user);
            $profile->setFullName("Contributor {$i}");
            $profile->setEuropeanId("EU" . str_pad($i, 6, '0', STR_PAD_LEFT));
            $profile->setPhone("+359" . rand(800000000, 999999999));
            $profile->setCountry($country);
            $profile->setCity($city);
            $profile->setTier($tiers[array_rand($tiers)]);
            $profile->setVerificationStatus('verified');
            $profile->setProfessionalBackground($backgrounds[array_rand($backgrounds)]);
            $profile->setExperience(rand(1, 20) . ' years');
            $profile->setMotivation('Passionate about European industrial properties');
            $profile->setLanguages(['en', strtolower($country)]);
            $profile->setExpertiseAreas(['industrial', 'commercial']);
            $profile->setGeographicCoverage([$country]);
            $profile->setAgreeToTerms(true);
            $profile->setSubscribeNewsletter(true);

            $this->entityManager->persist($profile);
        }

        $io->writeln("Created 20 additional users and contributor profiles (starting from #{$startIndex})");
    }

    private function generatePropertySubmissions(SymfonyStyle $io): void
    {
        $io->section('Creating Property Submissions');

        $contributors = $this->entityManager->getRepository(ContributorProfile::class)->findAll();
        
        if (empty($contributors)) {
            $io->warning('No contributors found, skipping property submissions');
            return;
        }

        $statuses = ['pending', 'approved', 'rejected'];
        
        for ($i = 1; $i <= 50; $i++) {
            $contributor = $contributors[array_rand($contributors)];
            
            $submission = new PropertySubmission();
            $submission->setSubmittedBy($contributor);
            $submission->setSubmissionId("SUB" . str_pad($i, 6, '0', STR_PAD_LEFT));
            $submission->setTitleBg("Индустриален имот {$i}");
            $submission->setTitleEn("Industrial Property {$i}");
            $submission->setDescriptionBg("Премиум индустриална база в отлично състояние");
            $submission->setDescriptionEn("Premium industrial facility in excellent condition");
            $submission->setCountry($contributor->getCountry());
            $submission->setLocationBg($contributor->getCity() . ", " . $contributor->getCountry());
            $submission->setLocationEn($contributor->getCity() . ", " . $contributor->getCountry());
            $submission->setArea((string)rand(1000, 50000));
            $submission->setPrice((string)rand(500000, 5000000));
            $submission->setStatus($statuses[array_rand($statuses)]);
            
            // Note: approvedAt and rejectedAt are set automatically when status changes
            
            $this->entityManager->persist($submission);
        }

        $io->writeln('Created 50 property submissions');
    }

    private function generateRewards(SymfonyStyle $io): void
    {
        $io->section('Creating Rewards');

        $contributors = $this->entityManager->getRepository(ContributorProfile::class)->findAll();
        
        if (empty($contributors)) {
            $io->warning('No contributors found, skipping rewards');
            return;
        }

        // For now, let's skip rewards due to potential schema issues
        $io->writeln('Skipping rewards generation for now - will add later');
        return;

        /* Commented out until we resolve the column issue
        $rewardTypes = [
            ContributorReward::TYPE_REVENUE_SHARE,
            ContributorReward::TYPE_REFERRAL_BONUS,
            ContributorReward::TYPE_PREMIUM_LISTING,
            ContributorReward::TYPE_BADGE_ACHIEVEMENT
        ];
        
        $statuses = [
            ContributorReward::STATUS_PENDING,
            ContributorReward::STATUS_APPROVED,
            ContributorReward::STATUS_PAID
        ];

        for ($i = 1; $i <= 30; $i++) {
            $contributor = $contributors[array_rand($contributors)];
            
            $reward = new ContributorReward();
            $reward->setContributor($contributor);
            $reward->setType($rewardTypes[array_rand($rewardTypes)]);
            $reward->setStatus($statuses[array_rand($statuses)]);
            $reward->setAmount((string)rand(100, 2000));
            $reward->setCurrency('EUR');
            $reward->setDescription("Reward for excellent contribution #{$i}");
            
            if ($reward->getStatus() === ContributorReward::STATUS_APPROVED || 
                $reward->getStatus() === ContributorReward::STATUS_PAID) {
                $reward->setAwardedAt(new \DateTimeImmutable('-' . rand(1, 60) . ' days'));
            }
            
            $this->entityManager->persist($reward);
        }

        $io->writeln('Created 30 rewards');
        */
    }
}
