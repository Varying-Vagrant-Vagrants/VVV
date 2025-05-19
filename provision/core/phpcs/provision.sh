#!/usr/bin/env bash
# @description PHP Codesniffer
set -eo pipefail

# @noargs
function php_codesniff_setup() {
  export COMPOSER_ALLOW_SUPERUSER=1
  export COMPOSER_NO_INTERACTION=1
  export COMPOSER_RUNTIME_ENV="vagrant"

  if [[ -f "/srv/www/phpcs/CodeSniffer.conf" ]]; then
    vvv_info " * [PHPCS]: Removing the old PHPCS setup"
    rm -rf /srv/www/phpcs
  fi

  # PHP_CodeSniffer (for running WordPress-Coding-Standards)
  # Sniffs WordPress Coding Standards
  vvv_info " * [PHPCS]: Provisioning PHP_CodeSniffer (phpcs), see https://github.com/PHPCSStandards/PHP_CodeSniffer"

  if [ ! -d "/srv/www/phpcs" ]; then
    vvv_info " * Setting up /srv/www/phpcs"
    mkdir -p /srv/www/phpcs
  fi

  cp -f "/srv/provision/core/phpcs/composer.json" "/srv/www/phpcs/composer.json"
  cd /srv/www/phpcs
  COMPOSER_RUNTIME_ENV="vagrant" composer update --no-ansi --no-progress --no-dev --prefer-dist

  if ! stat /srv/www/phpcs | grep -q 'Uid:.*(vagrant)'; then
    vvv_info " * [PHPCS]: Setting up ownership and permissions of /srv/www/phpcs"
    # Change ownership recursively, excluding .git folders
    find /srv/www/phpcs ! -path "*/.git*" -exec chown vagrant:vagrant {} +

    # Change permissions recursively, excluding .git folders
    find /srv/www/phpcs ! -path "*/.git*" -exec chmod u+rwX {} +

    find /srv/www/phpcs ! -path "*/.git*" -type f -exec chmod 644 {} \;
    find /srv/www/phpcs ! -path "*/.git*" -type d -exec chmod 755 {} \;
    chmod +x /srv/www/phpcs/bin/* || vvv_error " ! [PHPCS] chmod -x /srv/www/phpcs/bin/* failed"
    chmod +x /srv/www/phpcs/vendor/squizlabs/php_codesniffer/bin/* || vvv_error " ! [PHPCS] chmod -x /srv/www/phpcs/vendor/squizlabs/php_codesniffer/bin/* failed"
  fi

  vvv_info " * [PHPCS]: Setting WordPress-Core as the default PHPCodesniffer standard"

  # Install the standards in PHPCS
  if noroot php /srv/www/phpcs/bin/phpcs --config-set default_standard WordPress-Core; then
    vvv_success " * [PHPCS]: Successfully set the default PHPCS standard to WordPress-Core."
  else
    vvv_error " ! [PHPCS]: Failed to set the default standard to WordPress-Core."
    vvv_error " ! [PHPCS]: Permissions and owners of /src/www/phpcs/bin are as follows:\n$(ls -al /srv/www/phpcs/bin)"
    if command -v getfacl >/dev/null 2>&1; then
      vvv_error " ! [PHPCS]: getfacl /srv/www/phpcs/bin/phpcs\n$(getfacl /srv/www/phpcs/bin/phpcs)"
    fi
  fi
  local standards
  standards=$(noroot php /srv/www/phpcs/bin/phpcs -i)
  vvv_success " * [PHPCS]: Completed with the following PHPCS standards set up: ${standards}"
  vvv_info " * [PHPCS]: Help maintain PHPCS by sponsoring via Github Sponsors at https://github.com/sponsors/phpcsstandards or OpenCollective at https://opencollective.com/php_codesniffer"
}
export -f php_codesniff_setup

vvv_add_hook after_composer php_codesniff_setup
