<?php

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    public function findActivePromotions(string $type = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.isPaid = :isPaid')
            ->andWhere('p.startDate <= :now')
            ->andWhere('p.endDate >= :now')
            ->setParameter('isPaid', true)
            ->setParameter('now', new \DateTime());

        if ($type) {
            $qb->andWhere('p.type = :type')
               ->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }

    public function findExpiredPromotions(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.endDate < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingPromotions(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.startDate > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function findUnpaidPromotions(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isPaid = :isPaid')
            ->setParameter('isPaid', false)
            ->getQuery()
            ->getResult();
    }
} 