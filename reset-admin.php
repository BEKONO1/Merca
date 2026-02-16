<?php
/**
 * Script de réinitialisation du mot de passe admin
 * À exécuter une seule fois puis supprimer
 */

// Configuration de la base de données (sera remplie automatiquement)
require_once __DIR__ . '/application/config/database.php';

// Nouveau mot de passe admin
$new_password = 'admin123';

// Hash du mot de passe
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

echo "🔐 Réinitialisation du mot de passe admin\n";
echo "========================================\n\n";

try {
    // Connexion à la base de données
    $mysqli = new mysqli(
        $db['default']['hostname'],
        $db['default']['username'],
        $db['default']['password'],
        $db['default']['database'],
        $db['default']['port'] ?? 3306
    );

    if ($mysqli->connect_error) {
        echo "❌ Erreur de connexion: " . $mysqli->connect_error . "\n";
        exit(1);
    }

    // Mise à jour du mot de passe pour l'admin (id=1)
    $query = "UPDATE users SET password = ? WHERE id = 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $hashed_password);
    
    if ($stmt->execute()) {
        echo "✅ Mot de passe réinitialisé avec succès !\n\n";
        echo "📧 Email: (celui que vous avez entré lors de l'installation)\n";
        echo "🔑 Nouveau mot de passe: admin123\n\n";
        echo "⚠️  IMPORTANT: Supprimez ce fichier après utilisation !\n";
        echo "   Commande: rm reset-admin.php\n";
    } else {
        echo "❌ Erreur lors de la mise à jour: " . $stmt->error . "\n";
    }

    $stmt->close();
    $mysqli->close();

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
