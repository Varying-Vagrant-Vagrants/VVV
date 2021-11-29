#!/usr/bin/env bash
# @description Installs Composer and tools installed via Composer packages
set -eo pipefail

# @noargs
function composer_setup() {
  # Disable XDebug before any Composer provisioning.
  vvv_info " * Turning off XDebug to avoid Composer performance issues"
  sh /srv/config/homebin/xdebug_off

  # COMPOSER

  export COMPOSER_ALLOW_SUPERUSER=1
  export COMPOSER_NO_INTERACTION=1
  export COMPOSER_RUNTIME_ENV="vagrant"

  vvv_info " * Checking if Composer is installed"
  if ! command -v composer &> /dev/null; then
    vvv_info " * Installing Composer..."
    curl -sS "https://getcomposer.org/installer" | php
    chmod +x "composer.phar"
    chown -R vagrant:www-data /usr/local/bin
    mv "composer.phar" "/usr/local/bin/composer"
    vvv_success " * Composer installer steps completed"
  fi

  vvv_info " * Making sure the Composer cache is not owned by root"
  COMPOSER_DATA_DIR=$(composer config -g data-dir)
  mkdir -p "${COMPOSER_DATA_DIR}"/cache
  chown -R vagrant:www-data "${COMPOSER_DATA_DIR}"

  vvv_info " * Checking for GitHub tokens"
  if github_token=$(shyaml get-value -q "general.github_token" < "${VVV_CONFIG}"); then
    vvv_info " * A personal GitHub token was found, configuring Composer"
    rm /srv/provision/github.token
    echo "$github_token" >> /srv/provision/github.token
    ghtoken=$(cat /srv/provision/github.token)
    noroot composer config --global github-oauth.github.com "$ghtoken"
    vvv_success " * Your personal GitHub token is set for Composer."
  fi

  # Update both Composer and any global packages. Updates to Composer are direct from
  # the master branch on its GitHub repository.
  vvv_info " * Checking for Composer updates"
  if ! noroot composer --version --no-ansi | grep 'Composer version'; then
    vvv_info " * Updating Composer..."
    COMPOSER_HOME="${COMPOSER_DATA_DIR}" noroot composer --no-ansi global config bin-dir /usr/local/bin
    COMPOSER_HOME="${COMPOSER_DATA_DIR}" noroot composer --no-ansi self-update --2 --stable --no-progress --no-interaction
    vvv_info " * Making sure the PHPUnit 7.5 package is available..."
    COMPOSER_HOME="${COMPOSER_DATA_DIR}" noroot composer --no-ansi global require --prefer-dist --no-update --no-progress --no-interaction phpunit/phpunit:^7.5
  fi

  if [ -f "${COMPOSER_DATA_DIR}"/composer.json ]; then
    vvv_info " * Updating global Composer packages..."
    COMPOSER_HOME="${COMPOSER_DATA_DIR}" noroot composer --no-ansi global update --no-progress --no-interaction
    vvv_success " * Global Composer package update completed"
  fi

  vvv_hook after_composer
}
export -f composer_setup

vvv_add_hook tools_setup composer_setup
