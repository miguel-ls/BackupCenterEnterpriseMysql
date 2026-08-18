FROM php:8.2-fpm

# ================================
# DEPENDENCIAS DEL SISTEMA
# ================================
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    git \
    unzip \
    zip \
    curl \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_sqlite \
        intl \
        zip \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ================================
# NODE + NPM
# ================================
RUN apt-get update && apt-get install -y \
    nodejs \
    npm \
    && node --version \
    && npm --version \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ================================
# COMPOSER
# ================================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ================================
# APP
# ================================
WORKDIR /var/www/html
COPY . .

# Evitar warning git
RUN git config --global --add safe.directory /var/www/html

# ================================
# PHP DEPENDENCIAS
# ================================
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

# ================================
# BUILD VUE
# ================================
WORKDIR /var/www/html/Dashboard

RUN npm install
RUN npm run build

# Copiar build a public
RUN cp -R dist/* /var/www/html/public/

# ================================
# VOLVER A ROOT APP
# ================================
WORKDIR /var/www/html

# ================================
# USUARIO apps (IMPORTANTE)
# ================================
RUN groupadd -g 568 apps && \
    useradd -u 568 -g apps -M -s /usr/sbin/nologin apps

# ================================
# PERMISOS (CLAVE)
# ================================
RUN chown -R apps:apps /var/www/html/public && \
    chmod -R 755 /var/www/html/public && \
    find /var/www/html/public -type f -exec chmod 644 {} \;

RUN mkdir -p \
    /var/www/html/storage \
    /var/www/html/database \
    /var/log/supervisor

RUN chown -R apps:apps \
    /var/www/html/storage \
    /var/www/html/database \
    /var/log/supervisor

# ================================
# CONFIGURACIONES
# ================================
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

RUN sed -i 's/^user = .*/user = apps/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/^group = .*/group = apps/' /usr/local/etc/php-fpm.d/www.conf

COPY docker/nginx/default.conf /etc/nginx/sites-enabled/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ================================
# PUERTO
# ================================
EXPOSE 80

# ================================
# START
# ================================
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]