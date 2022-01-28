#!/usr/bin/env bash
# @description WP CLI
set -eo pipefail

function wp_cli_setup() {
  vvv_info " * [WP CLI]: Installing/updating WP CLI"

  # Remove old wp-cli symlink, if it exists.
  if [[ -L "/usr/local/bin/wp" ]]; then
    vvv_info " * [WP CLI]: Removing old WP CLI symlink"
    rm -f /usr/local/bin/wp
  fi

  if [[ ! -f "/usr/local/bin/wp" ]]; then
    vvv_info " * [WP CLI]: Downloading WP CLI nightly, see <url>http://wp-cli.org</url>"
    curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli-nightly.phar /tmp/wp-cli-nightly.phar
    mv -f /tmp/wp-cli-nightly.phar /usr/local/bin/wp
    chown -R vagrant:www-data /usr/local/bin
    chmod +x /usr/local/bin/wp
    vvv_success " * [WP CLI]: WP CLI Nightly Installed"

    vvv_info " * [WP CLI]: Grabbing bash completions"
    # Install bash completions
    mkdir -p /srv/config/wp-cli/
    vvv_info " * [WP CLI]: Downloading bash completions"
    curl -s https://raw.githubusercontent.com/wp-cli/wp-cli/master/utils/wp-completion.bash -o /srv/config/wp-cli/wp-completion.bash
    chown vagrant /srv/config/wp-cli/wp-completion.bash
    vvv_success " * [WP CLI]: Bash completions downloaded"
  else
    vvv_info " * [WP CLI]: Updating WP CLI Nightly"
    chown -R vagrant:www-data /usr/local/bin
    chmod +x /usr/local/bin/wp
    noroot wp cli update --nightly --yes
    vvv_success " * [WP CLI]: WP CLI Nightly updated"
  fi

  # ensure WP CLI is owned by the right user and executable
  chown -R vagrant:www-data /usr/local/bin
  chmod +x /usr/local/bin/wp

  if [ "${VVV_DOCKER}" != 1 ]; then
    vvv_info " * [WP-CLI]: Updating packages"
    noroot wp package update
    vvv_info " * [WP-CLI]: Package updates completed"
  fi

  vvv_success " * [WP-CLI]: WP CLI setup completed"
}
export -f wp_cli_setup

vvv_add_hook tools_setup wp_cli_setup
