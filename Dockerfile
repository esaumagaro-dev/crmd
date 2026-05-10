# Dockerfile for CRMD
# Cybersecurity Risk Management Dashboard
# Deploys on Render.com

FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) \
    gd \
    mysqli \
    pdo \
    pdo_mysql \
    onig \
    xml \
    ctype \
    json

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Apache document root
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create public directory structure if needed
RUN mkdir -p /var/www/html/public && \
    cp /var/www/html/*.php /var/www/html/public/ 2>/dev/null || true && \
    cp -r /var/www/html/assets /var/www/html/public/ 2>/dev/null || true && \
    cp -r /var/www/html/config /var/www/html/public/ 2>/dev/null || true && \
    cp -r /var/www/html/includes /var/www/html/public/ 2>/dev/null || true && \
    cp -r /var/www/html/api /var/www/html/public/ 2>/dev/null || true

# Expose port
EXPOSE 8080

# Set environment
ENV APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    APACHE_LOG_DIR=/var/log/apache2 \
    APACHE_LOCK_DIR=/var/run/apache2 \
    APACHE_PID_FILE=/var/run/apache2.pid

# Configure Apache for Render (port 8080)
RUN echo "Listen 8080" > /etc/apache2/ports.conf && \
    sed -i 's/:80/:8080/g' /etc/apache2/sites-available/000-default.conf

# Start Apache
CMD ["apache2-foreground"]
