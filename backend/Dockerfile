FROM php:8.3-fpm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Привести UID/GID www-data к UID/GID хост-пользователя,
# чтобы файлы, создаваемые php-fpm (кеши, сессии, логи),
# принадлежали тому же пользователю, что и на хосте.
RUN groupmod -o -g ${GID} www-data \
    && usermod  -o -u ${UID} -g ${GID} www-data

WORKDIR /var/www

EXPOSE 9000
CMD ["php-fpm"]
