#!/bin/sh
##########################################################
# run inside based on WORKDIR $(pwd): /${APP_DIR}
##########################
# Shell pre CMD run steps
##########################
# Fail on first error
set -e

# echo Script dir
SCRIPT_DIR=$(pwd)/${SCRIPT_DIR:-"runtime"}
echo "▶ SCRIPT_DIR=$SCRIPT_DIR"

########################
# CMDs
########################
# local .env
env_export() {
    if [ -f "$(pwd)/.env" ]; then
        set -a
        . "$(pwd)/.env"
        set +a
    fi
}

# Permissions & Dirs
dir_optimize() {
    mkdir -p \
    storage/framework/cache \
    storage/framework/views \
    storage/framework/sessions \
    storage/logs \
    bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    rm -rf storage/framework/sessions/*
    rm -rf storage/framework/cache/data/*
    rm -rf bootstrap/cache/*.php
}

# Clear all caches
cache_clear() {
    php artisan optimize:clear  # cache, config, route, view
    php artisan event:clear
}

# Cache rebuild
cache_rebuild() {
    php artisan config:cache #
    # php artisan route:cache # crashes
    php artisan view:cache
    php artisan event:cache
}

# App optimize
app_optimize() {
    dir_optimize
    cache_clear
    env_export
    cache_rebuild
}

########################
# Caddy CMDs
########################
caddy_start() {
    app_optimize
    caddy_config
    exec frankenphp run --config $SCRIPT_DIR/Caddyfile
}

caddy_config() {
    WORKER_CONFIG='worker public/index.php {$FRANKENPHP_NUM_WORKERS:4}'
    sed -i '/worker /d' "$SCRIPT_DIR/Caddyfile"
    [ "$APP_PORT" = 8000 ] && sed -i "/#worker_holder/ a $WORKER_CONFIG" $SCRIPT_DIR/Caddyfile
    frankenphp fmt --overwrite $SCRIPT_DIR/Caddyfile
}

caddy_stop() {
    if PID=$(pgrep -f "frankenphp"); then
        kill -TERM "$PID"
        echo "caddy:stopped at PID:$PID"
    else
        echo "caddy:stopped already"
    fi
}

caddy_status() {
    if PID=$(pgrep -f "frankenphp"); then
        echo "caddy:running at PID:$PID"
    else
        echo "caddy:stopped"
    fi
}

caddy_restart() {
    caddy_stop
    caddy_start
    # frankenphp reload --config $SCRIPT_DIR/Caddyfile # works if ${CADDY_ADMIN}=on
}

########################
# Worker CMDs
########################
queue_start() {
    app_optimize
    exec php artisan queue:work -v --queue={$QUEUES} --tries=${QUEUE_TRIES:-3}
}

queue_status() {
    echo "▶ QUEUE_TRIES=${QUEUE_TRIES}"
    if PIDs=$(pgrep -f "queue:work"); then
        echo "queue:running at PIDs:$PIDs"
        return 0 #healthy
    else
        echo "queue:stopped"
        return 1 #unhealthy
    fi
}

queue_restart() {
    env_export
    php artisan queue:restart
}

########################
# schedule CMDs
########################
schedule_start() {
    app_optimize
    exec php artisan schedule:work
}

schedule_status() {
    if PID=$(pgrep -f "schedule:work"); then
        echo "schedule:running at PID:$PID"
        return 0 #healthy
    else
        echo "schedule:stopped already"
        return 1 #unhealthy
    fi
}

schedule_restart() {
    env_export
    php artisan schedule:restart
}

########################
# Websocket CMDs
########################
websocket_start() {
    app_optimize
    exec php artisan reverb:start --host=${BIND_HOST} --port=${WS_PORT}
}

websocket_status() {
    if PID=$(pgrep -f "reverb:start"); then
        echo "reverb:running at PID:$PID"
        return 0 #healthy
    else
        echo "reverb:stopped already"
        return 1 #unhealthy
    fi
}

websocket_restart() {
    env_export
    php artisan reverb:restart
}

########################
# php.ini set
########################
php_ini() {
    env_export
    # Delete ONLY ini files in php root (not conf.d)
    # at local "/etc/php-zts"
    # at container "/usr/local/etc/php"
    PHP_DIR=${PHP_DIR:-"/etc/php-zts"}
    PHP_INI="${PHP_DIR}/php.ini"
    find "$PHP_DIR" \
    -maxdepth 1 \
    -type f \
    -name "php.ini*" \
    -exec rm -f {} \;
    echo "✔ Old php.ini files removed"
    echo "▶ Generating new php.ini from ENV"
    # Generate php.ini from env variables
cat > "$PHP_INI" <<EOF
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Auto-generated php.ini
; Generated at container start
; Source of truth: ENV variables
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

;;;;;;;;;;;;;;;;
; Error handling
;;;;;;;;;;;;;;;;
display_errors = ${PHP_DISPLAY_ERRORS:-Off}
display_startup_errors = ${PHP_DISPLAY_STARTUP_ERRORS:-Off}
log_errors = ${PHP_LOG_ERRORS:-On}
error_reporting = ${PHP_ERROR_REPORTING:-E_ALL & ~E_DEPRECATED & ~E_STRICT}
html_errors = ${PHP_HTML_ERRORS:-Off}
error_log = ${PHP_ERROR_LOG:-/proc/self/fd/2}
zend.exception_ignore_args = ${PHP_EXCEPTION_IGNORE_ARGS:-On}
zend.exception_string_param_max_len = ${PHP_EXCEPTION_STRING_PARAM_MAX_LEN:-0}

;;;;;;;;;;;;;;;;;;;;;
; Resource limits
;;;;;;;;;;;;;;;;;;;;;
memory_limit = ${PHP_MEMORY_LIMIT_IN_MB:-256}M
max_execution_time = ${PHP_MAX_EXECUTION_TIME:-120}
max_input_time = ${PHP_MAX_INPUT_TIME:-120}
default_socket_timeout = ${PHP_DEFAULT_SOCKET_TIMEOUT:-120}

;;;;;;;;;;;;;;;;;;;;;
; File uploads
;;;;;;;;;;;;;;;;;;;;;
upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE:-50M}
post_max_size = ${PHP_POST_MAX_SIZE_IN_MB:-64}M

;;;;;;;;;;;;;;;;;;;;;
; OPCache
;;;;;;;;;;;;;;;;;;;;;
opcache.enable = ${PHP_OPCACHE_ENABLE:-1}
opcache.enable_cli = ${PHP_OPCACHE_ENABLE_CLI:-1}
opcache.memory_consumption = ${PHP_OPCACHE_MEMORY:-1024}
opcache.interned_strings_buffer = ${PHP_OPCACHE_INTERNED_STRINGS:-128}
opcache.max_accelerated_files = ${PHP_OPCACHE_MAX_FILES:-40000}
opcache.validate_timestamps = ${PHP_OPCACHE_VALIDATE_TIMESTAMPS:-0}
opcache.revalidate_freq = ${PHP_OPCACHE_REVALIDATE_FREQ:-0}
opcache.save_comments = ${PHP_OPCACHE_SAVE_COMMENTS:-1}
opcache.max_wasted_percentage = ${PHP_OPCACHE_MAX_WASTED:-10}
opcache.enable_file_override = ${PHP_OPCACHE_FILE_OVERRIDE:-1}

;;;;;;;;;;;;;;;;;;;;;
; Realpath cache
;;;;;;;;;;;;;;;;;;;;;
realpath_cache_size = ${PHP_REALPATH_CACHE_SIZE:-4096K}
realpath_cache_ttl = ${PHP_REALPATH_CACHE_TTL:-600}
EOF
    echo "✔ php.ini generated at ${PHP_INI}"
}

################################################
# CMD router (mapper) script_name -> function
################################################
# script_name -> function
# sh -c launcher.sh <script_name ($1 shell arg or ${ENTRY_CMD} from ENV)>
# reg more (script_name -> function) & call them here

ENTRY_CMD="${1:-$ENTRY_CMD}"
case "$ENTRY_CMD" in
    dir_optimize) dir_optimize ;;
    cache_clear) cache_clear ;;
    cache_rebuild) cache_rebuild ;;
    app_optimize) app_optimize ;;
    env_export) env_export ;;
    caddy_start) caddy_start ;;
    caddy_status) caddy_status ;;
    caddy_stop) caddy_stop ;;
    caddy_config) caddy_config ;;
    caddy_restart) caddy_restart ;;
    queue_start) queue_start ;;
    queue_status) queue_status ;;
    queue_restart) queue_restart ;;
    schedule_start) schedule_start ;;
    schedule_status) schedule_status ;;
    schedule_restart) schedule_restart ;;
    websocket_start) websocket_start ;;
    websocket_status) websocket_status ;;
    websocket_restart) websocket_restart ;;
    php_ini) php_ini ;;
    *) echo "Unknown ENTRY_CMD $ENTRY_CMD"; exit 1 ;;
esac
