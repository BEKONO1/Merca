<?php
// Script de test de connexion MySQL

$host = getenv('MYSQLHOST') ?: 'trolley.proxy.rlwy.net';
$port = getenv('MYSQLPORT') ?: '56121';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: 'PQgvseAjUOpbmWfnXuKFjLNumsVqjHDQ';
$db   = getenv('MYSQLDATABASE') ?: 'railway';

echo "=== Test de connexion MySQL ===\n\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "User: $user\n";
echo "Database: $db\n\n";

try {
    $mysqli = new mysqli($host, $user, $pass, $db, (int)$port);
    
    if ($mysqli->connect_error) {
        echo "❌ ERREUR: " . $mysqli->connect_error . "\n";
        exit(1);
    }
    
    echo "✅ CONNEXION RÉUSSIE !\n";
    echo "Version MySQL: " . $mysqli->server_info . "\n";
    
    // Test simple
    $result = $mysqli->query("SHOW TABLES");
    if ($result) {
        echo "Tables trouvées: " . $result->num_rows . "\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    exit(1);
}
