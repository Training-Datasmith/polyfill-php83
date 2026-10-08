#!/usr/bin/env bash
set -euo pipefail

php_series="${1:?php series required}"

install_composer() {
    local expected actual
    expected="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    actual="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    if [ "$expected" != "$actual" ]; then
        echo "ERROR: Invalid Composer installer checksum" >&2
        rm -f composer-setup.php
        exit 1
    fi
    local composer_channel=()
    if [ "$(php -r 'echo PHP_VERSION_ID < 80200 ? 1 : 0;')" = 1 ]; then
        composer_channel=(--2.2)
    fi
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer "${composer_channel[@]}" --quiet
    rm -f composer-setup.php
}

container_setup() {
    if [ "$php_series" = "7.2" ]; then
        if grep -q buster /etc/os-release 2>/dev/null; then
            sed -i 's|deb.debian.org|archive.debian.org|g' /etc/apt/sources.list
            sed -i 's|security.debian.org|archive.debian.org|g' /etc/apt/sources.list
            echo 'Acquire::Check-Valid-Until "false";' >/etc/apt/apt.conf.d/99no-check-valid-until
        fi
    fi
    apt-get update -qq
    if [ "$php_series" = "8.3" ]; then
        apt-get install -y -qq libldap2-dev libonig-dev unzip git >/dev/null
        docker-php-ext-install -j"$(nproc)" mbstring ldap >/dev/null
    else
        apt-get install -y -qq libldap2-dev libonig-dev unzip git >/dev/null
        docker-php-ext-install -j"$(nproc)" mbstring ldap >/dev/null
    fi
    docker-php-ext-install -j"$(nproc)" zip >/dev/null 2>&1 || true
    php -m | grep -q mbstring || { echo "mbstring missing" >&2; exit 1; }
    php -m | grep -q ldap || { echo "ldap missing" >&2; exit 1; }
    install_composer
    rm -rf vendor composer.lock
    composer update --no-interaction --no-ansi --no-progress
    rm -f composer.lock
}

run_phpunit() {
    php -d display_errors=1 vendor/bin/phpunit --configuration phpunit.xml.dist "$@"
}

container_setup
php -v | head -1
echo '--- default order run 1 ---'
run_phpunit
echo '--- default order run 2 ---'
run_phpunit
echo '--- random seed 20261008 run 1 ---'
run_phpunit --order-by=random --random-order-seed=20261008
echo '--- random seed 20261008 run 2 ---'
run_phpunit --order-by=random --random-order-seed=20261008
