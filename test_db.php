<?php
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=industrial_properties_v2;port=3306',
        'root',
        'mP8105042183'
    );
    echo "Connection successful!\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch()) {
        echo $row[0] . "\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
