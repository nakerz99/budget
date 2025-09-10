<?php
/**
 * Hostinger Deployment Helper
 * 
 * This file helps with deploying Laravel to Hostinger shared hosting
 * Run this file after uploading to Hostinger to set up the application
 */

// Security check - only allow from specific IP or with password
$allowed_ips = ['127.0.0.1', '::1'];
$password = 'deploy2024'; // Change this password!

if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips) && 
    ($_GET['password'] ?? '') !== $password) {
    die('Access denied. Use ?password=deploy2024');
}

echo "<h1>Budget Tracker - Hostinger Deployment</h1>";

// Check if we're in the right directory
if (!file_exists('artisan')) {
    die('<p style="color: red;">Error: artisan file not found. Make sure you uploaded all Laravel files to public_html root.</p>');
}

echo "<p style='color: green;'>✓ Laravel application detected</p>";

// Check PHP version
$phpVersion = phpversion();
echo "<p>PHP Version: $phpVersion</p>";

if (version_compare($phpVersion, '7.4.0', '<')) {
    echo "<p style='color: red;'>Warning: PHP 7.4+ required. Current: $phpVersion</p>";
} else {
    echo "<p style='color: green;'>✓ PHP version compatible</p>";
}

// Check if .env exists
if (!file_exists('.env')) {
    echo "<p style='color: orange;'>⚠ .env file not found. Please create it from .env.example</p>";
} else {
    echo "<p style='color: green;'>✓ .env file found</p>";
}

// Check database connection
try {
    $pdo = new PDO(
        'mysql:host=' . env('DB_HOST', 'localhost') . ';port=' . env('DB_PORT', '3306') . ';dbname=' . env('DB_DATABASE'),
        env('DB_USERNAME'),
        env('DB_PASSWORD')
    );
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Check storage permissions
if (!is_writable('storage')) {
    echo "<p style='color: red;'>✗ Storage directory not writable</p>";
} else {
    echo "<p style='color: green;'>✓ Storage directory writable</p>";
}

// Check bootstrap/cache permissions
if (!is_writable('bootstrap/cache')) {
    echo "<p style='color: red;'>✗ Bootstrap cache directory not writable</p>";
} else {
    echo "<p style='color: green;'>✓ Bootstrap cache directory writable</p>";
}

echo "<h2>Deployment Steps</h2>";
echo "<ol>";
echo "<li>Create .env file from .env.example</li>";
echo "<li>Update database credentials in .env</li>";
echo "<li>Run migrations: <code>php artisan migrate --force</code></li>";
echo "<li>Seed admin user: <code>php artisan db:seed --class=AdminUserSeeder</code></li>";
echo "<li>Clear cache: <code>php artisan config:cache</code></li>";
echo "<li>Set proper file permissions (755 for folders, 644 for files)</li>";
echo "<li>Delete this file for security</li>";
echo "</ol>";

echo "<h2>Quick Commands</h2>";
echo "<p><a href='?action=migrate&password=$password'>Run Migrations</a></p>";
echo "<p><a href='?action=seed&password=$password'>Seed Admin User</a></p>";
echo "<p><a href='?action=cache&password=$password'>Clear Cache</a></p>";

// Handle actions
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'migrate':
        echo "<h3>Running Migrations...</h3>";
        $output = shell_exec('php artisan migrate --force 2>&1');
        echo "<pre>$output</pre>";
        break;
        
    case 'seed':
        echo "<h3>Seeding Admin User...</h3>";
        $output = shell_exec('php artisan db:seed --class=AdminUserSeeder 2>&1');
        echo "<pre>$output</pre>";
        break;
        
    case 'cache':
        echo "<h3>Clearing Cache...</h3>";
        $output = shell_exec('php artisan config:cache 2>&1');
        echo "<pre>$output</pre>";
        break;
}

// Helper function to get env values
function env($key, $default = null) {
    $envFile = file_get_contents('.env');
    preg_match("/^$key=(.*)$/m", $envFile, $matches);
    return $matches[1] ?? $default;
}
?>
