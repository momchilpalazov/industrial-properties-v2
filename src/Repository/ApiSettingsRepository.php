<?php

namespace App\Repository;

use App\Entity\ApiSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ApiSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiSettings::class);
    }

    public function save(ApiSettings $settings): void
    {
        $this->_em->persist($settings);
        $this->_em->flush();
    }

    public function getSettings(): ?ApiSettings
    {
        return $this->findOneBy([]) ?? $this->createDefaultSettings();
    }

    private function createDefaultSettings(): ApiSettings
    {
        $settings = new ApiSettings();
        $this->save($settings);
        return $settings;
    }
} 