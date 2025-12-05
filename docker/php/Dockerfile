FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libicu-dev libpq-dev libzip-dev libonig-dev libxml2-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    supervisor netcat-traditional inotify-tools \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
       intl pcntl bcmath opcache pdo pdo_pgsql mbstring exif zip gd

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
