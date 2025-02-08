FROM php:8.3-fpm

COPY ./docker/app/docker-start.sh /usr/local/bin/docker-start.sh
RUN chmod 755 /usr/local/bin/docker-start.sh

RUN apt-get update && apt-get install -y --no-install-recommends \
    libcurl4-openssl-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    gettext \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    openssl \
    unzip \
    git \
    wrk \
    procps \
    && docker-php-ext-install -j$(nproc) gd zip intl curl pdo pdo_mysql mbstring pcntl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


#RUN pecl install swoole
RUN pecl install redis
#RUN docker-php-ext-enable redis

COPY ./ /var/www/html
WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --optimize-autoloader --no-dev

EXPOSE 8000

CMD ["docker-start.sh"]

