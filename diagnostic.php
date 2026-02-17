<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h1>🔍 Diagnostic du site</h1>";

// Vérifier les fichiers CSS
echo "<h2>Fichiers CSS présents :</h2>";
$css_files = [
    'assets/admin/css/bootstrap.min.css',
    'assets/common/css/style.min.css',
    'assets/front_end/modern/css/bootstrap.min.css'
];

foreach ($css_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file - " . filesize($file) . " bytes<br>";
    } else {
        echo "❌ $file - MANQUANT<br>";
    }
}

// Vérifier les dossiers
echo "<h2>Dossiers :</h2>";
$dirs = ['assets', 'uploads', 'install'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $count = count(glob($dir . '/*'));
        echo "✅ $dir/ - $count éléments<br>";
    } else {
        echo "❌ $dir/ - MANQUANT<br>";
    }
}

// Liste des fichiers à la racine
echo "<h2>Fichiers à la racine :</h2>";
$files = glob('*');
echo implode(', ', array_slice($files, 0, 20)) . "...<br>";

// Test de connexion DB
echo "<h2>Test connexion DB :</h2>";
try {
    $host = 'trolley.proxy.rlwy.net';
    $user = 'root';
    $pass = 'PQgvseAjUOpbmWfnXuKFjLNumsVqjHDQ';
    $db   = 'railway';
    $port = 56121;
    
    $mysqli = @new mysqli($host, $user, $pass, $db, $port);
    if ($mysqli->connect_error) {
        echo "❌ Erreur: " . $mysqli->connect_error . "<br>";
    } else {
        echo "✅ Connexion réussie!<br>";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
}
