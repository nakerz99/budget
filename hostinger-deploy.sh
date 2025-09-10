#!/bin/bash

echo "🚀 Starting Hostinger deployment for Budget Tracker..."

# Step 1: Clean directory
echo "🧹 Cleaning current directory..."
rm -rf *
rm -rf .*
echo "✅ Directory cleaned"

# Step 2: Clone repository
echo "📥 Cloning repository..."
git clone https://github.com/nakerz99/budget.git .
echo "✅ Repository cloned"

# Step 3: Set up .env
echo "⚙️ Setting up environment..."
cp .env.example .env

# Step 4: Generate app key
echo "🔑 Generating application key..."
php artisan key:generate

# Step 5: Create public_html
echo "📁 Setting up public_html..."
mkdir -p public_html
cp -r public/* public_html/

# Step 6: Update public_html/index.php for Hostinger
echo "🔧 Updating index.php for Hostinger..."
cat > public_html/index.php << 'EOF'
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
EOF

# Step 7: Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env

# Step 8: Database setup
echo "🗄️ Setting up database..."
php artisan migrate --force
php artisan db:seed --force

# Step 9: Clear caches
echo "🧹 Clearing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment completed!"
echo "🌐 Your application should be available at: https://nrbudegetplanner.online"
echo ""
echo "⚠️  Don't forget to:"
echo "1. Update .env with your database credentials"
echo "2. Set APP_URL=https://nrbudegetplanner.online"
echo "3. Set APP_ENV=production"
echo "4. Set APP_DEBUG=false"

