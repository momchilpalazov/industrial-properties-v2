<?php

namespace App\Repository;

use App\Entity\PropertyInquiry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PropertyInquiryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyInquiry::class);
    }

    public function save(PropertyInquiry $inquiry): void
    {
        $this->_em->persist($inquiry);
        $this->_em->flush();
    }

    public function remove(PropertyInquiry $inquiry): void
    {
        $this->_em->remove($inquiry);
        $this->_em->flush();
    }

    public function findUnread(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.isRead = :isRead')
            ->setParameter('isRead', false)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 