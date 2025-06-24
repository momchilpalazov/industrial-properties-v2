<?php

namespace App\Command;

use App\Entity\FaqCategory;
use App\Repository\FaqCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-default-faq-categories',
    description: 'Създава стандартни FAQ категории',
)]
class CreateDefaultFaqCategoriesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FaqCategoryRepository $faqCategoryRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $defaultCategories = [
            [
                'nameBg' => 'Общи въпроси',
                'nameEn' => 'General Questions',
                'nameDe' => 'Allgemeine Fragen',
                'nameRu' => 'Общие вопросы',
                'slug' => 'general',
                'position' => 1
            ],
            [
                'nameBg' => 'Информация за имоти',
                'nameEn' => 'Property Information',
                'nameDe' => 'Immobilieninformationen',
                'nameRu' => 'Информация о недвижимости',
                'slug' => 'property-info',
                'position' => 2
            ],
            [
                'nameBg' => 'Правни въпроси',
                'nameEn' => 'Legal Questions',
                'nameDe' => 'Rechtliche Fragen',
                'nameRu' => 'Правовые вопросы',
                'slug' => 'legal',
                'position' => 3
            ],
            [
                'nameBg' => 'Наем и продажба',
                'nameEn' => 'Rent and Sale',
                'nameDe' => 'Miete und Verkauf',
                'nameRu' => 'Аренда и продажа',
                'slug' => 'rent-sale',
                'position' => 4
            ]
        ];

        $created = 0;
        foreach ($defaultCategories as $categoryData) {
            // Проверяваме дали вече съществува категория с този slug
            $existingCategory = $this->faqCategoryRepository->findOneBy(['slug' => $categoryData['slug']]);
            
            if (!$existingCategory) {
                $category = new FaqCategory();
                $category->setNameBg($categoryData['nameBg']);
                $category->setNameEn($categoryData['nameEn']);
                $category->setNameDe($categoryData['nameDe']);
                $category->setNameRu($categoryData['nameRu']);
                $category->setSlug($categoryData['slug']);
                $category->setPosition($categoryData['position']);
                $category->setIsVisible(true);

                $this->entityManager->persist($category);
                $created++;
                
                $io->success("Създадена FAQ категория: {$categoryData['nameBg']}");
            } else {
                $io->note("FAQ категория '{$categoryData['nameBg']}' вече съществува.");
            }
        }

        if ($created > 0) {
            $this->entityManager->flush();
            $io->success("Успешно създадени {$created} FAQ категории.");
        } else {
            $io->info('Всички FAQ категории вече съществуват.');
        }

        return Command::SUCCESS;
    }
}
