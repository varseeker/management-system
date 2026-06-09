# Stage 1: PHP dependencies
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

# Stage 2: Frontend assets
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json .npmrc* ./

# npm ci gagal jika lockfile dibuat di OS lain (Windows → Linux/Alpine).
# npm install menyesuaikan optional native bindings untuk platform build.
RUN npm install --ignore-scripts --no-audit --no-fund

COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public
COPY --from=vendor /app/vendor ./vendor

RUN npm run build

# Stage 3: Production runtime
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        nginx \
        supervisor \
        gettext \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        postgresql-dev \
        sqlite-dev \
        icu-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        opcache \
        pdo_pgsql \
        pdo_mysql \
        pdo_sqlite \
    && rm -rf /var/cache/apk/*

COPY docker/nginx.conf /etc/nginx/http.d/default.conf.template
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

WORKDIR /var/www/html

RUN mkdir -p \
        storage/framework/{cache,sessions,views} \
        storage/logs \
        bootstrap/cache \
        public/uploads/borrowings/pengajuan \
        public/uploads/borrowings/pengembalian \
    && chown -R www-data:www-data storage bootstrap/cache public/uploads

EXPOSE 8080

CMD ["/start.sh"]
