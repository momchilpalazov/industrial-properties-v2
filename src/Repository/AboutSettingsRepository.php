<?php

namespace App\Repository;

use App\Entity\AboutSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AboutSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AboutSettings::class);
    }

    public function save(AboutSettings $settings): void
    {
        $this->_em->persist($settings);
        $this->_em->flush();
    }

    public function getSettings(): ?AboutSettings
    {
        return $this->findOneBy([]) ?? $this->createDefaultSettings();
    }

    private function createDefaultSettings(): AboutSettings
    {
        $settings = new AboutSettings();
        
        // Заглавна секция
        $settings->setTitleBg('За нас')
            ->setTitleEn('About Us')
            ->setSubtitleBg('Вашият надежден партньор в света на индустриалните имоти')
            ->setSubtitleEn('Your trusted partner in industrial real estate');

        // История секция
        $settings->setContentBg('Industrial Properties е водеща компания в сферата на индустриалните имоти в България. С над 10 години опит на пазара, ние предлагаме професионални услуги в областта на покупко-продажбата и наемането на индустриални имоти. Нашият екип от експерти може да ви предложи най-подходящите решения за вашия бизнес.')
            ->setContentEn('Industrial Properties is a leading company in the industrial real estate sector in Bulgaria. With over 10 years of market experience, we offer professional services in the field of buying, selling and leasing industrial properties. Our team of experts can offer you the most suitable solutions for your business.');

        // Ценности секция
        $settings->setValuesBg([
            'quality' => [
                'title' => 'Качество',
                'description' => 'Стремим се към най-високо качество във всичко, което правим'
            ],
            'reliability' => [
                'title' => 'Надеждност',
                'description' => 'Изграждаме дългосрочни отношения, базирани на доверие'
            ],
            'development' => [
                'title' => 'Развитие',
                'description' => 'Постоянно се развиваме и следим новостите в бранша'
            ]
        ]);
        
        $settings->setValuesEn([
            'quality' => [
                'title' => 'Quality',
                'description' => 'We strive for the highest quality in everything we do'
            ],
            'reliability' => [
                'title' => 'Reliability',
                'description' => 'We build long-term relationships based on trust'
            ],
            'development' => [
                'title' => 'Development',
                'description' => 'We constantly develop and follow industry trends'
            ]
        ]);

        // Екип секция
        $settings->setTeam([
            [
                'name' => 'Иван Иванов',
                'position' => 'Управител',
                'image' => null
            ],
            [
                'name' => 'Мария Петрова',
                'position' => 'Брокер индустриални имоти',
                'image' => null
            ],
            [
                'name' => 'Георги Димитров',
                'position' => 'Брокер индустриални имоти',
                'image' => null
            ]
        ]);

        // Meta данни
        $settings->setMetaTitleBg('За нас | Industrial Properties')
            ->setMetaTitleEn('About Us | Industrial Properties')
            ->setMetaDescriptionBg('Научете повече за Industrial Properties - вашият надежден партньор в индустриалните имоти')
            ->setMetaDescriptionEn('Learn more about Industrial Properties - your trusted partner in industrial real estate');

        $this->save($settings);
        return $settings;
    }
} 