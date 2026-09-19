# Production image for the Early Warning backend (Laravel 12 / PHP 8.2).
#
# One container runs everything the app needs to be "always on":
#   - nginx + php-fpm ........ the API and the /admin panel
#   - earthquake:listen ...... the permanent EMSC real-time earthquake feed
#   - schedule:work .......... the scheduler (hourly weather:fetch)
# supervisord (docker/supervisord.conf) starts them and restarts any that die.
# The database is NOT inside the container: it is an external MySQL service.

FROM php:8.2-fpm-bookworm

# nginx = web server, supervisor = keeps the processes alive,
# gettext-base = envsubst (fills the port into the nginx config at start).
RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx supervisor gettext-base unzip git curl \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions the project needs (intl is required by the Filament admin panel).
COPY --from=ghcr.io/mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl pdo_mysql bcmath zip opcache pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP packages first (separate layer, so it is cached between deploys
# as long as composer.json / composer.lock don't change).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Then the application code.
COPY . .

# The build has no real .env, database or cache: point every driver at
# throw-away in-memory values just for these build-time commands.
RUN composer dump-autoload --optimize --no-dev --no-scripts \
    && export APP_ENV=production APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
       CACHE_STORE=array SESSION_DRIVER=array DB_CONNECTION=sqlite DB_DATABASE=:memory: \
    && php artisan package:discover --ansi \
    && php artisan filament:assets --ansi

# Server configuration.
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/nginx-site.conf.template /etc/nginx/nginx-site.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/app.conf
COPY docker/start.sh /usr/local/bin/start.sh
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf \
    && chmod +x /usr/local/bin/start.sh \
    && chown -R www-data:www-data storage bootstrap/cache

# Railway tells the container which port to listen on through $PORT
# (start.sh falls back to 8080).
EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
