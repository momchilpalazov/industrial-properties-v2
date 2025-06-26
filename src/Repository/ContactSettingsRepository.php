<?php

namespace App\Repository;

use App\Entity\ContactSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactSettings>
 */
class ContactSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactSettings::class);
    }

    /**
     * Get contact settings (there should be only one record)
     */
    public function getSettings(): ?ContactSettings
    {
        return $this->findOneBy([]) ?: new ContactSettings();
    }

    /**
     * Save contact settings
     */
    public function save(ContactSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove contact settings
     */
    public function remove(ContactSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
