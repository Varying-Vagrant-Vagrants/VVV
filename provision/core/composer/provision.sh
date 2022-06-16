#!/usr/bin/env bash
# @description Installs Composer and tools installed via Composer packages
set -eo pipefail

# @noargs
function composer_pre_setup() {
  # Disable XDebug before any Composer provisioning.
  vvv_info " * [Composer]: Turning off PHP debug mods to avoid Composer performance issues"
  /srv/config/homebin/switch_php_debugmod none
}
export -f composer_pre_setup

# @noargs
function composer_setup() {
  # COMPOSER
  export COMPOSER_ALLOW_SUPERUSER=1
  export COMPOSER_NO_INTERACTION=1
  export COMPOSER_RUNTIME_ENV="vagrant"

  vvv_info " * [Composer]: Checking if Composer is installed"
  if ! command -v composer &> /dev/null; then
    vvv_info " * [Composer]: Running Composer Installer..."
    curl -sS "https://getcomposer.org/installer" | php
    if [ -f composer.phar ]; then
      vvv_info " * [Composer]: Setting ownership and executable bit of composer..."
      chmod +x "composer.phar"
      chown -R vagrant:www-data /usr/local/bin
      vvv_info " * [Composer]: Moving composer into place..."
      if mv -f "composer.phar" "/usr/local/bin/composer"; then
        vvv_success " * [Composer]: Installed to /usr/local/bin"
      else
        vvv_error " ! [Composer]: Moving composer.phar to /usr/local/bin/composer did not succeed"
        return 1
      fi
    else
      vvv_error " ! [Composer]: Expected to find a composer.phar after running composer installer, but none was found"
      return 1
    fi
  else
    vvv_info " * [Composer]: Already installed"
  fi

  vvv_info " * [Composer]: Making sure the Composer cache is not owned by root"
  COMPOSER_DATA_DIR=$(composer config -g data-dir)
  mkdir -p "${COMPOSER_DATA_DIR}"/cache
  mkdir -p /root/.local/share/composer
  chown -R vagrant:www-data "${COMPOSER_DATA_DIR}"
  chown -R vagrant:www-data /root/.local/share/composer

  vvv_info " * [Composer]: Checking for GitHub tokens"
  if github_token=$(shyaml get-value -q "general.github_token" < "${VVV_CONFIG}"); then
    vvv_info " * [Composer]: A personal GitHub token was found, configuring Composer"
    rm /srv/provision/github.token
    echo "${github_token}" >> /srv/provision/github.token
    ghtoken=$(cat /srv/provision/github.token)
    noroot composer config --global github-oauth.github.com "$ghtoken"
    vvv_success " * [Composer]: Your personal GitHub token is set for Composer."
  fi

  # Update both Composer and any global packages. Updates to Composer are direct from
  # the master branch on its GitHub repository.
  vvv_info " * [Composer]: Checking for Composer updates"
  if ! noroot composer --version --no-ansi | grep 'Composer version'; then
    mkdir -p /root/.local/share/composer
    vvv_info " * [Composer]: Setting bin dir"
    COMPOSER_HOME="${COMPOSER_DATA_DIR}" noroot composer --no-ansi global config bin-dir /usr/local/bin
    vvv_info " * [Composer]: Updating Composer"
    COMPOSER_HOME="${COMPOSER_DATA_DIR}" noroot composer --no-ansi self-update --2 --stable --no-progress --no-interaction
    vvv_info " * [Composer]: Making sure the PHPUnit ^7.5 package is available..."
    COMPOSER_HOME="${COMPOSER_DATA_DIR}" noroot composer --no-ansi global require --prefer-dist --no-update --no-progress --no-interaction phpunit/phpunit:^7.5
  fi

  if [ -f "${COMPOSER_DATA_DIR}/composer.json" ]; then
    vvv_info " * [Composer]: Updating global Composer packages..."
    COMPOSER_HOME="${COMPOSER_DATA_DIR}" noroot composer --no-ansi global update --no-progress --no-interaction
    vvv_success " * [Composer]: Global Composer package update completed"
  fi

  vvv_success " * [Composer]: Main tasks completed, running after_composer hook"

  vvv_hook after_composer
}
export -f composer_setup

vvv_add_hook tools_pre_setup composer_pre_setup
vvv_add_hook tools_setup composer_setup
