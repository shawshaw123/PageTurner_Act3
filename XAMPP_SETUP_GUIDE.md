# XAMPP Setup Guide for Laboratory Activity 6

## 🚀 XAMPP-Specific Setup Instructions

### Prerequisites
- XAMPP installed (Apache + MySQL + PHP)
- PHP version 8.2+ (check with `php -v`)
- Composer installed globally

### 📋 Step-by-Step Setup

#### 1. Project Setup
```bash
# Navigate to your XAMPP htdocs directory
cd C:\xampp\htdocs

# Your project should already be in Activity4 folder
cd Activity4

# Install dependencies
composer install

# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

#### 2. Database Configuration
Edit your `.env` file with XAMPP MySQL settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pageturner_bookstore
DB_USERNAME=root
DB_PASSWORD=

# For XAMPP, typically:
# DB_HOST=localhost
# DB_USERNAME=root
# DB_PASSWORD="" (empty)
```

#### 3. Create Database
Using XAMPP phpMyAdmin:
1. Open `http://localhost/phpmyadmin`
2. Create new database: `pageturner_bookstore`
3. Use default collation: `utf8mb4_unicode_ci`

#### 4. Run Migrations
```bash
php artisan migrate
```

#### 5. XAMPP Apache Configuration
Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/Activity4/public"
    ServerName pageturner.local
    <Directory "C:/xampp/htdocs/Activity4/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add to `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 pageturner.local
```

#### 6. Start XAMPP Services
1. Open XAMPP Control Panel
2. Start Apache
3. Start MySQL
4. Access via: `http://pageturner.local` or `http://localhost/Activity4`

### 🔧 XAMPP-Specific Configurations

#### PHP Configuration (php.ini)
Edit `C:\xampp\php\php.ini`:

```ini
; Increase memory limits for large imports
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

; File upload settings
upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 20

; Enable required extensions
extension=zip
extension=gd
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_mysql
```

#### MySQL Configuration (my.ini)
Edit `C:\xampp\mysql\bin\my.ini`:

```ini
# Increase query cache for better performance
query_cache_type = 1
query_cache_size = 64M

# Increase max packet size for large imports
max_allowed_packet = 64M

# InnoDB settings for better performance
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
```

### 🗂️ Directory Permissions for XAMPP
Create these directories and set permissions:

```bash
# In XAMPP, use Windows file permissions or run as administrator
mkdir storage\app\imports
mkdir storage\app\exports
mkdir storage\app\backups
mkdir storage\app\audit-archives
mkdir storage\app\reports

# Set write permissions (right-click in Windows Explorer)
# Properties > Security > Edit > Add "Everyone" with Full Control
```

### 📊 XAMPP Performance Optimization

#### 1. Enable OPcache
In `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

#### 2. MySQL Optimization
```sql
-- Run these queries in phpMyAdmin for better performance
SET GLOBAL innodb_buffer_pool_size = 268435456; -- 256MB
SET GLOBAL query_cache_size = 67108864; -- 64MB
SET GLOBAL innodb_log_file_size = 67108864; -- 64MB
```

### 🚀 Running the Application

#### Option 1: Using PHP Development Server
```bash
php artisan serve --host=127.0.0.1 --port=8000
```
Access: `http://localhost:8000`

#### Option 2: Using XAMPP Apache
1. Make sure Apache is running in XAMPP Control Panel
2. Access: `http://localhost/Activity4` or `http://pageturner.local`

### 🔄 Queue Worker Setup for XAMPP

#### Method 1: Manual Queue Worker
```bash
php artisan queue:work --timeout=0 --tries=3
```

#### Method 2: Using Windows Task Scheduler
1. Open Task Scheduler
2. Create Basic Task
3. Trigger: Daily at system startup
4. Action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `artisan queue:work --timeout=0 --tries=3`
   - Start in: `C:\xampp\htdocs\Activity4`

#### Method 3: Background Process
```batch
@echo off
cd /d C:\xampp\htdocs\Activity4
start /B php artisan queue:work --timeout=0 --tries=3
```

Save as `start_queue.bat` and run it.

### 📅 Cron Job Alternative for XAMPP

Windows doesn't have cron, use Task Scheduler instead:

1. Open Task Scheduler
2. Create Basic Task named "Laravel Scheduler"
3. Trigger: Daily, recur every 1 minute
4. Action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `artisan schedule:run`
   - Start in: `C:\xampp\htdocs\Activity4`
5. Conditions: Run whether user is logged on or not

### 🧪 Testing Your XAMPP Setup

#### 1. Run Setup Script
```bash
php setup_lab6.php
```

#### 2. Run Feature Tests
```bash
php test_lab6_features.php
```

#### 3. Test Import/Export
- Access: `http://localhost/Activity4/admin/import`
- Upload a test Excel file
- Check progress in real-time

#### 4. Test Backup
- Access: `http://localhost/Activity4/admin/backup`
- Click "Run Backup Now"
- Check `storage\app\backups` folder

### 🔍 XAMPP Troubleshooting

#### Common Issues and Solutions

**Issue 1: Permission Denied**
```bash
# Solution: Run XAMPP as Administrator
# Or set folder permissions in Windows Explorer
```

**Issue 2: MySQL Connection Failed**
```bash
# Check if MySQL is running in XAMPP Control Panel
# Verify database exists in phpMyAdmin
# Check .env database credentials
```

**Issue 3: File Upload Too Large**
```ini
# Edit C:\xampp\php\php.ini
upload_max_filesize = 50M
post_max_size = 50M
# Restart Apache
```

**Issue 4: Memory Limit Exceeded**
```ini
# Edit C:\xampp\php\php.ini
memory_limit = 512M
# Restart Apache
```

**Issue 5: Queue Worker Not Running**
```bash
# Manually start queue worker
php artisan queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### 📁 XAMPP Directory Structure

```
C:\xampp\htdocs\Activity4\
├── app\                    # Application code
├── bootstrap\              # Bootstrap files
├── config\                 # Configuration files
├── database\               # Migrations and seeders
├── public\                 # Web root
├── resources\              # Views and assets
├── routes\                 # Route definitions
├── storage\                # Storage (app, framework, logs)
├── tests\                  # Test files
├── vendor\                 # Composer dependencies
├── .env                    # Environment configuration
├── artisan                 # Laravel command line
└── composer.json           # Dependencies
```

### 🚀 Performance Tips for XAMPP

1. **Enable OPcache**: Already configured above
2. **Use SSD**: If possible, move XAMPP to SSD
3. **Increase PHP Memory**: Set to 512MB or higher
4. **Optimize MySQL**: Use the provided my.ini settings
5. **Enable Gzip Compression**: In Apache configuration
6. **Use Browser Caching**: In .htaccess file

### 📞 XAMPP Ports Used

- **Apache**: 80 (HTTP), 443 (HTTPS)
- **MySQL**: 3306
- **PHP Development Server**: 8000 (if used)

Make sure these ports are not blocked by firewall or antivirus software.

---

## 🎉 Ready to Go!

Your Laboratory Activity 6 is now fully configured for XAMPP! 

**Quick Start:**
1. Start XAMPP (Apache + MySQL)
2. Run `php artisan serve` or use Apache
3. Access `http://localhost/Activity4`
4. Login as admin: `admin@pageturner.com` / `admin123`
5. Explore all the new Lab 6 features!

**Remember to run the queue worker for background processing:**
```bash
php artisan queue:work --timeout=0
```

Enjoy your Laboratory Activity 6 implementation! 🚀
