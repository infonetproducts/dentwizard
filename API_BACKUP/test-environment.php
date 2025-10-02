<?php
/**
 * API Environment Test Script
 * Run this script to verify your environment is ready for the API
 * 
 * Usage: php test-environment.php
 * Or visit: http://your-server.com/API/test-environment.php
 */

header('Content-Type: text/plain');

echo "===========================================\n";
echo "    DentWizard API Environment Test\n";
echo "===========================================\n\n";

$errors = 0;
$warnings = 0;

// 1. Check PHP Version
echo "[1] Checking PHP Version...\n";
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "    ✓ PHP Version: $phpVersion (OK)\n";
} else {
    echo "    ✗ PHP Version: $phpVersion (Requires 7.4+)\n";
    $errors++;
}
echo "\n";

// 2. Check Required PHP Extensions
echo "[2] Checking Required PHP Extensions...\n";
$requiredExtensions = [
    'PDO' => 'Database connectivity',
    'pdo_mysql' => 'MySQL database driver',
    'json' => 'JSON processing',
    'session' => 'Session management',
    'openssl' => 'JWT token generation',
    'curl' => 'External API calls'
];

foreach ($requiredExtensions as $ext => $purpose) {
    if (extension_loaded($ext)) {
        echo "    ✓ $ext: Installed ($purpose)\n";
    } else {
        echo "    ✗ $ext: Missing ($purpose)\n";
        $errors++;
    }
}
echo "\n";

// 3. Check Optional PHP Extensions
echo "[3] Checking Optional PHP Extensions...\n";
$optionalExtensions = [
    'opcache' => 'Performance optimization',
    'mbstring' => 'Multi-byte string support',
    'gd' => 'Image processing'
];

foreach ($optionalExtensions as $ext => $purpose) {
    if (extension_loaded($ext)) {
        echo "    ✓ $ext: Installed ($purpose)\n";
    } else {
        echo "    ⚠ $ext: Not installed ($purpose) - Optional\n";
        $warnings++;
    }
}
echo "\n";

// 4. Check File Permissions
echo "[4] Checking File Permissions...\n";
$currentDir = __DIR__;
if (is_writable($currentDir)) {
    echo "    ✓ API directory is writable\n";
} else {
    echo "    ⚠ API directory is not writable - May cause issues with logging\n";
    $warnings++;
}

if (file_exists('.htaccess')) {
    if (is_readable('.htaccess')) {
        echo "    ✓ .htaccess is readable\n";
    } else {
        echo "    ✗ .htaccess is not readable\n";
        $errors++;
    }
} else {
    echo "    ⚠ .htaccess not found - URL rewriting may not work\n";
    $warnings++;
}
echo "\n";

// 5. Check Composer
echo "[5] Checking Composer Dependencies...\n";
if (file_exists('vendor/autoload.php')) {
    echo "    ✓ Composer dependencies installed\n";
    require_once 'vendor/autoload.php';
    
    // Check for JWT library
    if (class_exists('Firebase\JWT\JWT')) {
        echo "    ✓ JWT library available\n";
    } else {
        echo "    ✗ JWT library not found\n";
        $errors++;
    }
} else {
    echo "    ✗ Composer dependencies not installed\n";
    echo "      Run: composer install\n";
    $errors++;
}
echo "\n";

// 6. Check Environment Configuration
echo "[6] Checking Environment Configuration...\n";
if (file_exists('.env')) {
    echo "    ✓ .env file exists\n";
    
    // Load and check .env
    $env = parse_ini_file('.env');
    
    $requiredEnvVars = [
        'DB_HOST' => 'Database host',
        'DB_NAME' => 'Database name',
        'DB_USER' => 'Database user',
        'JWT_SECRET_KEY' => 'JWT secret key',
        'BASE_URL' => 'Base URL for images'
    ];
    
    foreach ($requiredEnvVars as $var => $description) {
        if (isset($env[$var]) && !empty($env[$var])) {
            if ($var === 'JWT_SECRET_KEY' && $env[$var] === 'your-secret-key-change-this-in-production') {
                echo "    ⚠ $var: Using default value - MUST CHANGE!\n";
                $warnings++;
            } else {
                echo "    ✓ $var: Set\n";
            }
        } else {
            echo "    ✗ $var: Not set ($description)\n";
            $errors++;
        }
    }
} else {
    echo "    ✗ .env file not found\n";
    echo "      Run: cp .env.example .env\n";
    echo "      Then edit .env with your configuration\n";
    $errors++;
}
echo "\n";

// 7. Test Database Connection
echo "[7] Testing Database Connection...\n";
if (file_exists('.env')) {
    $env = parse_ini_file('.env');
    
    if (isset($env['DB_HOST']) && isset($env['DB_NAME']) && isset($env['DB_USER'])) {
        try {
            $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8";
            $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASSWORD'] ?? '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "    ✓ Database connection successful\n";
            
            // Check for key tables
            $tables = ['Users', 'Items', 'Orders', 'Clients', 'Category'];
            echo "    Checking tables:\n";
            
            foreach ($tables as $table) {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if ($stmt->rowCount() > 0) {
                    echo "      ✓ Table '$table' exists\n";
                } else {
                    echo "      ✗ Table '$table' not found\n";
                    $errors++;
                }
            }
        } catch (PDOException $e) {
            echo "    ✗ Database connection failed: " . $e->getMessage() . "\n";
            $errors++;
        }
    } else {
        echo "    ✗ Database configuration incomplete\n";
        $errors++;
    }
}
echo "\n";

// 8. Check lg_files Directory
echo "[8] Checking lg_files Directory...\n";
$lg_files_path = dirname(__DIR__) . '/lg_files';
if (is_dir($lg_files_path)) {
    echo "    ✓ lg_files directory found\n";
    
    // Check for key files
    if (file_exists($lg_files_path . '/shop_common_function.php')) {
        echo "    ✓ shop_common_function.php found\n";
    } else {
        echo "    ⚠ shop_common_function.php not found\n";
        $warnings++;
    }
} else {
    echo "    ⚠ lg_files directory not found at: $lg_files_path\n";
    echo "      Some functions may not work correctly\n";
    $warnings++;
}
echo "\n";

// 9. Check Web Server
echo "[9] Checking Web Server...\n";
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
echo "    Server: $serverSoftware\n";

if (stripos($serverSoftware, 'apache') !== false) {
    echo "    ✓ Apache detected\n";
    
    // Check if mod_rewrite is enabled
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        if (in_array('mod_rewrite', $modules)) {
            echo "    ✓ mod_rewrite enabled\n";
        } else {
            echo "    ✗ mod_rewrite not enabled - URL rewriting won't work\n";
            $errors++;
        }
    }
} elseif (stripos($serverSoftware, 'nginx') !== false) {
    echo "    ✓ Nginx detected\n";
    echo "    ⚠ Make sure to configure Nginx rewrite rules\n";
    $warnings++;
}
echo "\n";

// 10. Check SSL/HTTPS
echo "[10] Checking SSL/HTTPS...\n";
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    echo "    ✓ HTTPS is enabled\n";
} else {
    echo "    ⚠ HTTPS is not enabled - Required for production\n";
    $warnings++;
}
echo "\n";

// Summary
echo "===========================================\n";
echo "                 SUMMARY\n";
echo "===========================================\n";
echo "Errors:   $errors\n";
echo "Warnings: $warnings\n\n";

if ($errors === 0) {
    if ($warnings === 0) {
        echo "✓ Environment is fully ready for API implementation!\n";
    } else {
        echo "✓ Environment is ready, but there are some warnings to address.\n";
    }
} else {
    echo "✗ Environment is not ready. Please fix the errors above.\n";
}

echo "\n";
echo "Next Steps:\n";
if ($errors > 0) {
    echo "1. Fix all errors marked with ✗\n";
    echo "2. Run this test again\n";
} else {
    echo "1. Review and fix any warnings (marked with ⚠)\n";
    echo "2. Implement SSO validation in /v1/auth/validate.php\n";
    echo "3. Test each API endpoint\n";
    echo "4. Update CORS settings for your React app\n";
}

echo "\n===========================================\n";
echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
?>