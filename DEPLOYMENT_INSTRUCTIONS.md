# Budget Tracker - Hostinger Deployment Instructions

## Files Included
- `budget-deployment.zip` - Complete Laravel application ready for deployment

## Pre-Deployment Setup

### 1. Database Setup
1. Create a MySQL database in Hostinger hPanel
2. Note down the database credentials:
   - Database name
   - Username
   - Password
   - Host (usually localhost)

### 2. Upload Files
1. Extract `budget-deployment.zip` to your domain's root directory
2. Both `public/` and `public_html/` folders are included for maximum compatibility
3. Use `public_html/` for Hostinger shared hosting
4. Use `public/` for VPS or other hosting providers
5. No additional file moving is required - the structure is optimized for both setups

## Post-Upload Configuration

### 1. Environment Setup
1. Copy `.env.example` to `.env`
2. Update `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

### 2. Application Key
1. Generate application key (run in terminal or use online generator):
```bash
php artisan key:generate
```
2. Copy the generated key to your `.env` file:
```env
APP_KEY=base64:your_generated_key_here
```

### 3. Database Migration
1. Run migrations to create tables:
```bash
php artisan migrate
```

### 4. Seed Sample Data
1. Run seeders to populate with sample data:
```bash
php artisan db:seed
```

### 5. Set Permissions
1. Set proper permissions for storage and bootstrap/cache:
```bash
chmod -R 775 storage bootstrap/cache
```

### 6. Clear Caches
1. Clear application caches:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Default Login Credentials
- **Admin User**: admin / 123456
- **Regular User**: user / 123456

## Features Included
- ✅ User Authentication & Registration
- ✅ Transaction Management
- ✅ Budget Planning & Tracking
- ✅ Bills & Subscriptions Management
- ✅ Savings Goals
- ✅ Financial Reports with Charts
- ✅ Settings & Profile Management
- ✅ Philippine Peso (PHP) as default currency
- ✅ Manila timezone as default
- ✅ Realistic sample data

## Troubleshooting

### Common Issues:
1. **500 Error**: Check file permissions and .env configuration
2. **Database Connection Error**: Verify database credentials in .env
3. **Missing Key Error**: Run `php artisan key:generate`
4. **Permission Denied**: Set proper permissions on storage and cache folders

### File Structure After Deployment:
```
your-domain.com/
├── public/               ← For VPS/other hosting
│   ├── index.php
│   ├── .htaccess
│   ├── favicon.ico
│   └── robots.txt
├── public_html/          ← For Hostinger shared hosting
│   ├── index.php
│   ├── .htaccess
│   ├── favicon.ico
│   └── robots.txt
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/ (if composer install was run)
└── .env
```

## Support
If you encounter any issues, check the Laravel logs in `storage/logs/laravel.log`
