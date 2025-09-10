# Hostinger Deployment Instructions

## Pre-Deployment Checklist

### 1. Local Preparation
- [ ] All code is committed and tested
- [ ] Database migrations are ready
- [ ] Environment variables are configured
- [ ] All dependencies are installed

### 2. Hostinger Setup
- [ ] Domain is configured and pointing to Hostinger
- [ ] MySQL database is created in cPanel
- [ ] Database user is created with proper permissions
- [ ] SSL certificate is enabled (free SSL available)

## Deployment Steps

### Step 1: Prepare Files Locally
```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create deployment package
zip -r budget-tracker.zip . -x "node_modules/*" ".git/*" "tests/*" "*.md" "deploy-*" "cron.php" "artisan-web.php"
```

### Step 2: Upload to Hostinger
1. **Login to Hostinger cPanel**
2. **Go to File Manager**
3. **Navigate to `public_html` folder**
4. **Upload `budget-tracker.zip`**
5. **Extract the zip file**
6. **Move contents from Laravel's `public` folder to `public_html` root:**
   - Move `public/index.php` → `public_html/index.php`
   - Move `public/.htaccess` → `public_html/.htaccess`
   - Move `public/assets/` → `public_html/assets/` (if exists)

### Step 3: Environment Configuration
1. **Create `.env` file in `public_html` root:**
```bash
APP_NAME="Budget Tracker"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_hostinger_db_name
DB_USERNAME=your_hostinger_db_user
DB_PASSWORD=your_hostinger_db_password

# Cache Configuration
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail Configuration (Hostinger SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your-email@your-domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Broadcasting (Optional)
BROADCAST_DRIVER=log
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

# Sanctum
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

### Step 4: Database Setup
1. **Run migrations using the web interface:**
   - Visit: `https://your-domain.com/deploy-hostinger.php?password=deploy2024`
   - Click "Run Migrations"
   - Click "Seed Admin User"

### Step 5: Set File Permissions
Set the following permissions in Hostinger File Manager:
- **Folders**: 755 (storage, bootstrap/cache, public_html)
- **Files**: 644 (.env, .htaccess, index.php)

### Step 6: Test the Application
1. **Visit your domain** to see the landing page
2. **Test registration** - create a test user
3. **Test admin login** - use the seeded admin user
4. **Test user approval** - approve the test user
5. **Test user login** - login with the approved user

### Step 7: Security Cleanup
**IMPORTANT**: After successful deployment, delete these files for security:
- `deploy-hostinger.php`
- `artisan-web.php`
- `cron.php` (unless you need it for scheduled tasks)

## Post-Deployment Configuration

### 1. Cron Job Setup (Optional)
If you need scheduled tasks, set up a cron job in Hostinger cPanel:
- **URL**: `https://your-domain.com/cron.php`
- **Frequency**: Daily at 2:00 AM
- **Note**: This requires keeping the `cron.php` file

### 2. SSL Certificate
- Enable free SSL certificate in Hostinger cPanel
- Force HTTPS redirect in `.htaccess`

### 3. Backup Strategy
- Set up regular database backups in cPanel
- Download application files periodically
- Keep `.env` file secure and backed up

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check file permissions (755 for folders, 644 for files)
   - Check `.env` file exists and has correct database credentials
   - Check error logs in Hostinger cPanel

2. **Database Connection Error**
   - Verify database credentials in `.env`
   - Check if database user has proper permissions
   - Ensure database exists

3. **Storage Permission Error**
   - Set storage folder permissions to 755
   - Check if `bootstrap/cache` is writable

4. **Route Not Found**
   - Ensure `.htaccess` file is in `public_html` root
   - Check if mod_rewrite is enabled
   - Verify `index.php` is in `public_html` root

### Debug Mode
To enable debug mode temporarily:
1. Edit `.env` file
2. Set `APP_DEBUG=true`
3. Check error messages
4. **Remember to set back to `false` for production**

## Admin Access

### Default Admin User
- **Username**: admin
- **PIN**: 123456
- **Note**: Change the PIN after first login

### Creating Additional Admin Users
1. Login as admin
2. Go to admin dashboard
3. Use the admin interface to manage users

## Maintenance

### Regular Tasks
- Clear cache monthly: `php artisan cache:clear`
- Check error logs weekly
- Monitor disk usage
- Update Laravel when new versions are available

### Performance Optimization
- Enable gzip compression (already in `.htaccess`)
- Use browser caching (already configured)
- Monitor database performance
- Consider upgrading to VPS if needed

## Support

If you encounter issues:
1. Check Hostinger error logs
2. Verify all files are uploaded correctly
3. Ensure proper file permissions
4. Check database connectivity
5. Review Laravel logs in `storage/logs/`

## Security Notes

- Never leave deployment files on production
- Use strong passwords for admin accounts
- Enable SSL certificate
- Regular security updates
- Monitor for suspicious activity
- Backup data regularly
