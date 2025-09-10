<?php
/**
 * Web Interface for Artisan Commands
 * 
 * This file provides a web interface for running Artisan commands
 * on Hostinger shared hosting where SSH access is not available
 */

// Security check - only allow from specific IP or with password
$allowed_ips = ['127.0.0.1', '::1'];
$password = 'artisan2024'; // Change this password!

if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips) && 
    ($_GET['password'] ?? '') !== $password) {
    die('Access denied. Use ?password=artisan2024');
}

// Include Laravel bootstrap
require_once __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Budget Tracker - Artisan Web Interface</h1>";
echo "<p><strong>Warning:</strong> This interface should be deleted after deployment for security!</p>";

// Handle command execution
if (isset($_POST['command'])) {
    $command = $_POST['command'];
    $password_check = $_POST['password'] ?? '';
    
    if ($password_check !== $password) {
        die('Invalid password');
    }
    
    echo "<h2>Command Output</h2>";
    echo "<pre>";
    
    try {
        $exitCode = Artisan::call($command);
        echo Artisan::output();
        
        if ($exitCode !== 0) {
            echo "\nCommand failed with exit code: $exitCode";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    
    echo "</pre>";
    echo "<hr>";
}

?>

<h2>Available Commands</h2>

<form method="post">
    <input type="hidden" name="password" value="<?php echo $password; ?>">
    
    <div style="margin-bottom: 10px;">
        <label>Command:</label><br>
        <select name="command" style="width: 300px;">
            <option value="migrate --force">Run Migrations</option>
            <option value="db:seed --class=AdminUserSeeder">Seed Admin User</option>
            <option value="config:cache">Cache Configuration</option>
            <option value="route:cache">Cache Routes</option>
            <option value="view:cache">Cache Views</option>
            <option value="cache:clear">Clear All Cache</option>
            <option value="storage:link">Create Storage Link</option>
            <option value="key:generate">Generate Application Key</option>
        </select>
    </div>
    
    <button type="submit">Execute Command</button>
</form>

<h2>Custom Command</h2>

<form method="post">
    <input type="hidden" name="password" value="<?php echo $password; ?>">
    
    <div style="margin-bottom: 10px;">
        <label>Custom Command:</label><br>
        <input type="text" name="command" placeholder="e.g., migrate:status" style="width: 300px;">
    </div>
    
    <button type="submit">Execute Custom Command</button>
</form>

<h2>System Information</h2>
<ul>
    <li>PHP Version: <?php echo phpversion(); ?></li>
    <li>Laravel Version: <?php echo app()->version(); ?></li>
    <li>Current Time: <?php echo date('Y-m-d H:i:s'); ?></li>
    <li>Server IP: <?php echo $_SERVER['REMOTE_ADDR']; ?></li>
</ul>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
button { background: #007cba; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background: #005a87; }
input, select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
</style>
