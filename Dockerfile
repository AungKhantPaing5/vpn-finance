FROM php:8.1-apache

# PHP extension များ ထည့်သွင်းခြင်း
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Apache rewrite module
RUN a2enmod rewrite

# PHP file များကို Apache မှ အမှန်တကယ် Execute လုပ်ရန် Config ပြင်ခြင်း
RUN echo "<FilesMatch \.php$>\n\
    SetHandler application/x-httpd-php\n\
</FilesMatch>\n\
<Directory /var/www/html/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    DirectoryIndex index.php index.html\n\
</Directory>" > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
