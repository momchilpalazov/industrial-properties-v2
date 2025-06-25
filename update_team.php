<?php

require_once __DIR__.'/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

$kernel = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

$em = $container->get('doctrine')->getManager();

// Намираме AboutSettings
$aboutSettings = $em->getRepository(\App\Entity\AboutSettings::class)->find(1);

if ($aboutSettings) {
    // Добавяме английски преводи
    $teamEn = [
        ['name' => 'Ivan Ivanov', 'position' => 'Manager'],
        ['name' => 'Maria Petrova', 'position' => 'Industrial Real Estate Broker'],
        ['name' => 'Georgi Dimitrov', 'position' => 'Industrial Real Estate Broker']
    ];
    
    // Добавяме немски преводи
    $teamDe = [
        ['name' => 'Ivan Ivanov', 'position' => 'Manager'],
        ['name' => 'Maria Petrova', 'position' => 'Gewerbemakler'],
        ['name' => 'Georgi Dimitrov', 'position' => 'Gewerbemakler']
    ];
    
    // Добавяме руски преводи
    $teamRu = [
        ['name' => 'Иван Иванов', 'position' => 'Менеджер'],
        ['name' => 'Мария Петрова', 'position' => 'Брокер коммерческой недвижимости'],
        ['name' => 'Георги Димитров', 'position' => 'Брокер коммерческой недвижимости']
    ];
    
    $aboutSettings->setTeamEn($teamEn);
    $aboutSettings->setTeamDe($teamDe);
    $aboutSettings->setTeamRu($teamRu);
    
    $em->flush();
    
    echo "Successfully updated team translations!\n";
} else {
    echo "AboutSettings record not found!\n";
}
