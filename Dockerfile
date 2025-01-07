FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libmariadb-dev-compat \
    && docker-php-ext-install mysqli pdo pdo_mysql

RUN a2enmod rewrite && service apache2 restart
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN echo "DocumentRoot /var/www/html" >> /etc/apache2/sites-available/000-default.conf
RUN echo "<Directory /var/www/html>" >> /etc/apache2/sites-available/000-default.conf
RUN echo "  AllowOverride All" >> /etc/apache2/sites-available/000-default.conf
RUN echo "</Directory>" >> /etc/apache2/sites-available/000-default.conf

COPY ./site /var/www/html/

WORKDIR /var/www/html
