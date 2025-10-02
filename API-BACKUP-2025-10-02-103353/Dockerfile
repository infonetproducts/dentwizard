# Use official PHP image with Apache
FROM php:8.2-apache

# Install necessary PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install additional dependencies for SAML
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libssl-dev \
    && docker-php-ext-install soap \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy application files
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
