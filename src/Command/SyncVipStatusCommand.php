<?php

namespace App\Command;

use App\Repository\PropertyRepository;
use App\Service\VipService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-vip-status',
    description: 'Синхронизира VIP статусите на имотите с техните активни промоции',
)]
class SyncVipStatusCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PropertyRepository $propertyRepository,
        private VipService $vipService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $properties = $this->propertyRepository->findAll();
        $syncedCount = 0;

        foreach ($properties as $property) {
            $oldVipStatus = $property->isVip();
            $this->vipService->syncVipStatusWithPromotions($property);
            $newVipStatus = $property->isVip();
            
            if ($oldVipStatus !== $newVipStatus) {
                $syncedCount++;
                $status = $newVipStatus ? 'активиран' : 'деактивиран';
                $io->text(sprintf('Имот "%s": VIP статус %s', $property->getTitleBg(), $status));
            }
        }

        if ($syncedCount === 0) {
            $io->success('Всички VIP статуси са в синхрон с промоциите.');
        } else {
            $io->success(sprintf('Успешно синхронизирани %d имота.', $syncedCount));
        }

        return Command::SUCCESS;
    }
}
