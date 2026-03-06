# FrankenPHP:8.3 Runtime

consist of one binary file embedded 3 parts:

- php-zts (Zend Thread Safe PHP:8.3 runtime)
- frankenphp (GoRoutine Thread Based Workers)
- Caddy server (Server Manager)

## Config (ENV based)

- `Caddyfile`: the main config file
- `launcher.sh` -> `php_ini()`

---

## Command Usage

- ```runtime/launcher.sh <command_name>```
- available commands at the end of the entrypoint `launcher.sh`:
  - all commands works for both local (.env) & docker

```text
    php_ini
    dir_optimize
    cache_clear
    cache_rebuild
    app_optimize
    env_export // for local dev
    caddy_start
    caddy_status
    caddy_stop
    caddy_config
    caddy_restart
    queue_start
    queue_status
    queue_restart
    schedule_start
    schedule_status
    schedule_restart
    websocket_start
    websocket_status
    websocket_restart
```

- set `$ENTRY_CMD` at .env acc to service
- services:
  - api:
    - entry: `$ENTRY_CMD=caddy_start`
    - health: `caddy_status` or `/health`
    - restart: `caddy_restart`
  - websocket:
    - entry: `$ENTRY_CMD=websocket_start`
    - health: `websocket_status`
    - restart: `websocket_restart`
  - queue:
    - entry: `$ENTRY_CMD=queue_start`
    - health: `queue_status`
    - restart: `queue_restart`
  - schedule:
    - entry: `$ENTRY_CMD=schedule_start`
    - health: `schedule_status`
    - restart: `schedule_restart`
- using ```runtime/launcher.sh php_ini``` \
makes one runtime the same at prod, local, dev \
acc to .env vars

## Local Dev

- Supported operating systems
All Debian based operating systems with glibc >= 2.31

- Run the following command to automatically set up the repository for PHP 8.3: \
``` curl -fsSL <https://files.henderkes.com/install.sh> | sh -s 8.3 ```
- Install packages from the repository: \
```sudo apt install frankenphp```
- Install pre-packaged extensions using the php-zts- prefix:

    ```sudo apt update && \
    sudo apt install -y \
    php-zts-pdo \
    php-zts-pdo-mysql \
    php-zts-mysqli \
    php-zts-mbstring \
    php-zts-bcmath \
    php-zts-curl \
    php-zts-zip \
    php-zts-intl \
    php-zts-xml \
    php-zts-fileinfo \
    php-zts-pcntl \
    php-zts-sockets \
    php-zts-opcache \
    php-zts-gd \
    php-zts-soap \
    php-zts-redis
    ```

- For extensions not available as pre-packaged use PHP/PIE:

```bash
sudo pie-zts install apcu/apcu \
xdebug/xdebug \
pecl/uuid \
imagick/imagick \
pecl/yaml \
php-memcached/php-memcached \
php/sqlite3
```

- disable global caddy server (block :80 port) \
```sudo systemctl disable frankenphp```

### remove any duplicated ext.ini if warn founded not .dpkg

```sudo rm /etc/php-zts/conf.d/20-fileinfo.ini```

### Configure php-zts

- ```runtime/launcher.sh php_ini```
- or ```cp ./php8.3.ini /etc/php-zts/php.ini``` //your php.ini
- set ```$PHP_DIR="/etc/php-zts"```
- set ```$SCRIPT_DIR="runtime"```

### using php cli

- ```php-zts artisan serve```
- ```frankenphp php-cli serve```
- to use php alias instead of php-zts
  - remove old non thread safe php
  - ```sudo ln -s /usr/bin/php-zts /usr/bin/php```

### composer install

```text
    wget -O composer-setup.php <https://getcomposer.org/installer> && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php
```

---

## Docker Container

- `runtime/Dockerfile` contains 2 images \
acc to docker arg to `BASE` :
  - `alpine`: lighter (POSIX:Musl)
  - `bookworm` (default): more performant & stable (POSIX:Glib)
- ```docker build . -f runtime/Dockerfile -t dot_frankenphp```
- map the ports `-p` & `--env` acc to need & availability
- `docker run -d -p 81:80 -p 3307:3306 --name dot_api --restart=no --network=bridge --env "ENTRY_CMD=caddy_start" dot_frankenphp:latest`
- `AWS`: \
    configure github actions \
    map to runtime/Dockerfile
- `Northflank`: \
    browse to runtime/Dockerfile

## Available Modes & Features per path|port

all config is (ENV based)

- `:8000 Worker mode`:
  - keep app in memory no boot (more performant)
    - `runtime/Caddyfile`: server|manager|worker
    - `runtime/launcher.sh`: `php_ini()`
    - `config/octane.php`: reset app state
  - test: `/app-state`:
    - check the reqStaticCounter (App State)
    - check process_id

- `:80 Classic mode`
  - boot app every request (more safe)
  - `runtime/Caddyfile`: server|manager|worker
    - using frankephp threads more performant than `FPM` about 2 times
    - caddy can be a manager for `fastcgi` (`FPM` alternative)
  - `runtime/launcher.sh`: `php_ini()`
  - test: `/app-state`:
    - check the reqStaticCounter (App State)
    - check process_id

- `:8080 WS (Websocket)`
  - `config/broadcasting.php`: config `reverb`
  - `config/reverb.php`: configure ws
  - test: `/`:
    - open channel `messages`
    - choose event `1`
    - ```event (new App\Events\WSEvent('messages',1, ['number'=> fake()->numberBetween()]));```

- `"/.well-known/mercure" SSE (Server Sent Event)`
  - can be used alongside (Classic mode & Worker mode)
  - `runtime/Caddyfile`: config `mercure`
  - `config/broadcasting.php`: config `mercure`
  - test: `/`:
    - choose topic `messages.1`
    - ```sse('messages', 1, ['number'=> 1])```
    or
    - ```event (new App\Events\SSEvent('messages', 1, ['number'=> 1]))``` \
    extending the event class:
      - can have multiple listeners in the app
      - can be queued also
