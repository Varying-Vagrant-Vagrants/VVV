#!/usr/bin/env bash
# @description PHP Codesniffer
set -eo pipefail

function php_codesniff_setup() {
  export COMPOSER_ALLOW_SUPERUSER=1
  export COMPOSER_NO_INTERACTION=1

  if [[ -f "/srv/www/phpcs/CodeSniffer.conf" ]]; then
    vvv_info " * Upgrading from old PHPCS setup"
    rm -rf /srv/www/phpcs
  fi
  
  # PHP_CodeSniffer (for running WordPress-Coding-Standards)
  # Sniffs WordPress Coding Standards
  vvv_info " * Provisioning PHP_CodeSniffer (phpcs), see https://github.com/squizlabs/PHP_CodeSniffer"

  noroot mkdir -p /srv/www/phpcs
  cd /srv/www/phpcs
  COMPOSER_BIN_DIR="bin" noroot composer require --update-with-all-dependencies "dealerdirect/phpcodesniffer-composer-installer" "wp-coding-standards/wpcs" "automattic/vipwpcs" "phpcompatibility/php-compatibility" "phpcompatibility/phpcompatibility-paragonie" "phpcompatibility/phpcompatibility-wp" --no-ansi --no-progress
  
  vvv_info " * Symlinking phpcs and phcbf into /usr/local/bin"

  # Link `phpcbf` and `phpcs` to the `/usr/local/bin` directory so
  # that it can be used on the host in an editor with matching rules
  ln -sf "/srv/www/phpcs/bin/phpcbf" "/usr/local/bin/phpcbf"
  ln -sf "/srv/www/phpcs/bin/phpcs" "/usr/local/bin/phpcs"
  
  vvv_info " * Setting WordPress-Core as the default PHPCodesniffer standard"

  # Install the standards in PHPCS
  noroot phpcs --config-set default_standard WordPress-Core
  vvv_info " * The following PHPCS standards are set up:"
  noroot phpcs -i
  vvv_success " * PHPCS provisioning has ended"
}
export -f php_codesniff_setup

vvv_add_hook after_composer php_codesniff_setup
