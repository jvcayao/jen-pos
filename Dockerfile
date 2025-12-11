#Dockerfile Example on running PHP Laravel app using Apache web server 

FROM php:8.4-apache

# Install necessary libraries
RUN apt-get update && apt-get install -y \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y libzip-dev libonig-dev gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils librsvg2-bin fswatch ffmpeg nano  
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg 
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_22.x nodistro main" > /etc/apt/sources.list.d/nodesource.list 
RUN apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm
    

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip 

# Copy Laravel application
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install
RUN npm ci
RUN npm run build

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www/html


COPY .env.example .env
RUN php artisan key:generate
RUN php artisan migrate:fresh --force
RUN php artisan db:seed --force
RUN php artisan db:seed --force --class=MenuSeeder
RUN php artisan db:seed --force --class=ProductSeeder


# Expose port 80
EXPOSE 80

# Adjusting Apache configurations
RUN a2enmod rewrite
COPY ./docker/apache-config.conf /etc/apache2/sites-available/000-default.conf