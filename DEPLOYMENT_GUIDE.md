# Deployment Guide - AI Video Generator

## Table of Contents
1. [Server Requirements](#server-requirements)
2. [Apache Configuration](#apache-configuration)
3. [Nginx Configuration](#nginx-configuration)
4. [Database Setup](#database-setup)
5. [File Permissions](#file-permissions)
6. [Security Hardening](#security-hardening)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring & Logging](#monitoring--logging)

## Server Requirements

### Minimum Specifications
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 20GB SSD
- **OS**: Ubuntu 20.04 LTS / CentOS 8 / Debian 10+

### Required PHP Extensions
```bash
sudo apt-get install -y \
    php7.4 \
    php7.4-mysql \
    php7.4-pdo \
    php7.4-gd \
    php7.4-fileinfo \
    php7.4-mbstring \
    php7.4-curl \
    php7.4-xml
```

### MySQL Setup
```bash
sudo apt-get install mysql-server-8.0
sudo mysql_secure_installation
```

## Apache Configuration

### Install Apache
```bash
sudo apt-get install apache2
sudo a2enmod rewrite
sudo a2enmod ssl
```

### Virtual Host Configuration

Create `/etc/apache2/sites-available/ai-video-generator.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/ai-video-generator/public

    <Directory /var/www/ai-video-generator/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Security headers
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-XSS-Protection "1; mode=block"
    </Directory>

    <Directory /var/www/ai-video-generator/storage>
        Require all denied
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/ai-video-generator-error.log
    CustomLog ${APACHE_LOG_DIR}/ai-video-generator-access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/ai-video-generator/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/yourdomain.crt
    SSLCertificateKeyFile /etc/ssl/private/yourdomain.key

    # Same directives as port 80
    <Directory /var/www/ai-video-generator/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/ai-video-generator-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/ai-video-generator-ssl-access.log combined
</VirtualHost>
```

### Enable Site
```bash
sudo a2ensite ai-video-generator
sudo systemctl restart apache2
```

## Nginx Configuration

### Install Nginx
```bash
sudo apt-get install nginx
```

### Server Block Configuration

Create `/etc/nginx/sites-available/ai-video-generator`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    root /var/www/ai-video-generator/public;
    index index.php index.html;

    ssl_certificate /etc/ssl/certs/yourdomain.crt;
    ssl_certificate_key /etc/ssl/private/yourdomain.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Max upload size
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location /storage {
        deny all;
        return 404;
    }

    location ~ /\. {
        deny all;
    }

    access_log /var/log/nginx/ai-video-generator-access.log;
    error_log /var/log/nginx/ai-video-generator-error.log;
}
```

### Enable Site
```bash
sudo ln -s /etc/nginx/sites-available/ai-video-generator /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Database Setup

### Create Database
```bash
mysql -u root -p
```

```sql
CREATE DATABASE ai_video_generator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'aivideo'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON ai_video_generator.* TO 'aivideo'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Import Schema
```bash
mysql -u aivideo -p ai_video_generator < /var/www/ai-video-generator/database.sql
```

### Database Optimization
```sql
-- Add to /etc/mysql/mysql.conf.d/mysqld.cnf
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 32M
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

## File Permissions

```bash
cd /var/www/ai-video-generator

# Set ownership
sudo chown -R www-data:www-data .

# Set directory permissions
sudo find . -type d -exec chmod 755 {} \;

# Set file permissions
sudo find . -type f -exec chmod 644 {} \;

# Storage directories (writable)
sudo chmod -R 775 storage/
sudo chown -R www-data:www-data storage/

# Make console scripts executable
sudo chmod +x app/console/*.php
sudo chmod +x tests/*.php
```

## Security Hardening

### 1. Disable Directory Listing
Already configured in Apache/Nginx configs above (`Options -Indexes`)

### 2. Hide PHP Version
Add to `/etc/php/7.4/apache2/php.ini` or `/etc/php/7.4/fpm/php.ini`:
```ini
expose_php = Off
```

### 3. Disable Dangerous Functions
```ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
```

### 4. Set Upload Limits
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

### 5. Enable HTTPS Only
Set in `.env`:
```
SESSION_SECURE=true
```

### 6. Firewall Configuration
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 7. Fail2Ban (Brute Force Protection)
```bash
sudo apt-get install fail2ban

# Configure for Nginx/Apache
sudo nano /etc/fail2ban/jail.local
```

## Performance Optimization

### 1. Enable PHP OPcache
Add to `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 2. Enable Gzip Compression

**Apache:**
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript
</IfModule>
```

**Nginx:**
```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
```

### 3. Browser Caching

**Apache (.htaccess):**
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 4. Database Connection Pooling
Consider using ProxySQL or MySQL connection pooling

### 5. CDN Configuration
Upload static assets (CSS, JS, images) to CDN and update URLs

## Monitoring & Logging

### 1. Error Logging
Set in `.env`:
```
APP_ENV=production
APP_DEBUG=false
```

Monitor logs:
```bash
tail -f /var/log/apache2/ai-video-generator-error.log
# or
tail -f /var/log/nginx/ai-video-generator-error.log
```

### 2. Application Monitoring
Install monitoring tools:
- New Relic
- Datadog
- Sentry (for error tracking)

### 3. Server Monitoring
```bash
# Install monitoring tools
sudo apt-get install htop iotop nethogs

# Check system resources
htop
df -h
free -m
```

### 4. Database Monitoring
```sql
SHOW PROCESSLIST;
SHOW STATUS LIKE 'Threads_connected';
SHOW STATUS LIKE 'Slow_queries';
```

### 5. Log Rotation
Create `/etc/logrotate.d/ai-video-generator`:
```
/var/log/nginx/ai-video-generator-*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data adm
    sharedscripts
    postrotate
        /etc/init.d/nginx reload > /dev/null
    endscript
}
```

## Backup Strategy

### 1. Database Backup
```bash
# Daily backup script
#!/bin/bash
BACKUP_DIR="/backups/database"
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u aivideo -p'password' ai_video_generator | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -mtime +30 -delete
```

### 2. Files Backup
```bash
# Backup storage directory
tar -czf /backups/storage/storage_$(date +%Y%m%d).tar.gz /var/www/ai-video-generator/storage
```

### 3. Automated Backups (Cron)
```bash
# Add to crontab
0 2 * * * /path/to/backup-script.sh
```

## Cron Jobs

Add to crontab (`sudo crontab -e`):
```bash
# Process render jobs every 5 minutes
*/5 * * * * php /var/www/ai-video-generator/app/console/process-renders.php >> /var/log/render-processor.log 2>&1

# Database backup daily at 2 AM
0 2 * * * /path/to/backup-database.sh

# Clean old logs weekly
0 3 * * 0 find /var/log/ai-video-generator -mtime +30 -delete
```

## SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-apache
# or for Nginx
sudo apt-get install certbot python3-certbot-nginx

# Get certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
# or for Nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal (already set up by certbot)
sudo certbot renew --dry-run
```

## Post-Deployment Checklist

- [ ] Database imported successfully
- [ ] Environment variables configured
- [ ] File permissions set correctly
- [ ] Web server configured and running
- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] Cron jobs set up
- [ ] Backup strategy implemented
- [ ] Monitoring tools configured
- [ ] Error logging enabled
- [ ] Performance optimization applied
- [ ] Security hardening completed
- [ ] Application accessible via HTTPS
- [ ] Login works with demo credentials
- [ ] File upload works
- [ ] Background jobs processing
- [ ] Email notifications working (if configured)

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check Apache/Nginx error logs
   - Verify file permissions
   - Check PHP error logs

2. **Database Connection Failed**
   - Verify database credentials in `.env`
   - Check MySQL is running: `sudo systemctl status mysql`
   - Test connection: `mysql -u aivideo -p`

3. **File Upload Fails**
   - Check `storage/` permissions
   - Verify `upload_max_filesize` in `php.ini`
   - Check disk space: `df -h`

4. **Render Jobs Not Processing**
   - Verify cron job is running: `crontab -l`
   - Check render processor logs
   - Manually run: `php app/console/process-renders.php`

## Support

For deployment assistance, contact your system administrator or open an issue on GitHub.
