<?php

namespace App\Repository;

use App\Entity\Faq;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FaqRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    public function save(Faq $faq, bool $flush = true): void
    {
        $this->_em->persist($faq);
        
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Faq $faq): void
    {
        $this->_em->remove($faq);
        $this->_em->flush();
    }

    public function findActive(string $locale = 'bg')
    {
        return $this->createQueryBuilder('f')
            ->where('f.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('f.position', 'ASC')
            ->getQuery()
            ->getResult();
    }    public function findByCategory($category, string $locale = 'bg')
    {
        return $this->createQueryBuilder('f')
            ->where('f.category = :category')
            ->andWhere('f.isActive = :active')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->orderBy('f.position', 'ASC')
            ->getQuery()
            ->getResult();
    }    public function reorder(array $positions): void
    {
        foreach ($positions as $id => $position) {
            $this->createQueryBuilder('f')
                ->update()
                ->set('f.position', ':position')                ->where('f.id = :id')
                ->setParameter('position', $position)
                ->setParameter('id', $id)
                ->getQuery()
                ->execute();
        }
    }
}