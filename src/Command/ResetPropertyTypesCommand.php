<?php

namespace App\Command;

use App\Entity\PropertyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reset-property-types',
    description: 'Изтрива всички типове имоти и създава нови с правилната структура',
)]
class ResetPropertyTypesCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }
    
    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Принудително изтриване, дори ако има свързани имоти')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');
        
        // Проверка дали има свързани имоти
        $propertyCount = $this->entityManager->createQuery('SELECT COUNT(p) FROM App\Entity\Property p')
            ->getSingleScalarResult();
        
        if ($propertyCount > 0 && !$force) {
            $io->error('Има ' . $propertyCount . ' имота, свързани с типове имоти. Не можем да изтрием типовете имоти.');
            $io->note('Използвайте опцията --force, за да изтриете типовете имоти, въпреки че има свързани имоти.');
            $io->caution('ВНИМАНИЕ: Това може да доведе до проблеми с имотите, които са свързани с изтритите типове!');
            return Command::FAILURE;
        }
        
        if ($propertyCount > 0 && $force) {
            $io->warning('Има ' . $propertyCount . ' имота, свързани с типове имоти. Ще изтрием типовете имоти принудително.');
            $io->caution('ВНИМАНИЕ: Това може да доведе до проблеми с имотите, които са свързани с изтритите типове!');
            
            // Питаме потребителя дали е сигурен
            if (!$io->confirm('Сигурни ли сте, че искате да продължите?', false)) {
                $io->note('Операцията е отменена.');
                return Command::SUCCESS;
            }
        }
        
        // Изтриваме всички типове имоти
        $io->section('Изтриване на съществуващите типове имоти');
        
        $conn = $this->entityManager->getConnection();
        
        try {
            // Изключваме временно проверката за чужди ключове
            $conn->executeQuery('SET FOREIGN_KEY_CHECKS=0');
            
            // Изтриваме всички записи
            $deletedRows = $conn->executeQuery('DELETE FROM property_types')->rowCount();
            
            // Нулираме брояча на автоинкрементиране
            $conn->executeQuery('ALTER TABLE property_types AUTO_INCREMENT = 1');
            
            // Включваме отново проверката за чужди ключове
            $conn->executeQuery('SET FOREIGN_KEY_CHECKS=1');
            
            $io->success('Изтрити са ' . $deletedRows . ' типа имоти.');
        } catch (\Exception $e) {
            $io->error('Грешка при изтриване на типовете имоти: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        // Създаваме нови типове имоти
        $io->section('Създаване на нови типове имоти');
        
        // Основни категории
        $rentalCategory = $this->createPropertyType('Под наем', 'For Rent', 'Имоти под наем', 'Properties for rent', null, 0, 0, true);
        $saleCategory = $this->createPropertyType('Продажба', 'For Sale', 'Имоти за продажба', 'Properties for sale', null, 0, 1, true);
        $commercialCategory = $this->createPropertyType('Търговски', 'Commercial', 'Търговски имоти', 'Commercial properties', null, 0, 2, true);
        
        $io->success('Създадени са 3 основни категории.');
        
        // Подкатегории за "Под наем"
        $this->createPropertyType('Офис сграда', 'Office Building', 'Офис сгради под наем', 'Office buildings for rent', $rentalCategory, 1, 0, true);
        $this->createPropertyType('Склад', 'Warehouse', 'Складове под наем', 'Warehouses for rent', $rentalCategory, 1, 1, true);
        $this->createPropertyType('Логистичен център', 'Logistics Center', 'Логистични центрове под наем', 'Logistics centers for rent', $rentalCategory, 1, 2, true);
        $this->createPropertyType('Търговски площи', 'Retail Space', 'Търговски площи под наем', 'Retail spaces for rent', $rentalCategory, 1, 3, true);
        $this->createPropertyType('Парцел', 'Land', 'Парцели под наем', 'Land for rent', $rentalCategory, 1, 4, true);
        $this->createPropertyType('Хотел', 'Hotel', 'Хотели под наем', 'Hotels for rent', $rentalCategory, 1, 5, true);
        $this->createPropertyType('Производствено помещение', 'Industrial Space', 'Производствени помещения под наем', 'Industrial spaces for rent', $rentalCategory, 1, 6, true);
        
        $io->success('Създадени са 7 подкатегории за "Под наем".');
        
        // Подкатегории за "Продажба"
        $this->createPropertyType('Офис сграда', 'Office Building', 'Офис сгради за продажба', 'Office buildings for sale', $saleCategory, 1, 0, true);
        $this->createPropertyType('Склад', 'Warehouse', 'Складове за продажба', 'Warehouses for sale', $saleCategory, 1, 1, true);
        $this->createPropertyType('Логистичен център', 'Logistics Center', 'Логистични центрове за продажба', 'Logistics centers for sale', $saleCategory, 1, 2, true);
        $this->createPropertyType('Търговски площи', 'Retail Space', 'Търговски площи за продажба', 'Retail spaces for sale', $saleCategory, 1, 3, true);
        $this->createPropertyType('Парцел', 'Land', 'Парцели за продажба', 'Land for sale', $saleCategory, 1, 4, true);
        $this->createPropertyType('Хотел', 'Hotel', 'Хотели за продажба', 'Hotels for sale', $saleCategory, 1, 5, true);
        $this->createPropertyType('Производствено помещение', 'Industrial Space', 'Производствени помещения за продажба', 'Industrial spaces for sale', $saleCategory, 1, 6, true);
        
        $io->success('Създадени са 7 подкатегории за "Продажба".');
        
        // Подкатегории за "Търговски"
        $this->createPropertyType('Магазин', 'Shop', 'Магазини', 'Shops', $commercialCategory, 1, 0, true);
        $this->createPropertyType('Ресторант', 'Restaurant', 'Ресторанти', 'Restaurants', $commercialCategory, 1, 1, true);
        $this->createPropertyType('Кафе', 'Cafe', 'Кафета', 'Cafes', $commercialCategory, 1, 2, true);
        $this->createPropertyType('Бар', 'Bar', 'Барове', 'Bars', $commercialCategory, 1, 3, true);
        $this->createPropertyType('Нощен клуб', 'Night Club', 'Нощни клубове', 'Night clubs', $commercialCategory, 1, 4, true);
        $this->createPropertyType('Търговски център', 'Shopping Mall', 'Търговски центрове', 'Shopping malls', $commercialCategory, 1, 5, true);
        $this->createPropertyType('Супермаркет', 'Supermarket', 'Супермаркети', 'Supermarkets', $commercialCategory, 1, 6, true);
        
        $io->success('Създадени са 7 подкатегории за "Търговски".');
        
        $io->success('Всички типове имоти са създадени успешно!');
        
        return Command::SUCCESS;
    }
    
    private function createPropertyType(
        string $name, 
        ?string $nameEn, 
        ?string $description, 
        ?string $descriptionEn, 
        ?PropertyType $parent, 
        int $level, 
        int $position, 
        bool $isVisible
    ): PropertyType {
        $propertyType = new PropertyType();
        $propertyType->setName($name);
        $propertyType->setNameEn($nameEn);
        $propertyType->setDescription($description);
        $propertyType->setDescriptionEn($descriptionEn);
        $propertyType->setParent($parent);
        $propertyType->setLevel($level);
        $propertyType->setPosition($position);
        $propertyType->setIsVisible($isVisible);
        
        $this->entityManager->persist($propertyType);
        $this->entityManager->flush();
        
        return $propertyType;
    }
} 