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

    public function countByMonth(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT 
                YEAR(i.created_at) as year,
                MONTH(i.created_at) as month,
                COUNT(i.id) as count
            FROM property_inquiries i
            GROUP BY YEAR(i.created_at), MONTH(i.created_at)
            ORDER BY year ASC, month ASC
        ';

        $stmt = $conn->executeQuery($sql);
        return $stmt->fetchAllAssociative();
    }

    public function findTopLocations(int $limit = 5): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT 
                p.location_bg as location,
                COUNT(i.id) as inquiryCount
            FROM property_inquiries i
            JOIN properties p ON i.property_id = p.id
            GROUP BY p.location_bg
            ORDER BY inquiryCount DESC
            LIMIT ' . $limit;

        $stmt = $conn->executeQuery($sql);
        return $stmt->fetchAllAssociative();
    }

    public function calculateAverageResponseTime(): ?float
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT AVG(TIMESTAMPDIFF(HOUR, i.created_at, i.responded_at)) as avgHours
            FROM property_inquiries i
            WHERE i.responded_at IS NOT NULL
        ';

        $result = $conn->executeQuery($sql)->fetchOne();
        return $result ? round($result, 1) : null;
    }
} 