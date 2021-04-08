#!/usr/bin/env bash
# @description Installs composer and tools installed via composer packages
set -eo pipefail

# @noargs
function composer_setup() {
  # Disable xdebug before any composer provisioning.
  vvv_info " * Turning off XDebug to avoid Composer performance issues"
  sh /srv/config/homebin/xdebug_off

  vvv_info " * Making sure the composer cache is not owned by root"
  mkdir -p /usr/local/src/composer
  mkdir -p /usr/local/src/composer/cache
  chown -R vagrant:www-data /usr/local/src/composer
  chown -R vagrant:www-data /usr/local/bin

  # COMPOSER

  export COMPOSER_ALLOW_SUPERUSER=1
  export COMPOSER_NO_INTERACTION=1
  export COMPOSER_RUNTIME_ENV="vagrant"

  vvv_info " * Checking Composer is installed"
  composer_bin=$(command -v composer)
  if [[ $composer_bin ]]; then
    composer_ver=$(composer --version)
    vvv_error " * Composer found at ${composer_bin}"
    vvv_error " * Installed version: ${composer_ver}"
  fi
  if [[ ! -f "/usr/local/bin/composer" ]]; then
    vvv_info " * Installing Composer..."
    curl -sS "https://getcomposer.org/installer" | php
    chmod +x "composer.phar"
    mv "composer.phar" "/usr/local/bin/composer"
    vvv_info " * Forcing composer to v2.x"
    noroot composer selfupdate --2
    vvv_success " * Composer installer steps completed"
  fi

  vvv_info " * Checking for github tokens"
  if github_token=$(shyaml get-value -q "general.github_token" < "${VVV_CONFIG}"); then
    vvv_info " * A personal GitHub token was found, configuring composer"
    rm /srv/provision/github.token
    echo "$github_token" >> /srv/provision/github.token
    ghtoken=$(cat /srv/provision/github.token)
    noroot composer config --global github-oauth.github.com "$ghtoken"
    vvv_success " * Your personal GitHub token is set for Composer."
  fi

  # Update both Composer and any global packages. Updates to Composer are direct from
  # the master branch on its GitHub repository.
  vvv_info " * Checking for composer updates"
  if [[ -n "$(noroot composer --version --no-ansi | grep 'Composer version')" ]]; then
    vvv_info " * Updating Composer..."
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global config bin-dir /usr/local/bin
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi self-update --2 --stable --no-progress --no-interaction
    vvv_info " * Making sure the PHPUnit 7.5 package is available..."
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global require --prefer-dist --no-update --no-progress --no-interaction phpunit/phpunit:^7.5
  fi

  vvv_info " * Updating global composer packages..."
  COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global update --no-progress --no-interaction
  vvv_success " * Global composer package update completed"

  vvv_hook after_composer
}
export -f composer_setup

vvv_add_hook after_packages composer_setup
