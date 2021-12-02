#!/usr/bin/env bash
# @description WP CLI
set -eo pipefail

function wp_cli_setup() {
  vvv_info " * Installing/updating WP-CLI"
  # WP-CLI Install
  local exists_wpcli

  # Remove old wp-cli symlink, if it exists.
  if [[ -L "/usr/local/bin/wp" ]]; then
    vvv_info " * Removing old wp-cli symlink"
    rm -f /usr/local/bin/wp
  fi

  if [[ ! -f "/usr/local/bin/wp" ]]; then
    vvv_info " * Downloading wp-cli nightly, see <url>http://wp-cli.org</url>"
    curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli-nightly.phar
    mv wp-cli-nightly.phar /usr/local/bin/wp
    chown vagrant /usr/local/bin/wp
    chmod +x /usr/local/bin/wp

    vvv_success " * WP CLI Nightly Installed"

    vvv_info " * Grabbing WP CLI bash completions"
    # Install bash completions
    mkdir -p /srv/config/wp-cli/
    vvv_info " * Downloading WP CLI bash completions"
    curl -s https://raw.githubusercontent.com/wp-cli/wp-cli/master/utils/wp-completion.bash -o /srv/config/wp-cli/wp-completion.bash
    chown vagrant /srv/config/wp-cli/wp-completion.bash
  else
    chown vagrant /usr/local/bin/wp
    chmod +x /usr/local/bin/wp
    vvv_info " * Updating wp-cli..."
    noroot wp cli update --nightly --yes
    vvv_success " * WP CLI Nightly updated"
  fi

  # ensure WP CLI is owned by the right user and executable
  chown vagrant /usr/local/bin/wp
  chmod +x /usr/local/bin/wp

  if [ "${VVV_DOCKER}" != 1 ]; then
    vvv_info " * Disabling debug mods if present before running wp package installs"
    xdebug_off

    vvv_info " * Updating WP packages"
    noroot wp package update
    vvv_info " * WP package updates completed"
  fi
}
export -f wp_cli_setup

vvv_add_hook tools_setup wp_cli_setup
