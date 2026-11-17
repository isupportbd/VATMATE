FROM php:8.2-fpm

# --------------------------------------------------
# System deps + nginx + puppeteer deps + postgres deps
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
  nginx supervisor \
  git curl zip unzip libzip-dev libpng-dev libonig-dev \
  libcurl4-openssl-dev pkg-config libssl-dev \
  redis-tools iputils-ping \
  chromium chromium-driver wget \
  fonts-liberation fonts-dejavu-core fonts-dejavu-extra fonts-symbola \
  libnss3 libatk1.0-0 libatk-bridge2.0-0 libgtk-3-0 \
  libx11-xcb1 libxcb1 libxcomposite1 libxdamage1 libxrandr2 libgbm1 \
  libasound2 libxshmfence1 \
  nodejs npm \
  libpq-dev postgresql-client \
  && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# PHP extensions (pdo, pdo_pgsql, redis, etc.)
# --------------------------------------------------
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql zip gd pcntl \
  && pecl install redis \
  && docker-php-ext-enable redis

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# Application Code & Build
# --------------------------------------------------
WORKDIR /var/www/html
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies & build frontend assets
RUN npm ci || npm install \
  && npm run build \
  && rm -rf node_modules

# Puppeteer cache
ENV PUPPETEER_CACHE_DIR=/var/www/.cache/puppeteer
RUN mkdir -p $PUPPETEER_CACHE_DIR \
  && npx puppeteer browsers install chrome-headless-shell

# --------------------------------------------------
# Nginx config
# --------------------------------------------------
COPY docker/nginx/app.conf /etc/nginx/conf.d/default.conf
RUN rm -f /etc/nginx/sites-enabled/default || true

# --------------------------------------------------
# Supervisor config
# --------------------------------------------------
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# --------------------------------------------------
# Permissions & Directories
# --------------------------------------------------
RUN mkdir -p bootstrap/cache storage/framework/sessions storage/framework/views storage/framework/cache \
  && chown -R www-data:www-data bootstrap/cache storage \
  && chmod -R 775 bootstrap/cache storage \
  && mkdir -p /var/log/nginx /var/log/horizon /var/log/reverb /var/log/scheduler /var/log/supervisord

EXPOSE 80 6001
CMD ["/usr/bin/supervisord"]
