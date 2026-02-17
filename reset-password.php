<?php
/**
 * Réinitialisation du mot de passe admin - Script simplifié
 * Exécutez ce script une seule fois puis supprimez-le
 */

header('Content-Type: text/plain');

echo "🔐 Réinitialisation du mot de passe admin\n";
echo "========================================\n\n";

// Récupérer les variables d'environnement Railway (avec valeurs par défaut)
$host = getenv('MYSQLHOST') ?: 'trolley.proxy.rlwy.net';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: 'PQgvseAjUOpbmWfnXuKFjLNumsVqjHDQ';
$db   = getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('MYSQLPORT') ?: '56121';

// Afficher la configuration (sans le mot de passe)
echo "Configuration:\n";
echo "  Host: $host\n";
echo "  Port: $port\n";
echo "  User: $user\n";
echo "  Database: $db\n\n";

// Nouveau mot de passe
$new_password = 'admin123';
$hashed = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    // Connexion
    $mysqli = new mysqli($host, $user, $pass, $db, (int)$port);
    
    if ($mysqli->connect_error) {
        echo "❌ ERREUR: " . $mysqli->connect_error . "\n";
        exit;
    }
    
    echo "✅ Connexion réussie!\n\n";
    
    // Vérifier l'admin existant
    $result = $mysqli->query("SELECT id, email, mobile FROM users WHERE id = 1");
    if ($result && $row = $result->fetch_assoc()) {
        echo "Admin trouvé:\n";
        echo "  ID: " . $row['id'] . "\n";
        echo "  Email: " . $row['email'] . "\n";
        echo "  Mobile: " . $row['mobile'] . "\n\n";
    }
    
    // Mettre à jour le mot de passe
    $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = 1");
    $stmt->bind_param("s", $hashed);
    
    if ($stmt->execute()) {
        echo "✅ MOT DE PASSE RÉINITIALISÉ!\n\n";
        echo "📧 Email: admin@gmail.com\n";
        echo "🔑 Nouveau mot de passe: admin123\n\n";
        echo "⚠️  IMPORTANT: Supprimez ce fichier maintenant!\n";
        echo "   Commande: rm /var/www/html/reset-password.php\n";
    } else {
        echo "❌ Erreur: " . $stmt->error . "\n";
    }
    
    $stmt->close();
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
