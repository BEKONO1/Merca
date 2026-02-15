<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Logging Configuration
 * 
 * Configuration du système de logs eShop
 * Supporte deux modes:
 * 1. Fichiers (développement)
 * 2. stderr (production/Railway)
 */

// ============================================================
// LOG THRESHOLD
// ============================================================
// 0 = Désactivé
// 1 = Erreurs uniquement (production)
// 2 = Debugging
// 3 = Infos
// 4 = Tout
$config['log_threshold'] = (ENVIRONMENT === 'production') ? 1 : 3;

// ============================================================
// LOG PATH
// ============================================================
// Chemin du répertoire logs (fichier uniquement)
// Vide = utilise application/logs/ par défaut
$config['log_path'] = '';

// ============================================================
// LOG FILE EXTENSION
// ============================================================
$config['log_file_extension'] = 'php';

// ============================================================
// LOG FILE PERMISSIONS
// ============================================================
// Permissions octal pour les nouveaux fichiers logs
$config['log_file_permissions'] = 0644;

// ============================================================
// LOG DATE FORMAT
// ============================================================
// Format date pour les logs
$config['log_date_format'] = 'Y-m-d H:i:s';

// ============================================================
// UTILISER STDERR (Automatique pour Railway)
// ============================================================
// 
// Automatiquement activé si:
// - ENVIRONMENT=production
// - RAILWAY_ENVIRONMENT_NAME défini (variables Railway)
// - LOG_TO_STDERR=true
//
// Pour forcer stderr en dev: getenv('LOG_TO_STDERR');
// Pour forcer fichiers: getenv('LOG_TO_FILE');
//

$use_stderr_env = getenv('LOG_TO_STDERR');
$use_file_env = getenv('LOG_TO_FILE');

if ($use_stderr_env === 'true' || $use_stderr_env === '1') {
    // Force stderr
    define('USE_STDERR_LOGGING', true);
} elseif ($use_file_env === 'true' || $use_file_env === '1') {
    // Force fichiers
    define('USE_STDERR_LOGGING', false);
} else {
    // Auto-détection
    $environment = getenv('ENVIRONMENT') ?: getenv('APP_ENV') ?: ENVIRONMENT;
    $is_railway = getenv('RAILWAY_ENVIRONMENT_NAME') !== false;
    $is_docker = getenv('DOCKER_ENV') !== false;
    $is_production = in_array($environment, ['production', 'staging']);
    
    define('USE_STDERR_LOGGING', $is_production || $is_railway || $is_docker);
}

// ============================================================
// LOG OUTPUT STREAMS (Informational)
// ============================================================
// Ceci configure le système pour utiliser la classe Logger personnalisée
// 
// Logs de sortie:
// - Fichiers: application/logs/log-YYYY-MM-DD.php
// - Stderr: php://stderr (capturable par Railway)
// 
// Pour désactiver les logs: 
//   $config['log_threshold'] = 0;
// 
// Pour améliorer les performances:
//   $config['log_threshold'] = 1;  (production)
//

if (USE_STDERR_LOGGING) {
    // Production/Railway: stderr uniquement
    // Pas de création de fichiers
    
    // Message pour le débogage (visibles dans Rails logs si needed)
    // error_log('[LOG] stderr logging actif - Railway/Docker');
} else {
    // Développement: fichiers locaux
    // error_log('[LOG] fichier logging actif - Développement');
}

/* End of file logging.php */
