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

# Hanya package.json — lockfile Windows tidak kompatibel dengan Linux/Alpine.
# npm install di container menyelesaikan native bindings untuk platform build.
COPY package.json .npmrc* ./
RUN npm install --ignore-scripts --no-audit --no-fund

COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public
COPY --from=vendor /app/vendor ./vendor

RUN npm run build \
    && test -f public/build/manifest.json

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

RUN chmod +x /var/www/html/docker/fix-render-env.sh /var/www/html/docker/wait-for-db.sh /var/www/html/docker/start.sh \
    && mkdir -p \
        storage/framework/{cache,sessions,views} \
        storage/logs \
        storage/app/public/menus \
        storage/app/public/borrowings/pengajuan/thumbs \
        storage/app/public/borrowings/pengembalian/thumbs \
        storage/app/public/borrowings/seed \
        bootstrap/cache \
        public/uploads/borrowings/pengajuan \
        public/uploads/borrowings/pengembalian \
    && chown -R www-data:www-data storage bootstrap/cache public/uploads

EXPOSE 8080

CMD ["/start.sh"]
