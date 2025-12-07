FROM php:8.2-apache

# --------------------------------------------------
# 1. System dependencies
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
  git curl zip unzip libzip-dev libpng-dev libonig-dev \
  libcurl4-openssl-dev pkg-config libssl-dev supervisor redis-tools iputils-ping \
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
  && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# 2. Apache + PHP extensions
# --------------------------------------------------
RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql zip gd pcntl \
  && pecl install redis \
  && docker-php-ext-enable redis

# Custom php.ini
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# --------------------------------------------------
# 3. App code (already built in CI: vendor + public/build)
# --------------------------------------------------
COPY . /var/www/html
COPY .env /var/www/html/.env

WORKDIR /var/www/html

# Apache vhost
COPY docker/apache-vhost/vhost.conf /etc/apache2/sites-available/000-default.conf

# Supervisor config (your file)
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# --------------------------------------------------
# 4. Create/cache dirs (storage handled by volume + storage-link)
# --------------------------------------------------
RUN mkdir -p /var/www/html/bootstrap/cache \
  && chown -R www-data:www-data /var/www/html/bootstrap/cache \
  && chmod -R 755 /var/www/html/bootstrap/cache

# Log directories
RUN mkdir -p /var/log/apache2 /var/log/horizon /var/log/reverb /var/log/scheduler /var/log/supervisord \
  && chown -R www-data:www-data /var/log \
  && chmod -R 755 /var/log

# --------------------------------------------------
# 5. Laravel optimization (vendor already exists from CI)
# --------------------------------------------------
RUN php artisan config:cache \
  && php artisan route:cache \
  && php artisan view:cache

EXPOSE 80 6001

# Supervisor will run storage-link, apache, reverb, horizon, scheduler
CMD ["/usr/bin/supervisord"]