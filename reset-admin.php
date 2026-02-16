<?php
/**
 * Script de réinitialisation du mot de passe admin
 * À exécuter une seule fois puis supprimer
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration Railway directement
$hostname = getenv('MYSQLHOST') ?: 'trolley.proxy.rlwy.net';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: 'PQgvseAjUOpbmWfnXuKFjLNumsVqjHDQ';
$database = getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('MYSQLPORT') ?: 56121;

// Nouveau mot de passe admin
$new_password = 'admin123';

// Hash du mot de passe
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

echo "🔐 Réinitialisation du mot de passe admin\n";
echo "========================================\n\n";
echo "Connexion à: $hostname:$port\n";
echo "Base: $database\n\n";

try {
    // Connexion à la base de données
    $mysqli = new mysqli($hostname, $username, $password, $database, (int)$port);

    if ($mysqli->connect_error) {
        echo "❌ Erreur de connexion: " . $mysqli->connect_error . "\n";
        exit(1);
    }

    echo "✅ Connexion réussie !\n\n";

    // Mise à jour du mot de passe pour l'admin (id=1)
    $query = "UPDATE users SET password = ? WHERE id = 1";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        echo "❌ Erreur prepare: " . $mysqli->error . "\n";
        exit(1);
    }
    
    $stmt->bind_param("s", $hashed_password);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        echo "✅ Mot de passe réinitialisé avec succès !\n";
        echo "   Lignes affectées: $affected\n\n";
        echo "📧 Email: (celui que vous avez entré lors de l'installation)\n";
        echo "🔑 Nouveau mot de passe: admin123\n\n";
        echo "⚠️  IMPORTANT: Supprimez ce fichier après utilisation !\n";
        echo "   Commande: rm /var/www/html/reset-admin.php\n";
    } else {
        echo "❌ Erreur lors de la mise à jour: " . $stmt->error . "\n";
    }

    $stmt->close();
    $mysqli->close();

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
