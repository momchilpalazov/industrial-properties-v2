<?php

namespace App\Repository;

use App\Entity\PropertyImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PropertyImage>
 */
class PropertyImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyImage::class);
    }

    public function save(PropertyImage $image, bool $flush = false): void
    {
        $this->getEntityManager()->persist($image);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PropertyImage $image, bool $flush = false): void
    {
        $this->getEntityManager()->remove($image);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findMainImage(int $propertyId): ?PropertyImage
    {
        return $this->createQueryBuilder('i')
            ->where('i.property = :propertyId')
            ->andWhere('i.isMain = :isMain')
            ->setParameter('propertyId', $propertyId)
            ->setParameter('isMain', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function setMainImage(PropertyImage $image): void
    {
        // Първо премахваме main флага от всички снимки на имота
        $this->createQueryBuilder('i')
            ->update()
            ->set('i.isMain', ':isMain')
            ->where('i.property = :propertyId')
            ->setParameter('isMain', false)
            ->setParameter('propertyId', $image->getProperty()->getId())
            ->getQuery()
            ->execute();

        // След това задаваме main флага на избраната снимка
        $image->setIsMain(true);
        $this->save($image, true);
    }

    public function reorderImages(array $order): void
    {
        foreach ($order as $position => $imageId) {
            $this->createQueryBuilder('i')
                ->update()
                ->set('i.position', ':position')
                ->where('i.id = :imageId')
                ->setParameter('position', $position)
                ->setParameter('imageId', $imageId)
                ->getQuery()
                ->execute();
        }
    }
} 