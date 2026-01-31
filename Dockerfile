FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libmemcached-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    curl \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        gd \
        zip \
        mysqli \
        pdo \
        pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN echo "upload_max_filesize=100M\n\
post_max_size=100M\n\
memory_limit=256M\n\
max_execution_time=300\n\
max_input_vars=5000\n\
file_uploads=On" > /usr/local/etc/php/conf.d/uploads.ini

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/

RUN a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php \
    -- --install-dir=/usr/local/bin --filename=composer

# ✅ COPY ENTRYPOINT FIRST
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV COMPOSER_ALLOW_SUPERUSER=1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]

EXPOSE 80
