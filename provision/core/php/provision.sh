#!/usr/bin/env bash
# @description Installs the default version of PHP
set -eo pipefail

VVV_BASE_PHPVERSION=${VVV_BASE_PHPVERSION:-"8.2"}

function php_before_packages() {
  cp -f "/srv/provision/core/php/ondrej-ppa-pin" "/etc/apt/preferences.d/ondrej-ppa-pin"
}
vvv_add_hook before_packages php_before_packages

# @description Prints the path of the PHP apt source file for this OS and release
function php_apt_source_file() {
  local OSID OSCODENAME
  OSID=$(lsb_release --id --short)
  OSCODENAME=$(lsb_release --codename --short)
  echo "/srv/provision/core/php/sources-${OSID,,}-${OSCODENAME,,}.list"
}

function php_register_apt_sources() {
  local OSID=$(lsb_release --id --short)
  local OSCODENAME=$(lsb_release --codename --short)
  local APTSOURCE
  APTSOURCE=$(php_apt_source_file)
  if [ -f "${APTSOURCE}" ]; then
    cp -f "${APTSOURCE}" "/etc/apt/sources.list.d/vvv-php-sources.list"
  else
    vvv_error " ! VVV could not copy an Apt source file ( ${APTSOURCE} ), the current OS/Version (${OSID,,}-${OSCODENAME,,}) combination is unavailable"
  fi
}
vvv_add_hook register_apt_sources php_register_apt_sources

function php_register_apt_packages() {
  VVV_PACKAGE_LIST+=(
    # PHP
    #
    # Our base packages for php. As long as php*-fpm and php*-cli are
    # installed, there is no need to install the general php* package, which
    # can sometimes install apache as a requirement.
    "php${VVV_BASE_PHPVERSION}-fpm"
    "php${VVV_BASE_PHPVERSION}-cli"

    # Common and dev packages for php
    "php${VVV_BASE_PHPVERSION}-common"
    "php${VVV_BASE_PHPVERSION}-dev"

    # Extra PHP modules that we find useful
    "php-pear"
    "php${VVV_BASE_PHPVERSION}-pcov"
    "php${VVV_BASE_PHPVERSION}-ssh2"
    "php${VVV_BASE_PHPVERSION}-yaml"
    "php${VVV_BASE_PHPVERSION}-bcmath"
    "php${VVV_BASE_PHPVERSION}-curl"
    "php${VVV_BASE_PHPVERSION}-gd"
    "php${VVV_BASE_PHPVERSION}-intl"
    "php${VVV_BASE_PHPVERSION}-mbstring"
    "php${VVV_BASE_PHPVERSION}-mysql"
    "php${VVV_BASE_PHPVERSION}-imap"
    "php${VVV_BASE_PHPVERSION}-soap"
    "php${VVV_BASE_PHPVERSION}-xml"
    "php${VVV_BASE_PHPVERSION}-zip"
    "php${VVV_BASE_PHPVERSION}-gmp"
  )

  # ImageMagick
  VVV_PACKAGE_LIST+=(
    "php${VVV_BASE_PHPVERSION}-imagick"
    imagemagick
  )

  # Xdebug
  VVV_PACKAGE_LIST+=(
    "php${VVV_BASE_PHPVERSION}-xdebug"
  )
}
vvv_add_hook register_apt_packages php_register_apt_packages


# @description Installs the signing key for packages.sury.org, which provides PHP on Ubuntu 22.04 and newer
function php_install_sury_key() {
  local SOURCE_KEY="/srv/provision/core/php/apt-keys/sury-php.gpg"
  local DEST_KEY="/etc/apt/keyrings/sury-php.gpg"
  local KEY_URL="https://packages.sury.org/php/apt.gpg"

  mkdir -p /etc/apt/keyrings

  if [ -f "${DEST_KEY}" ] && ! gpg --show-keys "${DEST_KEY}" 2>/dev/null | grep -q "expired"; then
    vvv_info " * packages.sury.org PHP signing key is up to date"
    return 0
  fi

  if gpg --show-keys "${SOURCE_KEY}" 2>/dev/null | grep -q "expired"; then
    vvv_warn " * The packages.sury.org PHP signing key in VVV has expired, downloading a new one from ${KEY_URL}"
    if ! curl -fsSL "${KEY_URL}" -o "${SOURCE_KEY}"; then
      vvv_error " ! Failed to download the packages.sury.org PHP signing key from ${KEY_URL}"
      return 1
    fi
  fi

  vvv_info " * Installing the packages.sury.org PHP signing key to ${DEST_KEY}"
  if ! cp -f "${SOURCE_KEY}" "${DEST_KEY}"; then
    vvv_error " ! Failed to copy the packages.sury.org PHP signing key to ${DEST_KEY}"
    return 1
  fi
  chmod 644 "${DEST_KEY}"
  vvv_success " * packages.sury.org PHP signing key installed"
}

# @description Installs the signing keys needed by this OS release's PHP apt source
function php_register_apt_keys() {
  # IMPORTANT: Keys must be installed BEFORE sources are registered
  local APTSOURCE
  APTSOURCE=$(php_apt_source_file)

  if grep -q "signed-by=/etc/apt/keyrings/sury-php.gpg" "${APTSOURCE}" 2>/dev/null; then
    php_install_sury_key || return 1
  fi

  if grep -q "signed-by=/etc/apt/keyrings/php.gpg" "${APTSOURCE}" 2>/dev/null; then
    php_install_launchpad_keys || return 1
  elif [ -f /etc/apt/keyrings/php.gpg ] || [ -f /etc/apt/trusted.gpg.d/php.gpg ]; then
    vvv_info " * Removing the Launchpad PPA PHP keys, they are no longer used"
    rm -f /etc/apt/keyrings/php.gpg /etc/apt/trusted.gpg.d/php.gpg
  fi
}
vvv_add_hook register_apt_keys php_register_apt_keys

# @description Installs the Launchpad PPA keys for Ondřej Surý's PHP PPA, used on Ubuntu releases older than 22.04
function php_install_launchpad_keys() {
  # Modern approach: copy GPG key to keyrings directory
  # This replaces the deprecated apt-key add method

  local DEST_KEY="/etc/apt/keyrings/php.gpg"
  local SOURCE_KEY="/srv/provision/core/php/apt-keys/php.gpg"
  local NEEDS_UPDATE=0

  # Launchpad PPA key IDs for Ondřej Surý's PHP PPA
  local KEY_ID_1="71DAEAAB4AD4CAB6"  # 2024 key
  local KEY_ID_2="4F4EA0AAE5267A6C"  # 2009 key (legacy)
  local KEYSERVER="https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x"

  mkdir -p /etc/apt/keyrings

  # Check if we need to update the key
  if [ -f "${DEST_KEY}" ]; then
    # Check if the existing key is expired
    if command -v gpg &> /dev/null; then
      if gpg --show-keys "${DEST_KEY}" 2>/dev/null | grep -q "expired"; then
        vvv_warn " * Installed PHP GPG key has expired, will update"
        NEEDS_UPDATE=1
      else
        # Check if the key contains the required Launchpad key IDs
        local KEY_OUTPUT
        KEY_OUTPUT=$(gpg --show-keys "${DEST_KEY}" 2>/dev/null || echo "")
        if ! echo "${KEY_OUTPUT}" | grep -q "${KEY_ID_1}"; then
          vvv_warn " * PHP GPG key is missing Launchpad PPA key ${KEY_ID_1}, will update"
          NEEDS_UPDATE=1
        fi
      fi
    fi
  else
    # Key doesn't exist
    NEEDS_UPDATE=1
  fi

  # If we need to update, download from Launchpad keyserver
  if [ "${NEEDS_UPDATE}" -eq 1 ] || [ ! -f "${DEST_KEY}" ]; then
    vvv_info " * Downloading Launchpad PPA keys for Ondřej PHP repository"

    # Create temporary file for combining keys
    local TEMP_KEY
    TEMP_KEY=$(mktemp) || {
      vvv_error " ! Failed to create temporary file for key download"
      return 1
    }

    # Download and combine both Launchpad keys
    local DOWNLOAD_SUCCESS=0
    if curl -fsSL "${KEYSERVER}${KEY_ID_1}" -o "${TEMP_KEY}.1.asc" && \
       curl -fsSL "${KEYSERVER}${KEY_ID_2}" -o "${TEMP_KEY}.2.asc"; then
      # Dearmor keys (convert from ASCII to binary format required by APT)
      if gpg --dearmor < "${TEMP_KEY}.1.asc" > "${TEMP_KEY}.1" 2>/dev/null && \
         gpg --dearmor < "${TEMP_KEY}.2.asc" > "${TEMP_KEY}.2" 2>/dev/null; then
        # Combine both binary keys into single keyring
        cat "${TEMP_KEY}.1" "${TEMP_KEY}.2" > "${TEMP_KEY}"
        DOWNLOAD_SUCCESS=1
        vvv_success " * Downloaded and dearmored Launchpad PPA keys"
      else
        vvv_error " ! Failed to dearmor GPG keys"
        rm -f "${TEMP_KEY}" "${TEMP_KEY}.1" "${TEMP_KEY}.2" "${TEMP_KEY}.1.asc" "${TEMP_KEY}.2.asc"
        return 1
      fi
    else
      vvv_error " ! Failed to download Launchpad PPA keys from ${KEYSERVER}"
      rm -f "${TEMP_KEY}" "${TEMP_KEY}.1" "${TEMP_KEY}.2" "${TEMP_KEY}.1.asc" "${TEMP_KEY}.2.asc"

      # Try to use existing source key as fallback
      if [ -f "${SOURCE_KEY}" ]; then
        vvv_warn " * Attempting to use existing key from ${SOURCE_KEY}"
        # Check if source key needs dearmoring
        if head -1 "${SOURCE_KEY}" | grep -q "BEGIN PGP"; then
          vvv_info " * Dearmoring existing key"
          gpg --dearmor < "${SOURCE_KEY}" > "${TEMP_KEY}" 2>/dev/null || {
            cp -f "${SOURCE_KEY}" "${TEMP_KEY}"
          }
        else
          cp -f "${SOURCE_KEY}" "${TEMP_KEY}"
        fi
        DOWNLOAD_SUCCESS=1
      else
        return 1
      fi
    fi

    if [ "${DOWNLOAD_SUCCESS}" -eq 1 ]; then
      # Install the key
      vvv_info " * Installing Ondřej PHP Launchpad PPA keys to ${DEST_KEY}"
      if cp -f "${TEMP_KEY}" "${DEST_KEY}"; then
        chmod 644 "${DEST_KEY}"

        # Also save to source location for future use
        mkdir -p "$(dirname "${SOURCE_KEY}")"
        cp -f "${DEST_KEY}" "${SOURCE_KEY}"

        # Legacy support: also copy to trusted.gpg.d for older Ubuntu versions
        cp -f "${DEST_KEY}" "/etc/apt/trusted.gpg.d/php.gpg"
        chmod 644 /etc/apt/trusted.gpg.d/php.gpg

        vvv_success " * PHP Launchpad PPA keys installed successfully"
      else
        vvv_error " ! Failed to copy PHP GPG key to ${DEST_KEY}"
        rm -f "${TEMP_KEY}" "${TEMP_KEY}.1" "${TEMP_KEY}.2"
        return 1
      fi
    fi

    # Cleanup temporary files
    rm -f "${TEMP_KEY}" "${TEMP_KEY}.1" "${TEMP_KEY}.2" "${TEMP_KEY}.1.asc" "${TEMP_KEY}.2.asc"

    # Verify the key was installed successfully
    if [ ! -f "${DEST_KEY}" ]; then
      vvv_error " ! PHP GPG key was not successfully installed to ${DEST_KEY}"
      return 1
    fi
  else
    vvv_info " * PHP Launchpad PPA GPG key is up to date"
  fi
}

function phpfpm_setup() {
  # Copy php-fpm configs from local
  if [ -d "/etc/php/${VVV_BASE_PHPVERSION}/fpm" ]; then
    vvv_info " * Copying PHP configs"
    cp -f "/srv/config/php-config/php-fpm.conf" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/php-fpm.conf"
    if [ -d "/etc/php/${VVV_BASE_PHPVERSION}/fpm/pool.d" ]; then
      cp -f "/srv/config/php-config/php-www.conf" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/pool.d/www.conf"
    fi
    if [ -d "/etc/php/${VVV_BASE_PHPVERSION}/fpm/conf.d" ]; then
      cp -f "/srv/config/php-config/php-custom.ini" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/conf.d/php-custom.ini"
    fi
  fi

  vvv_info " * Checking supplementary PHP configs"

  for V in /etc/php/*; do
    if [ -d "${V}" ]; then
      if [[ -f "/etc/php/${V}/mods-available/mailcatcher.ini" ]]; then
        vvv_warn " * Cleaning up PHP ${V} mailcatcher.ini from a previous install"
        rm -f "/etc/php/${V}/mods-available/mailcatcher.ini"
      fi
      if [ -d "${V}/mods-available/" ]; then
        cp -f "/srv/config/php-config/mailhog.ini" "${V}/mods-available/mailhog.ini"
        cp -f "/srv/config/php-config/xdebug.ini" "${V}/mods-available/xdebug.ini"
      fi
      if [ -d "${V}/fpm/conf.d/" ]; then
        cp -f "/srv/config/php-config/opcache.ini" "${V}/fpm/conf.d/opcache.ini"
      fi
    fi
  done

  if [[ ! -d "/run/php" ]]; then
    mkdir -p "/run/php"
    chown -R www-data:www-data "/run/php"
  fi
}
export -f phpfpm_setup

vvv_add_hook before_packages phpfpm_setup 50
vvv_add_hook after_packages phpfpm_setup 50

function phpfpm_finalize() {
  # Disable PHP Xdebug module by default
  vvv_info " * Disabling XDebug PHP extension"
  phpdismod xdebug
  phpdismod pcov

  # Add the vagrant user to the www-data group so that it has better access
  # to PHP and Nginx related files.
  usermod -a -G www-data vagrant

  vvv_hook php_finalize
}
export -f phpfpm_finalize

vvv_add_hook finalize phpfpm_finalize

function phpfpm_services_restart() {
  # Restart all php-fpm versions
  if [ ! -f /.dockerenv ]; then
    find /etc/init.d/ -name "php*-fpm" -exec bash -c 'sudo service "$(basename "$0")" restart' {} \;
  fi
}
export -f phpfpm_services_restart

vvv_add_hook services_restart phpfpm_services_restart

function php_nginx_upstream() {
  vvv_info " * Copying /srv/config/php-config/upstream.conf to /etc/nginx/upstreams/php${VVV_BASE_PHPVERSION//.}.conf"
  cp -f "/srv/config/php-config/upstream.conf" "/etc/nginx/upstreams/php${VVV_BASE_PHPVERSION//.}.conf"
}
vvv_add_hook nginx_upstreams php_nginx_upstream

function vvv_php_memcached_register_packages() {
  VVV_PACKAGE_LIST+=(
    "php${VVV_BASE_PHPVERSION}-memcache"
    "php${VVV_BASE_PHPVERSION}-memcached"
  )
}
export -f vvv_php_memcached_register_packages
vvv_add_hook before_packages vvv_php_memcached_register_packages

function vvv_php_redis_register_packages() {
  VVV_PACKAGE_LIST+=(
    "php${VVV_BASE_PHPVERSION}-redis"
  )
}
export -f vvv_php_redis_register_packages
vvv_add_hook before_packages vvv_php_redis_register_packages
