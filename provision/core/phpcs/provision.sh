#!/usr/bin/env bash
# @description PHP Codesniffer
set -eo pipefail

function php_codesniff_setup() {
  export COMPOSER_ALLOW_SUPERUSER=1
  export COMPOSER_NO_INTERACTION=1

  # PHP_CodeSniffer (for running WordPress-Coding-Standards)
  # Sniffs WordPress Coding Standards
  vvv_info " * Install/Update PHP_CodeSniffer (phpcs), see https://github.com/squizlabs/PHP_CodeSniffer"
  vvv_info " * Install/Update WordPress-Coding-Standards, sniffs for PHP_CodeSniffer, see https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards"

  if [[ -f "/srv/www/phpcs/CodeSniffer.conf" ]]; then
    vvv_info " * Upgrading from old PHPCS setup"
    rm -rf /srv/www/phpcs
  fi
  noroot mkdir -p /srv/www/phpcs
  cd /srv/www/phpcs
  COMPOSER_BIN_DIR="bin" noroot composer require --update-with-all-dependencies "dealerdirect/phpcodesniffer-composer-installer" "wp-coding-standards/wpcs" "automattic/vipwpcs" "phpcompatibility/php-compatibility" "phpcompatibility/phpcompatibility-paragonie" "phpcompatibility/phpcompatibility-wp" --no-ansi --no-progress

  # Link `phpcbf` and `phpcs` to the `/usr/local/bin` directory so
  # that it can be used on the host in an editor with matching rules
  ln -sf "/srv/www/phpcs/bin/phpcbf" "/usr/local/bin/phpcbf"
  ln -sf "/srv/www/phpcs/bin/phpcs" "/usr/local/bin/phpcs"

  # Install the standards in PHPCS
  noroot phpcs --config-set default_standard WordPress-Core
  noroot phpcs -i
}
export -f php_codesniff_setup

vvv_add_hook after_composer php_codesniff_setup
