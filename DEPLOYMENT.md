# Air System - Deployment Guide

## 📋 Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Server Requirements](#server-requirements)
3. [Installation Steps](#installation-steps)
4. [Configuration](#configuration)
5. [Security Measures](#security-measures)
6. [Post-Deployment Tasks](#post-deployment-tasks)
7. [Troubleshooting](#troubleshooting)
8. [Maintenance](#maintenance)

---

## ✅ Pre-Deployment Checklist

Before deploying to production, ensure the following have been completed:

- [x] Environment set to `production` in `index.php`
- [x] Encryption key generated and set in `application/config/config.php`
- [x] CSRF protection enabled
- [x] Base URL configured for auto-detection
- [x] `.htaccess` file created for URL rewriting and security
- [x] Session storage path configured
- [x] `.env.example` created for environment variables
- [x] `robots.txt` created for SEO management
- [ ] Production database credentials configured
- [ ] SSL/TLS certificate installed
- [ ] Backup system configured
- [ ] Error logging configured
- [ ] File permissions set correctly

---

## 🖥️ Server Requirements

### Minimum Requirements

- **PHP Version:** 5.6 or newer (7.4+ recommended)
- **Web Server:** Apache 2.4+ or Nginx
- **Database:** MySQL 5.6+ or MariaDB 10.0+
- **PHP Extensions:**
  - `mysqli` or `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `json`
  - `xml`
  - `gd` or `imagick` (for image processing)
  - `fileinfo`
  - `zip`

### Recommended Server Configuration

```ini
memory_limit = 128M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
display_errors = Off
log_errors = On
error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
```

### Apache Modules Required

- `mod_rewrite` (for clean URLs)
- `mod_headers` (for security headers)
- `mod_expires` (for browser caching)
- `mod_deflate` (for compression)

---

## 📦 Installation Steps

### 1. Upload Files to Server

```bash
# Using FTP, SFTP, or SCP, upload all files to your server
# Recommended location: /var/www/html/air-system/ or your web root

# Or clone from Git repository
git clone https://github.com/dyaldy/air-system.git
cd air-system
```

### 2. Install Dependencies

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# If Composer is not installed, install it first:
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

### 3. Set File Permissions

```bash
# Make writable directories
chmod 755 application/cache
chmod 755 application/cache/sessions
chmod 755 application/logs
chmod 755 uploads

# Secure sensitive files
chmod 644 application/config/config.php
chmod 644 application/config/database.php
chmod 644 .htaccess
chmod 600 .env  # if using .env file

# Ensure index.php is readable
chmod 644 index.php
```

### 4. Create Database

```sql
-- Create database
CREATE DATABASE air_system CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Create database user
CREATE USER 'air_system_user'@'localhost' IDENTIFIED BY 'strong_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON air_system.* TO 'air_system_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;
```

### 5. Import Database Schema

```bash
# Import your database schema
mysql -u air_system_user -p air_system < database/schema.sql

# Or use phpMyAdmin to import the SQL file
```

---

## ⚙️ Configuration

### 1. Update Database Configuration

Edit `application/config/database.php`:

```php
$db['default'] = array(
    'dsn'      => '',
    'hostname' => 'localhost',
    'username' => 'air_system_user',
    'password' => 'your_secure_password',
    'database' => 'air_system',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => FALSE,  // MUST be FALSE in production
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt'  => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => FALSE  // Set to FALSE in production
);
```

### 2. Update Base URL (if needed)

The `.htaccess` file is configured for auto-detection. If you need a fixed URL:

```php
// In application/config/config.php
$config['base_url'] = 'https://yourdomain.com/air-system/';
```

### 3. Verify Encryption Key

Ensure a strong encryption key is set in `application/config/config.php`:

```php
$config['encryption_key'] = 'a7s9d8f7g6h5j4k3l2m1n0p9o8i7u6y5';
```

### 4. Configure Error Logging

Update `application/config/config.php`:

```php
$config['log_threshold'] = 1;  // 0=Disabled, 1=Error, 2=Debug, 3=Info, 4=All
$config['log_path'] = APPPATH . 'logs/';
```

### 5. Update .htaccess Base Path

If your application is in a subdirectory, update the `RewriteBase` in `.htaccess`:

```apache
# If at root:
RewriteBase /

# If in subdirectory:
RewriteBase /air-system/
```

---

## 🔒 Security Measures

### 1. SSL/TLS Certificate

```bash
# Install Let's Encrypt certificate (free)
sudo apt-get install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### 2. Force HTTPS

Add to `.htaccess` (above existing rules):

```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. Secure File Uploads

Ensure `uploads/` directory has an `.htaccess` file:

```apache
# In uploads/.htaccess
Options -Indexes
php_flag engine off
```

### 4. Database Security

- Use strong passwords (16+ characters, mixed case, numbers, symbols)
- Limit database user privileges to only what's needed
- Use separate database users for different environments
- Enable MySQL/MariaDB firewall rules

### 5. Regular Security Updates

```bash
# Keep system packages updated
sudo apt update && sudo apt upgrade

# Keep Composer dependencies updated
composer update --no-dev

# Keep CodeIgniter framework updated
# Check: https://github.com/bcit-ci/CodeIgniter/releases
```

---

## 📊 Post-Deployment Tasks

### 1. Test the Application

- [ ] Visit the application URL
- [ ] Test login functionality
- [ ] Test all major features
- [ ] Verify database connections
- [ ] Check error logs
- [ ] Test file uploads
- [ ] Verify email notifications (if applicable)

### 2. Configure Backups

```bash
# Database backup script (add to cron)
#!/bin/bash
BACKUP_DIR="/backups/air-system"
DB_NAME="air_system"
DB_USER="air_system_user"
DB_PASS="your_password"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 30 days of backups
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
```

Add to crontab:

```bash
# Daily backup at 2 AM
0 2 * * * /path/to/backup-script.sh
```

### 3. Set Up Monitoring

- Configure server monitoring (CPU, RAM, disk space)
- Set up application error notifications
- Monitor database performance
- Track uptime

### 4. Configure Log Rotation

Create `/etc/logrotate.d/air-system`:

```
/var/www/html/air-system/application/logs/*.php {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    missingok
    create 0644 www-data www-data
}
```

---

## 🔧 Troubleshooting

### Issue: 500 Internal Server Error

**Solution:**

- Check Apache error logs: `tail -f /var/log/apache2/error.log`
- Verify `.htaccess` syntax
- Check file permissions
- Enable PHP error logging

### Issue: Database Connection Failed

**Solution:**

- Verify database credentials in `database.php`
- Check if MySQL/MariaDB service is running
- Test database connection: `mysql -u username -p`
- Verify database user privileges

### Issue: Session Not Working

**Solution:**

- Check session save path exists and is writable
- Verify `application/cache/sessions/` directory permissions (755)
- Clear browser cookies
- Check session configuration in `config.php`

### Issue: CSRF Token Mismatch

**Solution:**

- Ensure CSRF protection is properly configured
- Check that forms include CSRF token
- Verify session is working correctly
- Clear browser cache and cookies

### Issue: Clean URLs Not Working

**Solution:**

- Verify `mod_rewrite` is enabled: `sudo a2enmod rewrite`
- Check `.htaccess` file exists and is readable
- Verify Apache allows `.htaccess` overrides
- Restart Apache: `sudo systemctl restart apache2`

---

## 🛠️ Maintenance

### Regular Tasks

- **Daily:** Monitor error logs
- **Weekly:** Check disk space and server resources
- **Monthly:** Review and update dependencies
- **Quarterly:** Security audit and penetration testing
- **Annually:** SSL certificate renewal (if not automated)

### Update Process

1. Backup database and files
2. Test updates in staging environment
3. Apply updates during low-traffic period
4. Monitor logs after deployment
5. Keep rollback plan ready

### Backup Strategy

- **Database:** Daily automated backups
- **Files:** Weekly full backups
- **Retention:** Keep 30 days of daily backups
- **Off-site:** Store backups in separate location
- **Test:** Regularly test backup restoration

---

## 📞 Support

For issues or questions:

- Check logs: `application/logs/`
- Review CodeIgniter documentation: https://codeigniter.com/userguide3/
- Contact system administrator

---

## 📝 Notes

### Important Security Reminders

1. **NEVER** commit `.env` files to version control
2. **ALWAYS** use strong, unique passwords
3. **REGULARLY** update all software components
4. **MONITOR** logs for suspicious activity
5. **BACKUP** data regularly

### Production Checklist

- [ ] All credentials changed from defaults
- [ ] HTTPS enabled and working
- [ ] Database backed up
- [ ] Error monitoring configured
- [ ] File permissions set correctly
- [ ] Backups tested and working
- [ ] Documentation updated
- [ ] Team trained on deployment process

---

**Last Updated:** October 13, 2025  
**Version:** 1.0.0  
**Maintained by:** Apparel One Indonesia
