FROM php:8.0-fpm AS builder

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd xml opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer.json and composer.lock
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --no-scripts --no-autoloader

# Copy package.json and package-lock.json
COPY package.json package-lock.json ./

# Install Node.js dependencies
RUN npm ci

# Copy the rest of the application code
COPY . .

# Generate optimized autoload files
RUN composer dump-autoload --no-dev --optimize

# Build frontend assets
RUN npm run production

# Remove node_modules to reduce image size
RUN rm -rf node_modules

# Production image
FROM php:8.0-fpm

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd xml opcache

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configure OPcache
RUN echo 'opcache.memory_consumption=128' >> "$PHP_INI_DIR/php.ini" \
    && echo 'opcache.interned_strings_buffer=8' >> "$PHP_INI_DIR/php.ini" \
    && echo 'opcache.max_accelerated_files=4000' >> "$PHP_INI_DIR/php.ini" \
    && echo 'opcache.revalidate_freq=2' >> "$PHP_INI_DIR/php.ini" \
    && echo 'opcache.fast_shutdown=1' >> "$PHP_INI_DIR/php.ini" \
    && echo 'opcache.enable_cli=1' >> "$PHP_INI_DIR/php.ini"

# Add user for Laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy application from builder stage
COPY --from=builder --chown=www:www /var/www/html /var/www/html

# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
