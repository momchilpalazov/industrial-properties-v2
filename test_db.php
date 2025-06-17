<?php
// Включваме дебъг информация
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Започваме опит за свързване с базата данни...\n";

try {
    echo "Опитвам се да създам PDO обект...\n";
    
    // Дефиниция на параметри
    $host = 'localhost';
    $db   = 'industrial_properties_v2';
    $user = 'root';
    $pass = 'mP8105042183@';
    $port = 3306;
    $charset = 'utf8mb4';
    
    echo "Настройки: host=$host, db=$db, user=$user, pass=******, port=$port\n";
    
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    echo "Опитвам се да се свържа с DSN: $dsn\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "Връзката е успешна!\n";
    
    try {
        echo "Опитвам се да покажа таблиците...\n";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "Намерени таблици: " . count($tables) . "\n";
            foreach ($tables as $table) {
                echo "- " . $table . "\n";
            }
        } else {
            echo "Няма намерени таблици в базата данни.\n";
        }
    } catch(PDOException $tableError) {
        echo "Грешка при опит да се покажат таблиците: " . $tableError->getMessage() . "\n";
    }
    
    echo "Край на скрипта.\n";
} catch(PDOException $e) {
    echo "Грешка при свързване с базата данни: " . $e->getMessage() . "\n";
}

echo "Скриптът завърши изпълнение.\n";
