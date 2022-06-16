#!/usr/bin/env bash
# @description PHP Codesniffer
set -eo pipefail

# @noargs
function php_codesniff_setup() {
  export COMPOSER_ALLOW_SUPERUSER=1
  export COMPOSER_NO_INTERACTION=1

  if [[ -f "/srv/www/phpcs/CodeSniffer.conf" ]]; then
    vvv_info " * [PHPCS]: Removing the old PHPCS setup"
    rm -rf /srv/www/phpcs
  fi

  # PHP_CodeSniffer (for running WordPress-Coding-Standards)
  # Sniffs WordPress Coding Standards
  vvv_info " * [PHPCS]: Provisioning PHP_CodeSniffer (phpcs), see https://github.com/squizlabs/PHP_CodeSniffer"

  noroot mkdir -p /srv/www/phpcs
  noroot cp -f "/srv/provision/core/phpcs/composer.json" "/srv/www/phpcs/composer.json"
  cd /srv/www/phpcs
  COMPOSER_BIN_DIR="bin" noroot composer update --no-ansi --no-progress

  vvv_info " * [PHPCS]: Setting WordPress-Core as the default PHPCodesniffer standard"

  # Install the standards in PHPCS
  noroot /srv/www/phpcs/bin/phpcs --config-set default_standard WordPress-Core
  local standards=$(noroot /srv/www/phpcs/bin/phpcs -i)
  vvv_success " * [PHPCS]: Completed with the following PHPCS standards set up: ${standards}"
}
export -f php_codesniff_setup

vvv_add_hook after_composer php_codesniff_setup
