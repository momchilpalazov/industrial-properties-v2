<?php

namespace App\Command;

use App\Entity\PropertyCategory;
use App\Entity\PropertyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-property-types',
    description: 'Creates basic property types',
)]
class CreatePropertyTypesCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Намираме категориите
        $saleCategory = $this->entityManager->getRepository(PropertyCategory::class)
            ->findOneBy(['slug' => 'for-sale']);
        $rentCategory = $this->entityManager->getRepository(PropertyCategory::class)
            ->findOneBy(['slug' => 'for-rent']);
        $investmentCategory = $this->entityManager->getRepository(PropertyCategory::class)
            ->findOneBy(['slug' => 'investment']);

        if (!$saleCategory || !$rentCategory || !$investmentCategory) {
            $io->error('Не са намерени всички категории. Моля, изпълнете командата app:create-property-categories първо.');
            return Command::FAILURE;
        }

        $types = [
            // Типове за продажба
            [
                'name' => 'Офис сграда',
                'nameEn' => 'Office Building',
                'description' => 'Офис сграда за продажба',
                'descriptionEn' => 'Office building for sale',
                'category' => $saleCategory,
                'position' => 1
            ],
            [
                'name' => 'Индустриален парцел',
                'nameEn' => 'Industrial Land',
                'description' => 'Индустриален парцел за продажба',
                'descriptionEn' => 'Industrial land for sale',
                'category' => $saleCategory,
                'position' => 2
            ],
            [
                'name' => 'Логистична база',
                'nameEn' => 'Logistics Base',
                'description' => 'Логистична база за продажба',
                'descriptionEn' => 'Logistics base for sale',
                'category' => $saleCategory,
                'position' => 3
            ],
            
            // Типове под наем
            [
                'name' => 'Офис под наем',
                'nameEn' => 'Office for Rent',
                'description' => 'Офис под наем',
                'descriptionEn' => 'Office for rent',
                'category' => $rentCategory,
                'position' => 1
            ],
            [
                'name' => 'Склад под наем',
                'nameEn' => 'Warehouse for Rent',
                'description' => 'Склад под наем',
                'descriptionEn' => 'Warehouse for rent',
                'category' => $rentCategory,
                'position' => 2
            ],
            [
                'name' => 'Производствено помещение под наем',
                'nameEn' => 'Production Facility for Rent',
                'description' => 'Производствено помещение под наем',
                'descriptionEn' => 'Production facility for rent',
                'category' => $rentCategory,
                'position' => 3
            ],
            
            // Типове за инвестиция
            [
                'name' => 'Инвестиционен проект',
                'nameEn' => 'Investment Project',
                'description' => 'Инвестиционен проект',
                'descriptionEn' => 'Investment project',
                'category' => $investmentCategory,
                'position' => 1
            ],
            [
                'name' => 'Бизнес за продажба',
                'nameEn' => 'Business for Sale',
                'description' => 'Бизнес за продажба',
                'descriptionEn' => 'Business for sale',
                'category' => $investmentCategory,
                'position' => 2
            ]
        ];

        $createdCount = 0;

        foreach ($types as $typeData) {
            // Проверяваме дали типът вече съществува
            $existingType = $this->entityManager->getRepository(PropertyType::class)
                ->findOneBy([
                    'name' => $typeData['name'],
                    'category' => $typeData['category']
                ]);

            if (!$existingType) {
                $type = new PropertyType();
                $type->setName($typeData['name']);
                $type->setNameEn($typeData['nameEn']);
                $type->setDescription($typeData['description']);
                $type->setDescriptionEn($typeData['descriptionEn']);
                $type->setCategory($typeData['category']);
                $type->setPosition($typeData['position']);
                $type->setLevel(0);
                $type->setIsVisible(true);

                $this->entityManager->persist($type);
                $createdCount++;
            }
        }

        if ($createdCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('Успешно създадени %d типа имоти.', $createdCount));
        } else {
            $io->info('Всички типове имоти вече съществуват.');
        }

        return Command::SUCCESS;
    }
} 