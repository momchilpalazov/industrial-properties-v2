<?php

namespace App\Command;

use App\Entity\Property;
use App\Entity\PropertyCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-property-categories',
    description: 'Updates existing properties to link them with the correct categories',
)]
class UpdatePropertyCategoriesCommand extends Command
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

        // Намираме всички имоти
        $properties = $this->entityManager->getRepository(Property::class)->findAll();

        $updatedCount = 0;

        foreach ($properties as $property) {
            $status = $property->getStatus();
            
            // Определяме категорията според статуса
            switch ($status) {
                case Property::STATUS_RENTED:
                    $property->setCategory($rentCategory);
                    break;
                case Property::STATUS_SOLD:
                case Property::STATUS_RESERVED:
                case Property::STATUS_AVAILABLE:
                    $property->setCategory($saleCategory);
                    break;
                default:
                    // По подразбиране слагаме в категория "Продажба"
                    $property->setCategory($saleCategory);
                    break;
            }
            
            $updatedCount++;
        }

        if ($updatedCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('Successfully updated %d properties.', $updatedCount));
        } else {
            $io->info('No properties found to update.');
        }

        return Command::SUCCESS;
    }
} 