<?php
/**
 * Cron Job Handler for Hostinger
 * 
 * This file handles scheduled tasks for the Budget Tracker
 * Set up as a cron job in Hostinger cPanel
 */

// Security check - only allow from localhost (cron jobs)
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    die('Access denied');
}

// Set time limit for long-running tasks
set_time_limit(300);

// Include Laravel bootstrap
require_once __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "[" . date('Y-m-d H:i:s') . "] Starting cron job...\n";

try {
    // Run scheduled tasks
    $exitCode = Artisan::call('schedule:run');
    
    if ($exitCode === 0) {
        echo "[" . date('Y-m-d H:i:s') . "] Cron job completed successfully\n";
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] Cron job completed with errors (exit code: $exitCode)\n";
    }
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Cron job failed: " . $e->getMessage() . "\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Cron job finished\n";
?>
