<?php

namespace App\Command;

use App\Entity\PropertyCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:create-property-categories',
    description: 'Creates the basic property categories',
)]
class CreatePropertyCategoriesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $categories = [
            [
                'name' => 'Продажба',
                'nameEn' => 'For Sale',
                'description' => 'Имоти за продажба',
                'descriptionEn' => 'Properties for sale',
                'position' => 1,
                'slug' => 'for-sale'
            ],
            [
                'name' => 'Под наем',
                'nameEn' => 'For Rent',
                'description' => 'Имоти под наем',
                'descriptionEn' => 'Properties for rent',
                'position' => 2,
                'slug' => 'for-rent'
            ],
            [
                'name' => 'Инвестиция',
                'nameEn' => 'Investment',
                'description' => 'Инвестиционни имоти',
                'descriptionEn' => 'Investment properties',
                'position' => 3,
                'slug' => 'investment'
            ]
        ];

        $createdCount = 0;

        foreach ($categories as $categoryData) {
            $existingCategory = $this->entityManager->getRepository(PropertyCategory::class)
                ->findOneBy(['slug' => $categoryData['slug']]);

            if (!$existingCategory) {
                $category = new PropertyCategory();
                $category->setName($categoryData['name']);
                $category->setNameEn($categoryData['nameEn']);
                $category->setDescription($categoryData['description']);
                $category->setDescriptionEn($categoryData['descriptionEn']);
                $category->setPosition($categoryData['position']);
                $category->setSlug($categoryData['slug']);
                $category->setIsVisible(true);

                $this->entityManager->persist($category);
                $createdCount++;
            }
        }

        if ($createdCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('Successfully created %d property categories.', $createdCount));
        } else {
            $io->info('All property categories already exist.');
        }

        return Command::SUCCESS;
    }
} 