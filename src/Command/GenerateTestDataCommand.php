<?php

namespace App\Command;

use App\Entity\ContributorProfile;
use App\Entity\ContributorReward;
use App\Entity\PropertySubmission;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:generate-test-data',
    description: 'Generate test data for PropertyCrowd Europe'
)]
class GenerateTestDataCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generating test data for PropertyCrowd Europe');

        // Create test users and contributors
        $contributors = $this->createTestContributors($io);
        
        // Create test property submissions
        $this->createTestSubmissions($io, $contributors);
        
        // Create test rewards
        $this->createTestRewards($io, $contributors);

        $this->entityManager->flush();

        $io->success('Test data generated successfully!');
        return Command::SUCCESS;
    }

    private function createTestContributors(SymfonyStyle $io): array
    {
        $io->section('Creating test contributors...');

        $contributors = [];
        $countries = ['Bulgaria', 'Romania', 'Greece', 'Serbia', 'Croatia', 'Slovenia'];
        $cities = [
            'Bulgaria' => ['Sofia', 'Plovdiv', 'Varna', 'Burgas'],
            'Romania' => ['Bucharest', 'Cluj-Napoca', 'Timisoara', 'Constanta'],
            'Greece' => ['Athens', 'Thessaloniki', 'Patras', 'Heraklion'],
            'Serbia' => ['Belgrade', 'Novi Sad', 'Niš', 'Kragujevac'],
            'Croatia' => ['Zagreb', 'Split', 'Rijeka', 'Osijek'],
            'Slovenia' => ['Ljubljana', 'Maribor', 'Celje', 'Kranj']
        ];

        $tiers = [ContributorProfile::TIER_BRONZE, ContributorProfile::TIER_SILVER, ContributorProfile::TIER_GOLD, ContributorProfile::TIER_PLATINUM];
        $backgrounds = ['Real Estate Agent', 'Property Developer', 'Investment Advisor', 'Architect', 'Urban Planner', 'Business Owner'];

        for ($i = 1; $i <= 25; $i++) {
            // Create user
            $user = new User();
            $user->setEmail("contributor{$i}@example.com");
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setRoles(['ROLE_CONTRIBUTOR']);
            $user->setIsActive(true);
            $this->entityManager->persist($user);

            // Create contributor profile
            $country = $countries[array_rand($countries)];
            $contributor = new ContributorProfile();
            $contributor->setUser($user);
            $contributor->setEuropeanId('EU' . str_pad($i, 6, '0', STR_PAD_LEFT));
            $contributor->setFullName("Test Contributor {$i}");
            $contributor->setCompany($i % 3 == 0 ? "Company {$i} Ltd" : null);
            $contributor->setCountry($country);
            $contributor->setCity($cities[$country][array_rand($cities[$country])]);
            $contributor->setPhone('+359' . rand(100000000, 999999999));
            $contributor->setProfessionalBackground($backgrounds[array_rand($backgrounds)]);
            $contributor->setExperience(rand(1, 20) . ' years');
            $contributor->setMotivation('Passionate about European real estate market development.');
            $contributor->setAgreeToTerms(true);
            $contributor->setSubscribeNewsletter(rand(0, 1) == 1);
            $contributor->setLanguages(['en', 'bg']);
            $contributor->setExpertiseAreas(['commercial', 'residential']);
            $contributor->setGeographicCoverage([$country]);
            $contributor->setTier($tiers[array_rand($tiers)]);
            $contributor->setContributionScore(rand(50, 5000));
            $contributor->setVerificationStatus(ContributorProfile::STATUS_VERIFIED);
            $contributor->setIdentityVerified(true);
            $contributor->setPhoneVerified(true);
            $contributor->setCompanyVerified($contributor->getCompany() !== null);
            
            $this->entityManager->persist($contributor);
            $contributors[] = $contributor;
        }

        $io->writeln("Created " . count($contributors) . " test contributors");
        return $contributors;
    }

    private function createTestSubmissions(SymfonyStyle $io, array $contributors): void
    {
        $io->section('Creating test property submissions...');

        $propertyTypes = ['industrial', 'warehouse', 'office', 'retail', 'land', 'mixed_use'];
        $statuses = [PropertySubmission::STATUS_PENDING, PropertySubmission::STATUS_APPROVED, PropertySubmission::STATUS_REJECTED];
        $countries = ['Bulgaria', 'Romania', 'Greece', 'Serbia', 'Croatia', 'Slovenia'];

        for ($i = 1; $i <= 50; $i++) {
            $contributor = $contributors[array_rand($contributors)];
            $status = $statuses[array_rand($statuses)];
            $country = $countries[array_rand($countries)];

            $submission = new PropertySubmission();
            $submission->setContributor($contributor);
            $submission->setPropertyName("Industrial Property {$i}");
            $submission->setDescription("This is a test property submission for PropertyCrowd Europe testing purposes. Property {$i} description with details about location and features.");
            $submission->setCountry($country);
            $submission->setCity("Test City {$i}");
            $submission->setAddress("Test Address {$i}, Street {$i}");
            $submission->setPropertyType($propertyTypes[array_rand($propertyTypes)]);
            $submission->setTotalArea(rand(500, 50000));
            $submission->setPrice(rand(100000, 10000000));
            $submission->setPriceType('sale');
            $submission->setStatus($status);
            $submission->setSubmittedAt(new \DateTimeImmutable('-' . rand(1, 90) . ' days'));
            
            if ($status === PropertySubmission::STATUS_APPROVED) {
                $submission->setApprovedAt(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));
            } elseif ($status === PropertySubmission::STATUS_REJECTED) {
                $submission->setRejectedAt(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));
                $submission->setRejectionReason('incomplete_information');
            }

            $this->entityManager->persist($submission);
        }

        $io->writeln("Created 50 test property submissions");
    }

    private function createTestRewards(SymfonyStyle $io, array $contributors): void
    {
        $io->section('Creating test rewards...');

        $rewardTypes = [
            ContributorReward::TYPE_REVENUE_SHARE,
            ContributorReward::TYPE_REFERRAL_BONUS,
            ContributorReward::TYPE_PREMIUM_LISTING,
            ContributorReward::TYPE_BADGE_ACHIEVEMENT,
            ContributorReward::TYPE_VIP_PROMOTION,
            ContributorReward::TYPE_TIER_UPGRADE,
            ContributorReward::TYPE_SPECIAL_BONUS
        ];

        $statuses = [
            ContributorReward::STATUS_PENDING,
            ContributorReward::STATUS_APPROVED,
            ContributorReward::STATUS_PAID,
            ContributorReward::STATUS_REJECTED
        ];

        for ($i = 1; $i <= 30; $i++) {
            $contributor = $contributors[array_rand($contributors)];
            $type = $rewardTypes[array_rand($rewardTypes)];
            $status = $statuses[array_rand($statuses)];

            $reward = new ContributorReward();
            $reward->setContributor($contributor);
            $reward->setType($type);
            $reward->setReason("Test reward {$i} for {$type}");
            $reward->setPoints(rand(10, 500));
            $reward->setMonetaryValue(rand(50, 1000));
            $reward->setStatus($status);
            $reward->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 60) . ' days'));
            
            if ($status === ContributorReward::STATUS_APPROVED || $status === ContributorReward::STATUS_PAID) {
                $reward->setAwardedAt(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));
            }

            $this->entityManager->persist($reward);
        }

        $io->writeln("Created 30 test rewards");
    }
}
