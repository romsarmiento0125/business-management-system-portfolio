FROM php:8.2-fpm

# Install system dependencies and PHP extensions needed by CodeIgniter 4
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
    zip \
    curl \
    supervisor \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) gd pdo_mysql mysqli mbstring xml bcmath intl zip \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files and install dependencies first (for better build caching)
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copy the rest of the application (including public/)
COPY . . 

# Set correct ownership for writable folder
RUN chown -R www-data:www-data /var/www/html/writable

# Copy Nginx config
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Remove default Nginx site config to avoid conflicts
RUN rm -f /etc/nginx/sites-enabled/default

# Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Ensure www-data owns the app directory inside container (for writable folders)
RUN usermod -u 1000 www-data || true
RUN mkdir -p /var/www/html && chown -R www-data:www-data /var/www/html

EXPOSE 80 443

CMD ["/usr/bin/supervisord"]
