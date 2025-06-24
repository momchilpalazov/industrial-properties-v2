<?php

namespace App\Command;

use App\Entity\BlogCategory;
use App\Repository\BlogCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(
    name: 'app:blog:create-default-categories',
    description: 'Creates default blog categories from the old hardcoded ones',
)]
class CreateDefaultBlogCategoriesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BlogCategoryRepository $blogCategoryRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $slugger = new AsciiSlugger();

        // Дефинираме категориите които са били хардкоднати
        $defaultCategories = [
            [
                'name' => 'Индустриални статии',
                'nameEn' => 'Industry Articles',
                'nameDe' => 'Industrieartikel',
                'nameRu' => 'Промышленные статьи',
                'description' => 'Статии за индустриални имоти и развития',
                'descriptionEn' => 'Articles about industrial properties and developments',
                'descriptionDe' => 'Artikel über Industrieimmobilien und Entwicklungen',
                'descriptionRu' => 'Статьи о промышленной недвижимости и разработках',
                'slug' => 'industry-articles',
                'position' => 1
            ],
            [
                'name' => 'Новини от сектора',
                'nameEn' => 'Sector News',
                'nameDe' => 'Branchennachrichten',
                'nameRu' => 'Новости сектора',
                'description' => 'Актуални новини от сферата на индустриалните имоти',
                'descriptionEn' => 'Current news from the industrial real estate sector',
                'descriptionDe' => 'Aktuelle Nachrichten aus dem Bereich Industrieimmobilien',
                'descriptionRu' => 'Актуальные новости из сферы промышленной недвижимости',
                'slug' => 'sector-news',
                'position' => 2
            ],
            [
                'name' => 'Съвети за инвеститори',
                'nameEn' => 'Investor Tips',
                'nameDe' => 'Investoren-Tipps',
                'nameRu' => 'Советы инвесторам',
                'description' => 'Полезни съвети и насоки за инвеститори в индустриални имоти',
                'descriptionEn' => 'Useful tips and guidance for industrial real estate investors',
                'descriptionDe' => 'Nützliche Tipps und Anleitungen für Investoren in Industrieimmobilien',
                'descriptionRu' => 'Полезные советы и рекомендации для инвесторов в промышленную недвижимость',
                'slug' => 'investor-tips',
                'position' => 3
            ]
        ];

        $created = 0;
        foreach ($defaultCategories as $categoryData) {
            // Проверяваме дали вече съществува категория с този slug
            $existingCategory = $this->blogCategoryRepository->findOneBy(['slug' => $categoryData['slug']]);
            
            if (!$existingCategory) {
                $category = new BlogCategory();
                $category->setName($categoryData['name']);
                $category->setNameEn($categoryData['nameEn']);
                $category->setNameDe($categoryData['nameDe']);
                $category->setNameRu($categoryData['nameRu']);
                $category->setDescription($categoryData['description']);
                $category->setDescriptionEn($categoryData['descriptionEn']);
                $category->setDescriptionDe($categoryData['descriptionDe']);
                $category->setDescriptionRu($categoryData['descriptionRu']);
                $category->setSlug($categoryData['slug']);
                $category->setPosition($categoryData['position']);
                $category->setIsVisible(true);

                $this->entityManager->persist($category);
                $created++;
                
                $io->success("Създадена категория: {$categoryData['name']}");
            } else {
                $io->note("Категория '{$categoryData['name']}' вече съществува.");
            }
        }

        if ($created > 0) {
            $this->entityManager->flush();
            $io->success("Успешно създадени {$created} категории.");
        } else {
            $io->info('Всички категории вече съществуват.');
        }

        return Command::SUCCESS;
    }
}
