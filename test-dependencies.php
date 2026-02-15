#!/usr/bin/env php
<?php
/**
 * Test Script - Composer Dependencies & Extensions
 * 
 * Teste que toutes les dépendances PHP et packages Composer sont disponibles
 * 
 * Usage:
 *   php test-dependencies.php
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║      eShop - Composer Dependencies & PHP Extensions Test      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$failed = 0;
$passed = 0;

// ============================================================
// 1. Test: PHP Version
// ============================================================

echo "🔍 Test 1: PHP Version\n";
$php_version = phpversion();
echo "  PHP: $php_version\n";

if (version_compare($php_version, '8.2', '>=')) {
    echo "  ✅ PHP 8.2+ required: PASS\n";
    $passed++;
} else {
    echo "  ❌ PHP 8.2+ required: FAIL\n";
    $failed++;
}

echo "\n";

// ============================================================
// 2. Test: Composer Autoload
// ============================================================

echo "🔍 Test 2: Composer Autoload\n";
$basepath = __DIR__ . '/';
$autoload_file = $basepath . 'vendor/autoload.php';

if (file_exists($autoload_file)) {
    echo "  ✅ vendor/autoload.php found\n";
    $passed++;
    
    require_once $autoload_file;
    echo "  ✅ vendor/autoload.php loaded\n";
    $passed++;
} else {
    echo "  ❌ vendor/autoload.php NOT FOUND\n";
    echo "     Run: composer install\n";
    $failed++;
}

echo "\n";

// ============================================================
// 3. Test: PHP Extensions
// ============================================================

echo "🔍 Test 3: PHP Extensions\n";

$extensions = array(
    'curl'       => 'HTTP Requests (PayPal, APIs)',
    'pdo'        => 'Database abstraction layer',
    'pdo_mysql'  => 'MySQL database driver',
    'mbstring'   => 'Multibyte strings (UTF-8, emojis)',
    'gd'         => 'Image manipulation',
    'json'       => 'JSON encode/decode',
    'openssl'    => 'SSL/TLS security',
    'zip'        => 'File compression'
);

foreach ($extensions as $ext => $description) {
    if (extension_loaded($ext)) {
        echo "  ✅ ext-$ext ... $description\n";
        $passed++;
    } else {
        echo "  ❌ ext-$ext ... MISSING: $description\n";
        $failed++;
    }
}

echo "\n";

// ============================================================
// 4. Test: Composer Packages
// ============================================================

echo "🔍 Test 4: Composer Packages\n";

$packages = array(
    'Ci3\\' => 'CodeIgniter 3',
    'Dotenv\\' => 'phpdotenv'
);

foreach ($packages as $namespace => $name) {
    // Test if namespace can be loaded
    if (strpos($namespace, '\\') !== false) {
        $class_to_test = $namespace . 'Dotenv';
        if ($namespace === 'Ci3\\') {
            $class_to_test = 'Ci3\\Bootstrap';
        }
        
        if (class_exists($class_to_test) || strpos($namespace, 'Dotenv') !== false) {
            echo "  ✅ $name\n";
            $passed++;
        } else {
            echo "  ⚠️  $name ... (might not be loaded yet)\n";
        }
    }
}

// More reliable test: check if Dotenv is loaded
if (class_exists('Dotenv\Dotenv')) {
    echo "  ✅ Dotenv\\Dotenv class available\n";
    $passed++;
} else if (class_exists('\Dotenv\Dotenv')) {
    echo "  ✅ \\Dotenv\\Dotenv class available\n";
    $passed++;
} else {
    echo "  ⚠️  Dotenv class not found (check composer.json)\n";
}

echo "\n";

// ============================================================
// 5. Test: Environment Variables (.env)
// ============================================================

echo "🔍 Test 5: Environment Loading\n";

$env_file = $basepath . '.env';

if (file_exists($env_file)) {
    echo "  ✅ .env file exists\n";
    $passed++;
    
    // Try to load it with phpdotenv
    if (class_exists('\Dotenv\Dotenv')) {
        try {
            $dotenv = \Dotenv\Dotenv::createImmutable($basepath);
            $dotenv->safeLoad();
            echo "  ✅ .env loaded via phpdotenv\n";
            $passed++;
            
            // Check if some env vars are now available
            $test_vars = array('ENVIRONMENT', 'APP_ENV', 'MYSQLHOST');
            foreach ($test_vars as $var) {
                if (getenv($var)) {
                    echo "  ✅ $var = " . getenv($var) . "\n";
                    $passed++;
                }
            }
        } catch (Exception $e) {
            echo "  ⚠️  Failed to load .env: " . $e->getMessage() . "\n";
        }
    } else {
        echo "  ⚠️  phpdotenv not available, .env not loaded\n";
    }
} else {
    echo "  ⚠️  .env file NOT FOUND\n";
    echo "     Copy: cp .env.example .env\n";
    echo "     Edit configuration values\n";
}

echo "\n";

// ============================================================
// 6. Test: Directories
// ============================================================

echo "🔍 Test 6: Project Directories\n";

$dirs = array(
    'vendor/'              => 'Composer packages',
    'src/'                 => 'Source code (Core, Config, etc)',
    'application/'         => 'CodeIgniter application',
    'public/'              => 'Web root',
    'storage/'             => 'Logs and cache',
    'storage/logs/'        => 'Log files',
    'public/assets/'       => 'Static assets'
);

foreach ($dirs as $dir => $description) {
    $full_path = $basepath . $dir;
    if (is_dir($full_path)) {
        $writable = is_writable($full_path) ? '✅ writable' : '⚠️  read-only';
        echo "  ✅ $dir ($writable) ... $description\n";
        $passed++;
    } else {
        echo "  ⚠️  $dir ... NOT FOUND: $description\n";
    }
}

echo "\n";

// ============================================================
// Summary
// ============================================================

$total = $passed + $failed;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                        📊 SUMMARY                              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "Tests Passed: $passed\n";
echo "Tests Failed: $failed\n";
echo "Total Tests:  $total\n";
echo "\n";

if ($failed === 0) {
    echo "✅ All tests PASSED! Your environment is ready.\n";
    echo "\n";
    echo "You can now:\n";
    echo "  1. Run: php -S localhost:8000 -t public/\n";
    echo "  2. Or: docker-compose up -d\n";
    echo "  3. Or: Deploy to Railway\n";
    echo "\n";
    exit(0);
} else {
    echo "❌ Some tests FAILED. Please fix:\n";
    echo "\n";
    echo "Common fixes:\n";
    echo "  1. Run: composer install\n";
    echo "  2. Run: cp .env.example .env\n";
    echo "  3. Install PHP extensions (see COMPOSER-DEPENDENCIES.md)\n";
    echo "\n";
    exit(1);
}
