# Production Deployment Guide & Pre-Launch Checklist
**Hospital Management System (HMS) — Meghna Clinic & Nursing Home, Dinajpur, Bangladesh**  
**Target Environment:** Linux VPS (Ubuntu 22.04/24.04 LTS) with Nginx / Apache, PHP 8.2+ FPM, and MySQL 8.0+

---

## 1. System Requirements & Prerequisites

| Component | Minimum Specification | Recommended Production Spec |
|---|---|---|
| **OS** | Ubuntu 22.04 LTS / Debian 12 | Ubuntu 24.04 LTS 64-bit |
| **CPU / RAM** | 2 vCPU, 4 GB RAM | 4 vCPU, 8 GB RAM |
| **Web Server** | Nginx 1.22+ or Apache 2.4+ | Nginx (event-driven, low memory footprint) |
| **PHP** | PHP 8.2 or 8.3 CLI + FPM | PHP 8.2/8.3 FPM with Zend OPcache enabled |
| **PHP Extensions** | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `intl`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `zip` |
| **Database** | MySQL 8.0+ or MariaDB 10.11+ | MySQL 8.0 (InnoDB engine, `utf8mb4_unicode_ci`) |
| **Process Manager** | Supervisor (for queues) | Supervisor 4.2+ |
| **SSL Certificate** | Let's Encrypt (Certbot) | Automated renewal via Certbot |

---

## 2. Server Provisioning & Package Installation

Run the following commands on a clean Ubuntu VPS:

```bash
# 1. Update system packages
sudo apt update && sudo apt upgrade -y

# 2. Install Nginx, MySQL, Git, Curl, Unzip, Supervisor
sudo apt install -y nginx mysql-server git curl unzip supervisor ufw certbot python3-certbot-nginx

# 3. Add PHP 8.2 PPA repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# 4. Install PHP 8.2 and required extensions
sudo apt install -y php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath \
    php8.2-curl php8.2-zip php8.2-intl php8.2-gd php8.2-opcache

# 5. Install Composer (PHP package manager)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# 6. Install Node.js 20 LTS (for compiling front-end Vite assets)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 3. Database Setup & Hardening

Log in to MySQL as root and create the isolated database and dedicated user:

```bash
sudo mysql -u root
```

Execute the following SQL commands:

```sql
-- Create dedicated database with utf8mb4 encoding
CREATE DATABASE mcnh_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated non-root user (replace with strong password)
CREATE USER 'mcnh_dbuser'@'127.0.0.1' IDENTIFIED BY 'STRONG_SECRET_PASSWORD_HERE';

-- Grant required permissions
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES, CREATE TEMPORARY TABLES, REFERENCES 
ON mcnh_production.* TO 'mcnh_dbuser'@'127.0.0.1';

-- Apply privilege changes
FLUSH PRIVILEGES;
EXIT;
```

---

## 4. Application Deployment & Storage Configuration

Clone the repository to `/var/www/hospital-management-system`:

```bash
# 1. Clone repository
sudo git clone -b hospital-management-system https://github.com/MdSadiarRahman/Hospital_Management_System.git /var/www/hospital-management-system

# 2. Enter project directory
cd /var/www/hospital-management-system

# 3. Create production environment file
cp .env.production.example .env

# 4. Edit .env with production database credentials and domain
nano .env
```

Key variables to configure in `/var/www/hospital-management-system/.env`:
```ini
APP_NAME="Meghna Clinic & Nursing Home"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://meghnaclinic.com
FORCE_HTTPS=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mcnh_production
DB_USERNAME=mcnh_dbuser
DB_PASSWORD=STRONG_SECRET_PASSWORD_HERE

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

LOG_CHANNEL=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=14
```

### Install Dependencies & Build Assets:
```bash
# 5. Install Composer PHP dependencies (no dev packages, optimized autoloader)
composer install --no-dev --optimize-autoloader

# 6. Generate unique application encryption key
php artisan key:generate --force

# 7. Install and compile front-end assets
npm ci
npm run build

# 8. Create secure medical report directory
mkdir -p storage/app/secure_medical_reports

# 9. Set strict Linux ownership and directory permissions
sudo chown -R www-data:www-data /var/www/hospital-management-system
sudo find /var/www/hospital-management-system -type f -exec chmod 644 {} \;
sudo find /var/www/hospital-management-system -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/hospital-management-system/storage /var/www/hospital-management-system/bootstrap/cache
```

---

## 5. Web Server Configuration

### Option A: Nginx (Recommended)

Create `/etc/nginx/sites-available/meghnaclinic.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name meghnaclinic.com www.meghnaclinic.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name meghnaclinic.com www.meghnaclinic.com;

    root /var/www/hospital-management-system/public;
    index index.php index.html;

    # SSL Certificate Configuration (Certbot will populate)
    # ssl_certificate /etc/letsencrypt/live/meghnaclinic.com/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/meghnaclinic.com/privkey.pem;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net;" always;

    # Maximum upload size for diagnostic PDFs / medical scans (15MB)
    client_max_body_size 15M;

    # Character set
    charset utf-8;

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Block access to hidden files (.env, .git)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Block direct access to storage and sensitive paths
    location ^~ /storage/app/ {
        deny all;
        return 404;
    }

    # PHP-FPM FastCGI Handler
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    # Static Asset Caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }

    access_log /var/log/nginx/mcnh_access.log;
    error_log /var/log/nginx/mcnh_error.log;
}
```

Enable site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/meghnaclinic.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# Obtain free SSL certificate with automated redirection
sudo certbot --nginx -d meghnaclinic.com -d www.meghnaclinic.com
```

---

## 6. Background Workers & Queue Management (Supervisor)

The HMS handles audit logging, system alerts, and notification dispatches asynchronously via the database queue.

Create `/etc/supervisor/conf.d/mcnh-worker.conf`:

```ini
[program:mcnh-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hospital-management-system/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hospital-management-system/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Start and verify workers:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mcnh-worker:*
sudo supervisorctl status
```

---

## 7. Cron Scheduled Tasks

Edit the web server user's crontab:
```bash
sudo crontab -u www-data -e
```

Add the Laravel Scheduler entry:
```cron
* * * * * cd /var/www/hospital-management-system && php artisan schedule:run >> /dev/null 2>&1
```

---

## 8. Automated Daily Backup Strategy

Create backup directory and shell script:
```bash
sudo mkdir -p /opt/backups/mcnh
sudo chmod 700 /opt/backups/mcnh
```

Create `/opt/scripts/backup-mcnh.sh`:
```bash
#!/bin/bash
set -e

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/opt/backups/mcnh"
DB_NAME="mcnh_production"
DB_USER="mcnh_dbuser"
DB_PASS="STRONG_SECRET_PASSWORD_HERE"

# 1. Dump MySQL database (single-transaction for InnoDB consistency)
mysqldump -u "$DB_USER" -p"$DB_PASS" --single-transaction --quick --routines --triggers "$DB_NAME" | gzip > "$BACKUP_DIR/db_${DATE}.sql.gz"

# 2. Archive secure medical diagnostic reports
tar -czf "$BACKUP_DIR/reports_${DATE}.tar.gz" -C /var/www/hospital-management-system/storage/app secure_medical_reports

# 3. Set strict permissions on backups
chmod 600 $BACKUP_DIR/*

# 4. Retain backups for 30 days, purge older
find "$BACKUP_DIR" -type f -mtime +30 -delete

echo "[$(date)] HMS Backup completed successfully: db_${DATE}.sql.gz and reports_${DATE}.tar.gz" >> /var/log/mcnh_backup.log
```

Make executable and add to root crontab (runs nightly at 2:30 AM):
```bash
sudo chmod +x /opt/scripts/backup-mcnh.sh
sudo crontab -e
```
Add:
```cron
30 2 * * * /opt/scripts/backup-mcnh.sh
```

---

## 9. Migration & Zero-Downtime Deployment Script

Create `/var/www/deploy.sh`:

```bash
#!/bin/bash
set -e

echo "Starting deployment at $(date)..."
cd /var/www/hospital-management-system

# 1. Put application in maintenance mode
php artisan down --render="errors.503" --secret="bypass-deploy-secret-2026"

# 2. Pull latest code from production branch
git fetch origin hospital-management-system
git reset --hard origin/hospital-management-system

# 3. Install/update Composer dependencies
composer install --no-dev --prefer-dist --optimize-autoloader

# 4. Compile frontend assets
npm ci
npm run build

# 5. Run database migrations safely
php artisan migrate --force

# 6. Clear and rebuild production caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart queue workers so new code takes effect
sudo supervisorctl restart mcnh-worker:*

# 8. Reset permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 9. Bring application back online
php artisan up

echo "Deployment finished successfully at $(date)!"
```

---

## 10. Rollback Strategy

In the event of an unexpected regression during migration:

1. **Step 1: Put Application in Maintenance Mode**
   ```bash
   php artisan down --secret="rollback-mode"
   ```
2. **Step 2: Rollback Database Migration (if applicable)**
   ```bash
   php artisan migrate:rollback --step=1 --force
   ```
3. **Step 3: If Database Corruption Occurred, Restore Nightly Backup**
   ```bash
   gunzip < /opt/backups/mcnh/db_YYYYMMDD_HHMMSS.sql.gz | mysql -u mcnh_dbuser -p mcnh_production
   ```
4. **Step 4: Revert Git Commit to Previous Stable Release**
   ```bash
   git reset --hard PREVIOUS_STABLE_COMMIT_HASH
   composer install --no-dev --optimize-autoloader
   npm run build
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   sudo supervisorctl restart mcnh-worker:*
   php artisan up
   ```

---

## 11. Pre-Launch Verification Checklist

Before opening the portal to hospital staff and public patients, verify all items:

- [ ] **1. `APP_ENV=production`**: Set in `.env`.
- [ ] **2. `APP_DEBUG=false`**: Verified false; stack traces are never shown to patients or staff.
- [ ] **3. Application Key**: Generated via `php artisan key:generate`.
- [ ] **4. HTTPS Enforcement**: SSL certificate installed, `FORCE_HTTPS=true`, HTTP automatically redirects to HTTPS.
- [ ] **5. Cookie Security**: `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax`.
- [ ] **6. Production Caching Active**: Verified `php artisan config:cache`, `route:cache`, and `view:cache` executed.
- [ ] **7. Storage Permissions**: `storage/` and `bootstrap/cache/` writable by `www-data` (775).
- [ ] **8. Non-Public Medical File Isolation**: `storage/app/secure_medical_reports/` directory is blocked from direct HTTP access; reports stream solely through authenticated controller checks.
- [ ] **9. Rate Limiting Active**: Tested 5 consecutive failed logins trigger throttle lockout.
- [ ] **10. Immutable Audit Ledger**: Verified `$log->update()` and `$log->delete()` throw exceptions.
- [ ] **11. Sensitive Data Redacted**: Passwords, API tokens, and PINs are masked in audit logs.
- [ ] **12. Zero IDOR Vulnerabilities**: URL parameter tampering on prescriptions, lab reports, and billing slips is blocked (403 Forbidden).
- [ ] **13. Database Engine**: Verified InnoDB engine with transaction support and pessimistic row-locking on bed assignments and payments.
- [ ] **14. Scheduled Cron Active**: Crontab entry `* * * * * ... artisan schedule:run` active under `www-data`.
- [ ] **15. Supervisor Workers Running**: `supervisorctl status` confirms `mcnh-worker` is `RUNNING`.
- [ ] **16. Automated Nightly Backups**: Backup script scheduled in crontab at 2:30 AM and tested with dry-run dump.
- [ ] **17. Mail Server Credentials**: SMTP gateway configured and test email sent.
- [ ] **18. Default Credentials Changed**: All seeded initial administrative passwords changed to secure random production passphrases.
- [ ] **19. Front-end Assets Compiled**: Production assets exist in `public/build/` with valid `manifest.json`.
- [ ] **20. All 336 Tests Passing**: Verified with `php artisan test` before release.

