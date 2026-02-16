<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS (Railway)
| -------------------------------------------------------------------
*/
$active_group = 'default';
$query_builder = TRUE;

// Configuration Railway
$db['default'] = array(
    'dsn'       => '',
    'hostname'  => getenv('MYSQLHOST') ?: 'trolley.proxy.rlwy.net',
    'username'  => getenv('MYSQLUSER') ?: 'root',
    'password'  => getenv('MYSQLPASSWORD') ?: 'PQgvseAjUOpbmWfnXuKFjLNumsVqjHDQ',
    'database'  => getenv('MYSQLDATABASE') ?: 'railway',
    'port'      => getenv('MYSQLPORT') ?: 56121,
    'dbdriver'  => 'mysqli',
    'dbprefix'  => '',
    'pconnect'  => FALSE,
    'db_debug'  => (ENVIRONMENT !== 'production'),
    'cache_on'  => FALSE,
    'cachedir'  => '',
    'char_set'  => 'utf8mb4',
    'dbcollat'  => 'utf8mb4_unicode_ci',
    'swap_pre'  => '',
    'encrypt'   => FALSE,
    'compress'  => FALSE,
    'stricton'  => FALSE,
    'failover'  => array(),
    'save_queries' => TRUE
);
