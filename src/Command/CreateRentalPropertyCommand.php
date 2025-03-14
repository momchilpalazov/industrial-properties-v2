<?php

namespace App\Command;

use App\Entity\Property;
use App\Entity\PropertyCategory;
use App\Entity\PropertyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:create-rental-property',
    description: 'Creates a test rental property',
)]
class CreateRentalPropertyCommand extends Command
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

        // Намираме категорията "Под наем"
        $rentCategory = $this->entityManager->getRepository(PropertyCategory::class)
            ->findOneBy(['slug' => 'for-rent']);

        if (!$rentCategory) {
            $io->error('Категорията "Под наем" не е намерена. Моля, изпълнете командата app:create-property-categories първо.');
            return Command::FAILURE;
        }

        // Намираме тип "Офис под наем"
        $officeType = $this->entityManager->getRepository(PropertyType::class)
            ->findOneBy([
                'name' => 'Офис под наем',
                'category' => $rentCategory
            ]);

        if (!$officeType) {
            $io->error('Типът "Офис под наем" не е намерен. Моля, изпълнете командата app:create-property-types първо.');
            return Command::FAILURE;
        }

        // Създаваме тестов имот под наем
        $property = new Property();
        $property->setTitleBg('Офис под наем в бизнес център');
        $property->setTitleEn('Office for rent in business center');
        $property->setDescriptionBg('Модерен офис под наем в бизнес център. Напълно обзаведен и готов за нанасяне.');
        $property->setDescriptionEn('Modern office for rent in business center. Fully furnished and ready to move in.');
        $property->setLocationBg('София, Бизнес Парк');
        $property->setLocationEn('Sofia, Business Park');
        $property->setArea('120');
        $property->setPrice('1500');
        $property->setStatus(Property::STATUS_RENTED);
        $property->setIsActive(true);
        $property->setIsFeatured(false);
        $property->setIsVip(false);
        $property->setCategory($rentCategory);
        $property->setType($officeType);
        
        // Генерираме уникален slug с добавяне на случайно число
        $baseSlug = $this->slugger->slug($property->getTitleBg())->lower();
        $randomNumber = rand(1000, 9999);
        $slug = $baseSlug . '-' . $randomNumber;
        $property->setSlug($slug);
        
        // Генерираме референтен номер
        $property->setReferenceNumber('RNT-' . rand(1000, 9999));

        $this->entityManager->persist($property);
        $this->entityManager->flush();

        $io->success(sprintf('Успешно създаден имот под наем с ID: %d', $property->getId()));

        return Command::SUCCESS;
    }
} 