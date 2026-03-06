# - Caddy FrankenPHP: web server + built-in PHP runtime worker.
# - Octane: state manager not server worker manager
# - Octane manage reset cached application state inside workers.
# bookworm|alpine
ARG BASE=bookworm
######################
# Alpine base
######################
FROM dunglas/frankenphp:php8.3-alpine AS alpine
RUN apk add --no-cache \
    bash curl icu-dev libzip-dev oniguruma-dev \
    openssl-dev libxml2-dev zlib-dev linux-headers
######################
# Bookworm base
######################
FROM dunglas/frankenphp:php8.3-bookworm AS bookworm
RUN apt-get update && apt-get install -y --no-install-recommends \
    bash curl libicu-dev libzip-dev libonig-dev libssl-dev \
    libxml2-dev zlib1g-dev ca-certificates  build-essential
######################
# Final stage selector
######################
FROM ${BASE} AS runtime
# Runtime args
ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_PROCESS_TIMEOUT=0 \
    PHP_DIR="/usr/local/etc/php" \
    SCRIPT_DIR="runtime" \
    APP_DIR="app" \
    APP_HOST="localhost" \
    BIND_HOST="0.0.0.0" \
    APP_SCHEME="http" \
    APP_PORT=80 \
    REDIS_PORT=6379 \
    DB_PORT=3306 \
    WS_PORT=8080 \
    # WS_PATH="/ws" \
    REVERB_SCALING_ENABLED=false \
    QUEUE_TRIES=3 \
    # Critical Config (immutable Names "Vendor use")
    MAX_REQUESTS=1000 \
    REQUEST_MAX_EXECUTION_TIME=120 \
    PHP_POST_MAX_SIZE_IN_MB=64 \
    PHP_MEMORY_LIMIT_IN_MB=256 \
    GARBAGE_AT_MEMORY_PERCENT=0.60 \
    FRANKENPHP_NUM_THREADS=8 \
    FRANKENPHP_MAX_THREADS=64 \
    FRANKENPHP_NUM_WORKERS=4

# install php extensions
RUN install-php-extensions \
    pdo pdo_mysql mysqli mbstring bcmath curl zip intl \
    xml dom fileinfo pcntl sockets opcache redis

# remove apt lists
RUN rm -rf /var/lib/apt/lists/*

# Copy composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application
COPY . /${APP_DIR}

# Set working directory (set "$(pwd) and ./ == "/${APP_DIR}")
WORKDIR /${APP_DIR}

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --ignore-platform-reqs \
    -vvv

# Edge Server:
#   - direct client connect (not behind Load balancer)
#   - use APP_PORT (HTTP:80|HTTPS:443)
#   - handles TLS if AUTO_HTTPS & APP_PORT:443 enabled
#   - example: direct client use of (Caddy FrankenPHP)
#
# Upstream Server (behind|currently):
#   - indirect client connect (behind Load balancer)
#   - no OCTANE_PORT: octane handle laravel app boot state context only
#   - (HTTPS)->LB->(HTTP)->APP_PORT->REDIS_PORT_OR_DB_PORT
EXPOSE ${APP_PORT} \
    ${WS_PORT} \
    ${REDIS_PORT} \
    ${DB_PORT}

# Entry point (should be copied if .dockerignored)
# Executable Scripts
# Set php.ini
RUN chmod +x ${SCRIPT_DIR}/* ${PHP_DIR}/*
RUN ${SCRIPT_DIR}/launcher.sh php_ini
ENTRYPOINT ["./runtime/launcher.sh"]
