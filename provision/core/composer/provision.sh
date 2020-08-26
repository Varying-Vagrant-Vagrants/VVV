#!/bin/bash
composer_setup() {
  # Disable xdebug before any composer provisioning.
  sh /srv/config/homebin/xdebug_off

  echo " * Making sure the composer cache is not owned by root"
  mkdir -p /usr/local/src/composer
  mkdir -p /usr/local/src/composer/cache
  chown -R vagrant:www-data /usr/local/src/composer
  chown -R vagrant:www-data /usr/local/bin

  # COMPOSER

  export COMPOSER_ALLOW_SUPERUSER=1
  export COMPOSER_NO_INTERACTION=1

  echo " * Checking for Composer"
  exists_composer="$(which composer)"
  if [[ "/usr/local/bin/composer" != "${exists_composer}" ]]; then
    echo " * Installing Composer..."
    curl -sS "https://getcomposer.org/installer" | php
    chmod +x "composer.phar"
    mv "composer.phar" "/usr/local/bin/composer"
  fi

  github_token=$(shyaml get-value general.github_token 2> /dev/null < "${VVV_CONFIG}")
  if [[ ! -z $github_token ]]; then
    rm /srv/provision/github.token
    echo "$github_token" >> /srv/provision/github.token
    echo " * A personal GitHub token was found, configuring composer"
    ghtoken=$(cat /srv/provision/github.token)
    noroot composer config --global github-oauth.github.com "$ghtoken"
    echo " * Your personal GitHub token is set for Composer."
  fi

  # Update both Composer and any global packages. Updates to Composer are direct from
  # the master branch on its GitHub repository.
  if [[ -n "$(noroot composer --version --no-ansi | grep 'Composer version')" ]]; then
    echo " * Updating Composer..."
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global config bin-dir /usr/local/bin
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi self-update --stable --no-progress --no-interaction
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global require --prefer-dist --no-update --no-progress --no-interaction phpunit/phpunit:^7.5
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global require --prefer-dist --no-update --no-progress --no-interaction phpunit/phpunit:^7.5
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global update --no-progress --no-interaction
  fi

  vvv_hook after_composer
}
export -f composer_setup

vvv_add_hook after_packages composer_setup