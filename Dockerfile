FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
  git curl zip unzip libzip-dev libpng-dev libonig-dev \
  libcurl4-openssl-dev pkg-config libssl-dev supervisor redis-tools iputils-ping

# Apache + PHP extensions
RUN a2enmod rewrite

# Redis PHP extension
RUN docker-php-ext-install pdo pdo_mysql zip gd pcntl \
  && pecl install redis && docker-php-ext-enable redis

# Copy custom php.ini
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Laravel code into container
COPY . /var/www/html
COPY .env /var/www/html/.env

# Working directory
WORKDIR /var/www/html

# Apache config
COPY docker/apache-vhost/vhost.conf /etc/apache2/sites-available/000-default.conf

# Supervisor config
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader && \
  mkdir -p /var/www/html/storage/app/public/temp_exports && \
  mkdir -p /var/www/html/storage/app/public/uploads && \
  chown -R www-data:www-data /var/www/html && \
  chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Install Chrome + dependencies for Browsershot/dompdf
RUN apt-get update \
  && apt-get install -y \
  chromium \
  chromium-driver \
  fonts-liberation \
  libnss3 \
  libatk1.0-0 \
  libatk-bridge2.0-0 \
  libgtk-3-0 \
  libx11-xcb1 \
  libxcb1 \
  libxcomposite1 \
  libxdamage1 \
  libxrandr2 \
  libgbm1 \
  libasound2 \
  libxshmfence1 \
  wget \
  nodejs \
  && rm -rf /var/lib/apt/lists/*

# Create log directories and set permissions
RUN mkdir -p /var/log/apache2 /var/log/horizon /var/log/reverb /var/log/scheduler /var/log/supervisord \
  && chown -R www-data:www-data /var/log/apache2 /var/log/horizon /var/log/reverb /var/log/scheduler /var/log/supervisord \
  && chmod -R 755 /var/log

# Laravel optimization commands
RUN php artisan config:cache \
  && php artisan route:cache \
  && php artisan view:cache

EXPOSE 80 6001

# Start supervisor (which manages nginx + php-fpm + reverb + horizon)
CMD ["/usr/bin/supervisord"]
