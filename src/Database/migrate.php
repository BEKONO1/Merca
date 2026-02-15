<?php
/**
 * Database Migration Script
 * 
 * Reads and executes SQL files from Migrations/ directory
 * Supports DATABASE_URL and individual MYSQL* env vars
 * 
 * Usage:
 *   php src/Database/migrate.php              # Run all migrations
 *   ENVIRONMENT=production php src/Database/migrate.php  # Production
 */

// Set base paths
define('BASEPATH', dirname(dirname(dirname(__FILE__))) . '/');
define('MIGRATIONS_PATH', BASEPATH . 'application/migrations/');

// Ensure Migrations directory exists
if (!is_dir(MIGRATIONS_PATH)) {
    mkdir(MIGRATIONS_PATH, 0755, true);
}

// Load environment variables (from .env if available)
if (file_exists(BASEPATH . 'vendor/autoload.php')) {
    require_once BASEPATH . 'vendor/autoload.php';
    
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = \Dotenv\Dotenv::createImmutable(BASEPATH);
            $dotenv->safeLoad();
        } catch (Exception $e) {
            error_log('[MIGRATE] Warning: Could not load .env - ' . $e->getMessage());
        }
    }
}

// Load database configuration
require_once BASEPATH . 'src/Config/database.php';

// Display header
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║              🗄️  DATABASE MIGRATION SCRIPT                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Get database configuration
try {
    $dbConfig = getDbConfig();
    
    echo "📊 Database Configuration:\n";
    echo "   Host: " . $dbConfig['hostname'] . "\n";
    echo "   Database: " . $dbConfig['database'] . "\n";
    echo "   Charset: " . $dbConfig['char_set'] . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error loading database config: " . $e->getMessage() . "\n";
    error_log('[MIGRATE] Config error: ' . $e->getMessage());
    exit(1);
}

// Create PDO connection
try {
    // Build DSN with port if specified
    $port = !empty($dbConfig['port']) ? ':' . $dbConfig['port'] : '';
    $dsn = 'mysql:host=' . $dbConfig['hostname'] . $port . ';charset=' . $dbConfig['char_set'];
    
    $pdo = new PDO(
        $dsn,
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30,
        ]
    );
    
    echo "✅ Connected to MySQL\n";
    
} catch (PDOException $e) {
    echo "❌ Connection Error: " . $e->getMessage() . "\n";
    error_log('[MIGRATE] Connection error: ' . $e->getMessage());
    exit(1);
}

// Create database if not exists
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $dbConfig['database'] . "`");
    echo "✅ Database created or already exists\n";
} catch (PDOException $e) {
    echo "❌ Create database error: " . $e->getMessage() . "\n";
    error_log('[MIGRATE] Create database error: ' . $e->getMessage());
    exit(1);
}

// Select database
try {
    $pdo->exec("USE `" . $dbConfig['database'] . "`");
    echo "✅ Selected database: " . $dbConfig['database'] . "\n\n";
} catch (PDOException $e) {
    echo "❌ Select database error: " . $e->getMessage() . "\n";
    error_log('[MIGRATE] Select database error: ' . $e->getMessage());
    exit(1);
}

// Create migrations tracking table if not exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL UNIQUE,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Migrations tracking table ready\n\n";
} catch (PDOException $e) {
    echo "⚠️  Could not create migrations table: " . $e->getMessage() . "\n";
}

// Read migration files
echo "🔍 Reading migration files from: " . MIGRATIONS_PATH . "\n\n";

$sqlFiles = glob(MIGRATIONS_PATH . "*.sql");

if (empty($sqlFiles)) {
    echo "ℹ️  No SQL migration files found (expected pattern: *.sql)\n";
    echo "    Checked: " . MIGRATIONS_PATH . "\n";
    echo "\n✅ Migration complete (nothing to migrate)\n\n";
    exit(0);
}

// Sort files by name (typically contains timestamp)
sort($sqlFiles);

// Execute migrations
$executed = 0;
$skipped = 0;
$failed = 0;

echo "═════════════════════════════════════════════════════════════════\n";
echo "🚀 EXECUTING MIGRATIONS\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

foreach ($sqlFiles as $filePath) {
    $filename = basename($filePath);
    
    // Check if migration already executed
    try {
        $stmt = $pdo->prepare("SELECT id FROM migrations WHERE migration = ?");
        $stmt->execute([$filename]);
        
        if ($stmt->fetch()) {
            echo "⏭️  Skipped: $filename (already executed)\n";
            $skipped++;
            continue;
        }
    } catch (PDOException $e) {
        // Table might not exist yet, continue
    }
    
    // Read and execute migration file
    try {
        $sql = file_get_contents($filePath);
        
        if (empty(trim($sql))) {
            echo "⚠️  Empty: $filename (skipped)\n";
            $skipped++;
            continue;
        }
        
        // Split statements by semicolon and execute each
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($s) { return !empty($s); }
        );
        
        $statementCount = 0;
        foreach ($statements as $statement) {
            try {
                $pdo->exec($statement);
                $statementCount++;
            } catch (PDOException $e) {
                throw new Exception("Statement $statementCount failed: " . $e->getMessage());
            }
        }
        
        // Record migration as executed
        try {
            $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$filename]);
        } catch (PDOException $e) {
            // Tracking failed, but migration succeeded - log warning
            error_log("[MIGRATE] Could not record migration $filename: " . $e->getMessage());
        }
        
        echo "✅ Migrated: $filename ($statementCount statements)\n";
        $executed++;
        
    } catch (Exception $e) {
        echo "❌ Failed: $filename\n";
        echo "   Error: " . $e->getMessage() . "\n";
        error_log("[MIGRATE] Migration error in $filename: " . $e->getMessage());
        $failed++;
    }
}

// Summary
echo "\n═════════════════════════════════════════════════════════════════\n";
echo "📊 MIGRATION SUMMARY\n";
echo "═════════════════════════════════════════════════════════════════\n\n";
echo "   Executed: $executed\n";
echo "   Skipped:  $skipped\n";
echo "   Failed:   $failed\n";
echo "\n";

if ($failed > 0) {
    echo "❌ Migration completed with errors\n\n";
    exit(1);
} else {
    echo "✅ Migration completed successfully\n\n";
    exit(0);
}
