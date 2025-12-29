#!/bin/bash
set -e

# ============================================================
# Server Setup Script for Ubuntu 22.04
# Installs: PHP 8.3, Nginx, Node.js 20, Composer, MySQL Client
# ============================================================

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Setting up Server for Laravel${NC}"
echo -e "${GREEN}========================================${NC}"

# Update system
echo -e "\n${YELLOW}[1/8] Updating system packages...${NC}"
sudo apt update && sudo apt upgrade -y

# Install essential packages
echo -e "\n${YELLOW}[2/8] Installing essential packages...${NC}"
sudo apt install -y \
    software-properties-common \
    curl \
    git \
    unzip \
    zip \
    acl \
    supervisor \
    certbot \
    python3-certbot-nginx

# Add PHP repository and install PHP 8.3
echo -e "\n${YELLOW}[3/8] Installing PHP 8.3...${NC}"
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y \
    php8.3-fpm \
    php8.3-cli \
    php8.3-common \
    php8.3-mysql \
    php8.3-pgsql \
    php8.3-sqlite3 \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-gd \
    php8.3-imagick \
    php8.3-redis \
    php8.3-opcache

# Configure PHP
echo -e "\n${YELLOW}[4/8] Configuring PHP...${NC}"
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 64M/' /etc/php/8.3/fpm/php.ini
sudo sed -i 's/post_max_size = 8M/post_max_size = 64M/' /etc/php/8.3/fpm/php.ini
sudo sed -i 's/memory_limit = 128M/memory_limit = 256M/' /etc/php/8.3/fpm/php.ini
sudo sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/8.3/fpm/php.ini

# Install Composer
echo -e "\n${YELLOW}[5/8] Installing Composer...${NC}"
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Node.js 20 LTS
echo -e "\n${YELLOW}[6/8] Installing Node.js 20...${NC}"
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
echo -e "\n${YELLOW}[7/8] Installing and configuring Nginx...${NC}"
sudo apt install -y nginx

# Create Nginx configuration for Laravel
sudo tee /etc/nginx/sites-available/sunbites-pos > /dev/null << 'NGINX_CONF'
server {
    listen 80;
    listen [::]:80;
    server_name _;
    root /var/www/sunbites-pos/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml application/javascript application/json;
    gzip_disable "MSIE [1-6]\.";
}
NGINX_CONF

# Enable site and disable default
sudo ln -sf /etc/nginx/sites-available/sunbites-pos /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Create application directory
echo -e "\n${YELLOW}[8/8] Setting up application directory...${NC}"
sudo mkdir -p /var/www/sunbites-pos
sudo chown -R ubuntu:ubuntu /var/www/sunbites-pos
sudo chmod -R 755 /var/www/sunbites-pos

# Create deploy user SSH directory for GitHub Actions
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Configure Supervisor for queue worker
sudo tee /etc/supervisor/conf.d/sunbites-worker.conf > /dev/null << 'SUPERVISOR_CONF'
[program:sunbites-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sunbites-pos/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=ubuntu
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/sunbites-pos/storage/logs/worker.log
stopwaitsecs=3600
SUPERVISOR_CONF

# Restart services
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
sudo nginx -t
sudo systemctl enable nginx
sudo systemctl enable php8.3-fpm

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}Server Setup Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "PHP Version: $(php -v | head -n 1)"
echo -e "Node Version: $(node -v)"
echo -e "NPM Version: $(npm -v)"
echo -e "Composer Version: $(composer -V)"
echo -e "Nginx Status: $(sudo systemctl is-active nginx)"
echo -e "\n${YELLOW}Next Steps:${NC}"
echo -e "1. Upload your application to /var/www/sunbites-pos"
echo -e "2. Create .env file with production settings"
echo -e "3. Run: composer install --optimize-autoloader --no-dev"
echo -e "4. Run: npm ci && npm run build"
echo -e "5. Run: php artisan migrate --force"
echo -e "6. Run: php artisan config:cache && php artisan route:cache && php artisan view:cache"
