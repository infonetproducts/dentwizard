<?php
/**
 * AUTOMATED CORS FIX SCRIPT
 * 
 * This script automatically adds CORS headers to all PHP files in the current directory
 * 
 * USAGE:
 * 1. Upload this file to /lg/API/
 * 2. Run: php auto-fix-cors.php
 * 3. Delete this file after running
 */

$cors_headers = '<?php
// CORS Headers - Auto-added by fix script
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER[\'REQUEST_METHOD\'] === \'OPTIONS\') {
    http_response_code(200);
    exit();
}
?>' . "\n";

$files_updated = 0;
$files_skipped = 0;
$errors = [];

// Get all PHP files in current directory and subdirectories
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filename = $file->getPathname();
        
        // Skip this script itself
        if (basename($filename) === 'auto-fix-cors.php') {
            continue;
        }
        
        // Read file content
        $content = file_get_contents($filename);
        
        // Check if CORS headers already exist
        if (strpos($content, 'Access-Control-Allow-Origin') !== false) {
            echo "⏭️  Skipped (already has CORS): $filename\n";
            $files_skipped++;
            continue;
        }
        
        // Add CORS headers at the beginning, after <?php tag
        if (strpos($content, '<?php') === 0) {
            // Replace the opening <?php tag with our headers
            $new_content = $cors_headers . substr($content, 5);
        } else {
            // No <?php tag at start, add our headers at the very beginning
            $new_content = $cors_headers . $content;
        }
        
        // Backup original file
        $backup_name = $filename . '.backup';
        copy($filename, $backup_name);
        
        // Write updated content
        if (file_put_contents($filename, $new_content)) {
            echo "✅ Updated: $filename (backup: $backup_name)\n";
            $files_updated++;
        } else {
            echo "❌ Error updating: $filename\n";
            $errors[] = $filename;
        }
    }
}

echo "\n========================================\n";
echo "CORS Fix Script Complete!\n";
echo "========================================\n";
echo "Files updated: $files_updated\n";
echo "Files skipped: $files_skipped\n";

if (count($errors) > 0) {
    echo "Errors: " . count($errors) . "\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\n⚠️  IMPORTANT: Delete this script after running!\n";
echo "⚠️  Backups created with .backup extension\n";
?>
