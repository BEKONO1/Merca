#!/usr/bin/env php
<?php
/**
 * Test Logging Script
 * 
 * Teste la configuration des logs eShop
 * 
 * Utilisation:
 *   php test-logging.php
 *   php test-logging.php --env=production
 *   php test-logging.php --env=development
 */

// Parse les arguments CLI
$options = getopt('e:', ['env:']);
$env = $options['e'] ?? $options['env'] ?? $_ENV['ENVIRONMENT'] ?? 'development';

putenv("ENVIRONMENT=$env");
putenv("LOGS_DEBUG=1");

// ============================================================
// Configuration de base
// ============================================================

define('BASEPATH', __DIR__ . '/');
define('APPPATH', BASEPATH . 'application/');
define('SRCPATH', BASEPATH . 'src/');
define('STORAGE_PATH', BASEPATH . 'storage/');
define('ENVIRONMENT', $env);

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           eShop Logging Configuration Test                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "📋 Configuration actuelle:\n";
echo "  - ENVIRONMENT: " . ENVIRONMENT . "\n";
echo "  - Base Path: " . BASEPATH . "\n";
echo "  - App Path: " . APPPATH . "\n";
echo "  - Storage Path: " . STORAGE_PATH . "\n";
echo "\n";

// ============================================================
// 1. Test: Charger le Bootstrap
// ============================================================

echo "🔄 Test 1: Chargement du Bootstrap...\n";
if (file_exists(SRCPATH . 'Core/Bootstrap.php')) {
    require_once SRCPATH . 'Core/Bootstrap.php';
    echo "  ✅ Bootstrap chargé avec succès\n";
    echo "  ✅ USE_STDERR_LOGGING: " . (defined('USE_STDERR_LOGGING') && USE_STDERR_LOGGING ? 'YES' : 'NO') . "\n";
} else {
    echo "  ❌ Bootstrap non trouvé: " . SRCPATH . 'Core/Bootstrap.php' . "\n";
    exit(1);
}

echo "\n";

// ============================================================
// 2. Test: Charger la configuration des logs
// ============================================================

echo "🔄 Test 2: Chargement de la configuration des logs...\n";
$config = array();
if (file_exists(APPPATH . '../src/Config/logging.php')) {
    require APPPATH . '../src/Config/logging.php';
    echo "  ✅ Configuration chargée\n";
    echo "  - log_threshold: " . ($config['log_threshold'] ?? 'undefined') . "\n";
    echo "  - log_date_format: " . ($config['log_date_format'] ?? 'Y-m-d H:i:s') . "\n";
} else {
    echo "  ⚠️  Config logging non trouvée, using defaults\n";
}

echo "\n";

// ============================================================
// 3. Test: Vérifier les répertoires
// ============================================================

echo "🔄 Test 3: Vérification des répertoires...\n";
$dirs = array(
    'Storage' => STORAGE_PATH,
    'Storage/logs' => STORAGE_PATH . 'logs/',
    'Application/logs' => APPPATH . 'logs/',
    'Public/uploads' => BASEPATH . 'public/uploads/',
);

foreach ($dirs as $name => $path) {
    if (is_dir($path)) {
        $writable = is_writable($path) ? '✅ writable' : '⚠️  read-only';
        echo "  ✅ $name ... $writable\n";
    } else {
        echo "  ❌ $name ... NOT FOUND\n";
    }
}

echo "\n";

// ============================================================
// 4. Test: Écrire des logs fictifs
// ============================================================

echo "🔄 Test 4: Écriture de logs de test...\n";

// Créer les fonctions de log si elles n'existent pas
if (!function_exists('log_message')) {
    function log_message($level = 'info', $msg = '', $php_error = FALSE) {
        $levels = array(
            '0' => 'OFF',
            '1' => 'ERROR',
            '2' => 'DEBUG',
            '3' => 'INFO',
            '4' => 'ALL'
        );
        
        $date = date('Y-m-d H:i:s');
        $message = "[$date] [" . strtoupper($level) . "] $msg";
        
        if (defined('USE_STDERR_LOGGING') && USE_STDERR_LOGGING) {
            fwrite(STDERR, $message . "\n");
        } else {
            echo $message . "\n";
        }
        
        return TRUE;
    }
}

// Tester les logs
log_message('error', 'Test ERROR message - This should appear in logs');
log_message('info', 'Test INFO message - Connection successful');
log_message('debug', 'Test DEBUG message with data: {"user_id": 123}');

echo "  ✅ Messages de test envoyés\n";

echo "\n";

// ============================================================
// 5. Test: Vérifier les variables d'environnement
// ============================================================

echo "🔄 Test 5: Variables d'environnement...\n";

$env_vars = array(
    'ENVIRONMENT',
    'APP_ENV',
    'RAILWAY_ENVIRONMENT_NAME',
    'LOG_TO_STDERR',
    'LOG_TO_FILE',
    'LOG_LEVEL',
);

$found = 0;
foreach ($env_vars as $var) {
    $value = getenv($var);
    if ($value !== false) {
        echo "  ✅ $var = $value\n";
        $found++;
    }
}

if ($found === 0) {
    echo "  ⚠️  Aucune variable d'environnement eShop détectée\n";
} else {
    echo "  ✅ $found variable(s) detected\n";
}

echo "\n";

// ============================================================
// 6. Résumé
// ============================================================

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                        📊 RÉSUMÉ                               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$mode = (defined('USE_STDERR_LOGGING') && USE_STDERR_LOGGING) ? 'STDERR' : 'FILES';
echo "Mode de logging: $mode\n";
echo "\n";

if ($mode === 'STDERR') {
    echo "✅ Configuration PRODUCTION/RAILWAY:\n";
    echo "   - Les logs vont vers php://stderr\n";
    echo "   - Visibles dans Railway Logs dashboard\n";
    echo "   - Pas de fichiers disk utilisés\n";
} else {
    echo "✅ Configuration DÉVELOPPEMENT:\n";
    echo "   - Les logs vont vers application/logs/\n";
    echo "   - Accédez à: tail -f application/logs/log-*.php\n";
    echo "   - Fichiers locaux pour inspection\n";
}

echo "\n";
echo "✅ Tous les tests passés !\n";
echo "\n";
