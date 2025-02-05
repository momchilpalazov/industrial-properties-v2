<?php

namespace App\Repository;

use App\Entity\FooterSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FooterSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FooterSettings::class);
    }

    public function save(FooterSettings $settings): void
    {
        $this->_em->persist($settings);
        $this->_em->flush();
    }

    public function getSettings(): ?FooterSettings
    {
        return $this->findOneBy([]) ?? $this->createDefaultSettings();
    }

    private function createDefaultSettings(): FooterSettings
    {
        $settings = new FooterSettings();
        $settings->setDescription('Вашият надежден партньор в индустриалните имоти')
            ->setAddress('София, България')
            ->setPhone('+359 2 123 4567')
            ->setEmail('office@industrial-properties.bg')
            ->setSocialLinks([
                'facebook' => 'https://facebook.com/',
                'linkedin' => 'https://linkedin.com/',
                'instagram' => 'https://instagram.com/',
                'youtube' => 'https://youtube.com/'
            ]);

        $this->save($settings);
        return $settings;
    }
} 