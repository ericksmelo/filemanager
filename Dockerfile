FROM php:8.2-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Install fileinfo for mime detection
RUN docker-php-ext-install fileinfo

# Apache config
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Copy app source
COPY . /var/www/html/

# Create uploads dir and set permissions
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod 755 /var/www/html/uploads

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
