#!/usr/bin/env php
<?php

use App\Entity\AboutSettings;
use Doctrine\DBAL\DriverManager;

// Include the Composer autoloader
require __DIR__ . '/vendor/autoload.php';

// Database connection parameters from .env
$connectionParams = [
    'dbname' => 'industrial_properties_v2',
    'user' => 'root',
    'password' => '',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
];

$conn = DriverManager::getConnection($connectionParams);

// English team data
$teamEn = [
    ['name' => 'Ivan Ivanov', 'position' => 'Manager'],
    ['name' => 'Maria Petrova', 'position' => 'Industrial Real Estate Broker'],
    ['name' => 'Georgi Dimitrov', 'position' => 'Industrial Real Estate Broker']
];

// German team data
$teamDe = [
    ['name' => 'Ivan Ivanov', 'position' => 'Manager'],
    ['name' => 'Maria Petrova', 'position' => 'Gewerbemakler'],
    ['name' => 'Georgi Dimitrov', 'position' => 'Gewerbemakler']
];

// Russian team data
$teamRu = [
    ['name' => 'Иван Иванов', 'position' => 'Менеджер'],
    ['name' => 'Мария Петрова', 'position' => 'Брокер коммерческой недвижимости'],
    ['name' => 'Георги Димитров', 'position' => 'Брокер коммерческой недвижимости']
];

// Update the database
$sql = "UPDATE about_settings SET team_en = ?, team_de = ?, team_ru = ? WHERE id = 1";
$stmt = $conn->prepare($sql);
$stmt->executeStatement([
    json_encode($teamEn),
    json_encode($teamDe),
    json_encode($teamRu)
]);

echo "Team translations updated successfully!\n";
