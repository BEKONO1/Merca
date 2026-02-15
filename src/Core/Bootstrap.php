<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * eShop Bootstrap Core
 * 
 * Initialise les systèmes principaux de l'application
 * Appelé au startup pour chaque requête
 * 
 * @package     eShop
 * @category    Core
 */

// ============================================================
// 1. CONFIGURATION ENVIRONNEMENT
// ============================================================

// Définir les chemins constants S'ils ne le sont pas
if (!defined('APPPATH')) {
    define('APPPATH', BASEPATH . 'application/');
}
if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', BASEPATH . 'storage/');
}

// ============================================================
// 2. CHARGER LES VARIABLES D'ENVIRONNEMENT (.env)
// ============================================================

// Utiliser phpdotenv si disponible (Composer)
if (file_exists(BASEPATH . 'vendor/autoload.php')) {
    require_once BASEPATH . 'vendor/autoload.php';
    
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = \Dotenv\Dotenv::createImmutable(BASEPATH);
            $dotenv->safeLoad();
            error_log('[BOOTSTRAP] .env loaded via phpdotenv');
        } catch (Exception $e) {
            error_log('[BOOTSTRAP] Warning: Failed to load .env with phpdotenv - ' . $e->getMessage());
        }
    }
} else {
    // Fallback: charger manuellement le .env si Composer n'est pas disponible
    if (file_exists(BASEPATH . '.env')) {
        $env_file = BASEPATH . '.env';
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ((array) $lines as $line) {
            // Ignorer les commentaires
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parser la variable
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Retirer les quotes
                $value = trim($value, '"\'');
                
                // Définir comme variable d'environnement SEULEMENT si pas déjà définie
                if (!getenv($name)) {
                    putenv("$name=$value");
                    $_ENV[$name] = $value;
                }
            }
        }
        error_log('[BOOTSTRAP] .env loaded manually (phpdotenv not available)');
    }
}

// ============================================================
// 3. INITIALISER LE SYSTÈME DE LOGS
// ============================================================

// Charger la configuration des logs
$log_config = array();
if (file_exists(APPPATH . '../src/Config/logging.php')) {
    require APPPATH . '../src/Config/logging.php';
    // $config est disponible maintenant
}

// Déterminer le type de logging
$use_stderr = defined('USE_STDERR_LOGGING') ? USE_STDERR_LOGGING : false;

if ($use_stderr) {
    // Production: Tous les logs vers stderr
    ini_set('error_log', 'php://stderr');
    ini_set('log_errors', '1');
    
    // Rediriger error_reporting vers stderr aussi
    $error_handler = function ($errno, $errstr, $errfile, $errline) {
        $error_types = array(
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        );
        
        $type = isset($error_types[$errno]) ? $error_types[$errno] : 'UNKNOWN';
        $message = sprintf(
            '[%s] %s in %s on line %d',
            date('Y-m-d H:i:s'),
            $type,
            $errfile,
            $errline
        );
        
        error_log($message);
        error_log("  Message: $errstr");
        
        return false;
    };
    
    if (ENVIRONMENT === 'production') {
        set_error_handler($error_handler, E_ALL);
    }
    
    error_log('[BOOTSTRAP] Logging vers stderr activé (Production/Railway)');
} else {
    // Développement: Fichiers locaux
    $log_path = APPPATH . 'logs/';
    
    if (!is_dir($log_path)) {
        @mkdir($log_path, 0755, true);
    }
    
    if (is_writable($log_path)) {
        ini_set('error_log', $log_path . 'php-error.log');
        error_log('[BOOTSTRAP] Logging vers fichiers activé (Développement)');
    }
}

// ============================================================
// 3. CONFIGURATION SÉCURITÉ
// ============================================================

// XSS Protection
ini_set('xss_audit', '0');  // Désactiver XSS audit (codeigniter le gère)

// Désactiver l'affichage des erreurs en production
if (ENVIRONMENT === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);  // Log everything, but don't display
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// ============================================================
// 4. CONFIGURATION DE SESSION
// ============================================================

// En production/HTTPS, forcer les options de sécurité
if (ENVIRONMENT === 'production' && isset($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
}

// ============================================================
// 5. HEADERS DE SÉCURITÉ (Production)
// ============================================================

if (ENVIRONMENT === 'production' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Forcer HTTPS si disponible
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 
        $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// ============================================================
// 6. INFORMATIONS DE DÉBOGAGE
// ============================================================

if (ENVIRONMENT === 'development') {
    error_log('');
    error_log('========================================');
    error_log('eShop Bootstrap - Development Mode');
    error_log('========================================');
    error_log('Time: ' . date('Y-m-d H:i:s'));
    error_log('Base Path: ' . BASEPATH);
    error_log('App Path: ' . APPPATH);
    error_log('Environment: ' . ENVIRONMENT);
    error_log('USE_STDERR_LOGGING: ' . ($use_stderr ? 'YES' : 'NO'));
    error_log('PHP Version: ' . phpversion());
    error_log('========================================');
    error_log('');
}

/* End of file Bootstrap.php */
