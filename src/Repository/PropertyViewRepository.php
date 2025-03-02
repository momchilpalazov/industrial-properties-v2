<?php

namespace App\Repository;

use App\Entity\PropertyView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PropertyViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyView::class);
    }

    public function findMostViewedProperties(int $limit = 5): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT 
                p.id,
                p.title_bg as title,
                COUNT(v.id) as viewCount
            FROM properties p
            JOIN property_views v ON v.property_id = p.id
            GROUP BY p.id, p.title_bg
            ORDER BY viewCount DESC
            LIMIT ' . $limit;

        $stmt = $conn->executeQuery($sql);
        return $stmt->fetchAllAssociative();
    }

    public function addView(int $propertyId, string $ipAddress, string $userAgent): void
    {
        $view = new PropertyView();
        $view->setProperty($this->getEntityManager()->getReference('App:Property', $propertyId));
        $view->setIpAddress($ipAddress);
        $view->setUserAgent($userAgent);

        $this->getEntityManager()->persist($view);
        $this->getEntityManager()->flush();
    }
} 