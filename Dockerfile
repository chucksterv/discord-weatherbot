# Use an official PHP image
FROM php:8.3-cli

# Install system dependencies and PDO MySQL
RUN docker-php-ext-install pdo pdo_pgsql

# Set working directory
WORKDIR /app

# Copy composer files first (better caching)
COPY composer.json ./

# Install dependencies
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && composer install --no-dev --optimize-autoloader

# Copy app files
COPY . .

