<?php

require_once 'vendor/autoload.php';

use App\Kernel;
use App\Entity\AboutSettings;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load('.env');

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

$entityManager = $container->get('doctrine')->getManager();
$aboutRepo = $entityManager->getRepository(AboutSettings::class);

$settings = $aboutRepo->find(1);

if ($settings) {
    // Добавяме тестови данни за EN
    $teamEn = [
        ['name' => 'Ivan Ivanov', 'position' => 'Manager'],
        ['name' => 'Maria Petrova', 'position' => 'Industrial Real Estate Broker'],
        ['name' => 'Georgi Dimitrov', 'position' => 'Industrial Real Estate Broker']
    ];
    
    // Добавяме тестови данни за DE
    $teamDe = [
        ['name' => 'Ivan Ivanov', 'position' => 'Geschäftsführer'],
        ['name' => 'Maria Petrova', 'position' => 'Industrieimmobilien-Makler'],
        ['name' => 'Georgi Dimitrov', 'position' => 'Industrieimmobilien-Makler']
    ];
    
    // Добавяме тестови данни за RU
    $teamRu = [
        ['name' => 'Иван Иванов', 'position' => 'Менеджер'],
        ['name' => 'Мария Петрова', 'position' => 'Брокер промышленной недвижимости'],
        ['name' => 'Георгий Димитров', 'position' => 'Брокер промышленной недвижимости']
    ];
    
    $settings->setTeamEn($teamEn);
    $settings->setTeamDe($teamDe);
    $settings->setTeamRu($teamRu);
    
    $entityManager->flush();
    
    echo "Данните за екипа са добавени успешно за всички езици!\n";
} else {
    echo "Няма намерени настройки!\n";
}
