<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS (Railway-Ready)
| -------------------------------------------------------------------
| 
| Configuration optimisée pour déploiement sur Railway.
| Supporte deux modes de configuration :
| 
| 1. DATABASE_URL (DSN) - Recommandé pour Railway/Heroku/Cloud
| 2. Variables individuelles (MYSQLHOST, MYSQLUSER, etc.) - Fallback
|
| -------------------------------------------------------------------
*/

$active_group = 'default';
$query_builder = TRUE;

/**
 * Parse DATABASE_URL DSN format
 * Exemple: mysql://user:password@localhost:3306/database
 * Railway fournit automatically cette variable
 */
function parseDatabaseUrl($url) {
    if (empty($url)) {
        return null;
    }

    $parsed = parse_url($url);

    if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
        trigger_error('DATABASE_URL format invalide: ' . $url, E_USER_WARNING);
        return null;
    }

    return array(
        'scheme'   => $parsed['scheme'] ?? 'mysqli',
        'hostname' => $parsed['host'],
        'port'     => $parsed['port'] ?? ($parsed['scheme'] === 'mysql' ? 3306 : 5432),
        'username' => isset($parsed['user']) ? urldecode($parsed['user']) : '',
        'password' => isset($parsed['pass']) ? urldecode($parsed['pass']) : '',
        'database' => ltrim($parsed['path'] ?? '', '/'),
    );
}

/**
 * Récupérer la configuration de la base de données
 */
function getDbConfig() {
    $config = array();

    // 1. PRIORITÉ: DATABASE_URL (Railway, Heroku, etc.)
    $database_url = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? false);

    if ($database_url) {
        $parsed = parseDatabaseUrl($database_url);

        if ($parsed !== null) {
            $config['hostname'] = $parsed['hostname'];
            $config['username'] = $parsed['username'];
            $config['password'] = $parsed['password'];
            $config['database'] = $parsed['database'];
            $config['port']      = $parsed['port'];
            
            // Déterminer le driver basé sur le scheme
            $config['dbdriver'] = in_array($parsed['scheme'], ['mysql', 'mysqli']) ? 'mysqli' : 'pdo';
            
            error_log('[DB] DATABASE_URL utilisée - Mode Railway/Cloud');
            return $config;
        }
    }

    // 2. FALLBACK: Variables individuelles (Local development)
    $hostname = getenv('MYSQLHOST') ?: ($_ENV['MYSQLHOST'] ?? 'localhost');
    $username = getenv('MYSQLUSER') ?: ($_ENV['MYSQLUSER'] ?? 'root');
    $password = getenv('MYSQLPASSWORD') ?: ($_ENV['MYSQLPASSWORD'] ?? '');
    $database = getenv('MYSQLDATABASE') ?: ($_ENV['MYSQLDATABASE'] ?? 'eshop_db');
    $port     = getenv('MYSQLPORT') ?: ($_ENV['MYSQLPORT'] ?? 3306);

    $config['hostname'] = $hostname;
    $config['username'] = $username;
    $config['password'] = $password;
    $config['database'] = $database;
    $config['port']     = (int) $port;
    $config['dbdriver'] = 'mysqli';

    error_log('[DB] Configuration individuelle utilisée - Mode Local');
    return $config;
}

// Récupérer la configuration
$db_config = getDbConfig();

/**
 * Validation de la configuration avant connexion
 */
if (empty($db_config['database'])) {
    $error_msg = 'ERREUR CRITIQUE: Aucune base de données configurée. ' .
                 'Vérifiez DATABASE_URL ou MYSQLDATABASE.';
    error_log($error_msg);
    throw new Exception($error_msg);
}

// Configuration de la base de données
$db['default'] = array(
    // ============ CONNEXION ============
    'dsn'        => '',  // Laissé vide, utilise les paramètres individuels
    'hostname'   => $db_config['hostname'],
    'username'   => $db_config['username'],
    'password'   => $db_config['password'],
    'database'   => $db_config['database'],
    'dbdriver'   => $db_config['dbdriver'],
    'port'       => $db_config['port'],

    // ============ PERFORMANCE ============
    'pconnect'   => FALSE,  // Ne pas utiliser les connexions persistantes en production
    'failover'   => array(),  // Failover (db secondaire optionnelle)
    'save_queries' => (ENVIRONMENT !== 'production'),  // Désactiver en production

    // ============ OPTIMISATIONS ============
    'char_set'   => 'utf8mb4',  // Caractères Unicode complets (emoji friendly)
    'dbcollat'   => 'utf8mb4_unicode_ci',
    'compress'   => FALSE,

    // ============ SÉCURITÉ & DEBUG ============
    'db_debug'   => (ENVIRONMENT !== 'production'),
    'encrypt'    => FALSE,  // À TRUE si SSL requis par Railway/Hosting
    'stricton'   => (ENVIRONMENT !== 'production'),

    // ============ CACHE & PREFIX ============
    'dbprefix'   => '',
    'cache_on'   => FALSE,
    'cachedir'   => STORAGE_PATH . 'db_cache/',
    'swap_pre'   => '',
);

/**
 * Logging de la configuration en mode développement
 */
if (ENVIRONMENT === 'development') {
    error_log('[DB] Configuration appliquée:');
    error_log('  - Host: ' . $db['default']['hostname']);
    error_log('  - Port: ' . $db['default']['port']);
    error_log('  - Database: ' . $db['default']['database']);
    error_log('  - Driver: ' . $db['default']['dbdriver']);
    error_log('  - Charset: ' . $db['default']['char_set']);
}

// Nettoyage
unset($db_config);
