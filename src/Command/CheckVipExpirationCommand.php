<?php

namespace App\Command;

use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-vip-expiration',
    description: 'Проверява и премахва изтекли VIP статуси на имоти',
)]
class CheckVipExpirationCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PropertyRepository $propertyRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $now = new \DateTimeImmutable();
        $expiredProperties = $this->propertyRepository->createQueryBuilder('p')
            ->where('p.isVip = :isVip')
            ->andWhere('p.vipExpiration < :now')
            ->setParameter('isVip', true)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        if (empty($expiredProperties)) {
            $io->success('Няма имоти с изтекъл VIP статус.');
            return Command::SUCCESS;
        }

        foreach ($expiredProperties as $property) {
            $property->setIsVip(false);
            $property->setVipExpiration(null);
            $io->text(sprintf('Премахнат VIP статус на имот: %s', $property->getTitleBg()));
        }

        $this->entityManager->flush();
        $io->success(sprintf('Успешно премахнат VIP статус на %d имота.', count($expiredProperties)));

        return Command::SUCCESS;
    }
} 