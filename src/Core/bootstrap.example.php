<?php
/**
 * eShop - Bootstrap Example
 * 
 * Ceci montre comment intégrer le Bootstrap dans votre index.php existant
 * 
 * À utiliser dans: public/index.php ou le point d'entrée principal
 */

// Autoloader Composer (si utilisé)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// ============================================================
// 1. Définir les constantes de base PATH
// ============================================================

define('BASEPATH', __DIR__ . '/../');
define('APPPATH', BASEPATH . 'application/');
define('SRCPATH', BASEPATH . 'src/');
define('STORAGE_PATH', BASEPATH . 'storage/');

// Déterminer l'environnement
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', getenv('ENVIRONMENT') ?: getenv('APP_ENV') ?: 'development');
}

// ============================================================
// 2. CHARGER LE BOOTSTRAP DU NOYAU
// ============================================================

if (file_exists(SRCPATH . 'Core/Bootstrap.php')) {
    require_once SRCPATH . 'Core/Bootstrap.php';
    
    error_log('[INDEX] Bootstrap chargé depuis src/Core/Bootstrap.php');
}

// ============================================================
// 3. Charger CodeIgniter 3
// ============================================================

const CI_VERSION = '3.1.13';

// Votre code d'initialisation CodeIgniter 3 existant
// Les logs seront maintenant capturés correctement
// ...

// Exemple CodeIgniter 3 bootstrap:
// require_once BASEPATH . 'indices/ci_bootstrap.php';

echo "Bootstrap intégré avec succès !";
echo "<br>Environnement: " . ENVIRONMENT;
echo "<br>Logging: " . (defined('USE_STDERR_LOGGING') && USE_STDERR_LOGGING ? 'stderr' : 'fichiers');
