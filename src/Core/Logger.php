<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * eShop Logger Class
 * 
 * Système de logging optimisé pour:
 * - Développement local: Fichiers application/logs/
 * - Production (Railway): php://stderr
 * 
 * @package     eShop
 * @category    Core
 */
class Logger
{
    /**
     * Log threshold levels
     * 0 = Disables logging
     * 1 = Error Messages (for live apps)
     * 2 = Debug Messages
     * 3 = Informational Messages
     * 4 = All Messages
     */
    protected $threshold = 1;

    /**
     * Log path (file-based only)
     */
    protected $log_path = '';

    /**
     * Log file extension
     */
    protected $log_file_ext = 'php';

    /**
     * Log file permissions
     */
    protected $file_permissions = 0644;

    /**
     * Date format for logs
     */
    protected $date_format = 'Y-m-d H:i:s';

    /**
     * Whether to use stderr (production)
     */
    protected $use_stderr = false;

    /**
     * Levels
     */
    protected $levels = array(
        '0' => 'OFF',
        '1' => 'ERROR',
        '2' => 'DEBUG',
        '3' => 'INFO',
        '4' => 'ALL'
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->log_path = APPPATH . 'logs/';
        $this->date_format = config_item('log_date_format');
        $this->log_file_ext = config_item('log_file_extension');
        $this->threshold = config_item('log_threshold');
        $this->file_permissions = config_item('log_file_permissions');

        // Détecter si on doit utiliser stderr (production + Railway)
        $this->use_stderr = $this->shouldUseStderr();

        // Créer le répertoire de logs s'il n'existe pas
        if (!$this->use_stderr && !is_dir($this->log_path)) {
            @mkdir($this->log_path, 0755, true);
        }

        log_message('info', 'Logger initialized - stderr: ' . ($this->use_stderr ? 'YES' : 'NO'));
    }

    /**
     * Déterminer s'il faut utiliser stderr
     */
    protected function shouldUseStderr()
    {
        // Production + Railway = stderr
        $environment = getenv('ENVIRONMENT') ?: getenv('APP_ENV') ?: ENVIRONMENT;
        
        // Utiliser stderr pour:
        // - Production (ENVIRONMENT=production)
        // - Railway (détecte par absence de log file ou par variable)
        // - Docker (marée recommandée)
        $use_container_logs = in_array($environment, ['production', 'staging']) ||
                              getenv('RAILWAY_ENVIRONMENT_NAME') !== false ||
                              getenv('DOCKER_ENV') !== false ||
                              getenv('LOG_TO_STDERR') === 'true';

        return (bool) $use_container_logs;
    }

    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @param   string $level   Log level
     * @param   string $msg     Log message
     * @param   bool $php_error Is this a PHP error message?
     * @return  bool
     */
    public function write_log($level = 'info', $msg = '', $php_error = false)
    {
        // Valider le threshold
        if ($this->threshold === 0) {
            return false;
        }

        // Mapper le level à un numéro
        $level_num = $this->getLevelNumber($level);
        
        // Vérifier si ce level doit être loggé
        if ($level_num > $this->threshold) {
            return false;
        }

        // Formater le message
        $log_message = $this->formatMessage($level, $msg);

        if ($this->use_stderr) {
            return $this->writeToStderr($log_message);
        } else {
            return $this->writeToFile($level, $log_message);
        }
    }

    /**
     * Écrire vers stderr (pour containerization)
     */
    protected function writeToStderr($message)
    {
        $stderr = fopen('php://stderr', 'a');

        if (is_resource($stderr)) {
            fwrite($stderr, $message . "\n");
            fclose($stderr);
            return true;
        }

        return false;
    }

    /**
     * Écrire vers un fichier
     */
    protected function writeToFile($level, $message)
    {
        $filepath = $this->log_path . 'log-' . date('Y-m-d') . '.' . $this->log_file_ext;

        // Vérifier les permissions du répertoire
        if (!is_dir($this->log_path)) {
            @mkdir($this->log_path, 0755, true);
        }

        if (!is_writable($this->log_path)) {
            return false;
        }

        // Ajouter au fichier
        $message .= "\n";

        if (!file_exists($filepath)) {
            // Créer le fichier avec les bons permissions
            $file = fopen($filepath, 'w');
            if (is_resource($file)) {
                fwrite($file, $message);
                fclose($file);
                @chmod($filepath, $this->file_permissions);
                return true;
            }
            return false;
        } else if (is_writable($filepath)) {
            if ($fp = fopen($filepath, 'a')) {
                flock($fp, LOCK_EX);
                fwrite($fp, $message);
                flock($fp, LOCK_UN);
                fclose($fp);
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Mapper level string vers numéro
     */
    protected function getLevelNumber($level)
    {
        $level = strtoupper($level);

        // Mapper les levels standards
        $level_map = array(
            'ERROR'   => 1,
            'DEBUG'   => 2,
            'INFO'    => 3,
            'WARNING' => 1,
            'WARN'    => 1,
            'NOTICE'  => 3,
            'ALL'     => 4
        );

        return isset($level_map[$level]) ? $level_map[$level] : 1;
    }

    /**
     * Formater le message de log
     */
    protected function formatMessage($level, $msg)
    {
        $level = strtoupper($level);
        $date = date($this->date_format);

        // Format: [2026-02-15 10:30:45] ERROR - Message details
        return sprintf(
            '[%s] %s - %s',
            $date,
            str_pad($level, 8),
            $msg
        );
    }
}

/* End of file Logger.php */
