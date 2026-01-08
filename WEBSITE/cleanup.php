<?php
/**
 * Auto-cleanup script for Legend House
 * Removes temporary and backup files automatically
 */

function cleanupWasteFiles() {
    $patterns = [
        '*.backup',
        '*.bak',
        '*~',
        '*.tmp',
        '*.swp',
        '*.swo'
    ];
    
    $directories = [
        __DIR__,
        __DIR__ . '/tools',
        __DIR__ . '/cache',
        __DIR__ . '/tmp'
    ];
    
    $removedFiles = [];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) continue;
        
        foreach ($patterns as $pattern) {
            $files = glob($dir . '/' . $pattern);
            foreach ($files as $file) {
                if (is_file($file) && unlink($file)) {
                    $removedFiles[] = $file;
                }
            }
        }
    }
    
    return $removedFiles;
}

// Run cleanup
$removed = cleanupWasteFiles();
if (count($removed) > 0) {
    echo "Cleaned up " . count($removed) . " waste files:\n";
    foreach ($removed as $file) {
        echo "  - " . basename($file) . "\n";
    }
} else {
    echo "No waste files found. System is clean!\n";
}
?>
