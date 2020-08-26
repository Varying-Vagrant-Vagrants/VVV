#!/bin/bash

php_codesniff_setup() {
  export DEBIAN_FRONTEND=noninteractive

  # PHP_CodeSniffer (for running WordPress-Coding-Standards)
  # Sniffs WordPress Coding Standards
  echo -e "\n * Install/Update PHP_CodeSniffer (phpcs), see https://github.com/squizlabs/PHP_CodeSniffer"
  echo -e "\n * Install/Update WordPress-Coding-Standards, sniffs for PHP_CodeSniffer, see https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards"
  cd /srv/provision/phpcs
  noroot composer update --no-ansi --no-autoloader --no-progress

  # Link `phpcbf` and `phpcs` to the `/usr/local/bin` directory so
  # that it can be used on the host in an editor with matching rules
  ln -sf "/srv/www/phpcs/bin/phpcbf" "/usr/local/bin/phpcbf"
  ln -sf "/srv/www/phpcs/bin/phpcs" "/usr/local/bin/phpcs"

  # Install the standards in PHPCS
  phpcs --config-set installed_paths ./CodeSniffer/Standards/WordPress/,./CodeSniffer/Standards/VIP-Coding-Standards/,./CodeSniffer/Standards/PHPCompatibility/,./CodeSniffer/Standards/PHPCompatibilityParagonie/,./CodeSniffer/Standards/PHPCompatibilityWP/
  phpcs --config-set default_standard WordPress-Core
  phpcs -i
}
export -f php_codesniff_setup

vvv_add_hook after_composer php_codesniff_setup