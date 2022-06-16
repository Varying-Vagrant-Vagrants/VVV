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
    CURLOUTPUT=$(curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli-nightly.phar -o /tmp/wp-cli-nightly.phar)
    if [ $? -eq 0 ]; then
      vvv_info " * [WP CLI]: Downloaded, moving wp-cli-nightly.phar"
      mv -f /tmp/wp-cli-nightly.phar /usr/local/bin/wp
      chown -R vagrant:www-data /usr/local/bin
      chmod +x /usr/local/bin/wp
      vvv_success " * [WP CLI]: WP CLI Nightly Installed"
    else
      vvv_error " ! [WP CLI]: wp-cli-nightly.phar failed to download, curl exited with a bad error code ${?}"
      vvv_error "${CURLOUTPUT}"
      return 1
    fi
    vvv_info " * [WP CLI]: Grabbing bash completions"
    # Install bash completions
    mkdir -p /srv/config/wp-cli/
    vvv_info " * [WP CLI]: Downloading bash completions"
    if curl -s https://raw.githubusercontent.com/wp-cli/wp-cli/master/utils/wp-completion.bash -o /srv/config/wp-cli/wp-completion.bash; then
      chown vagrant /srv/config/wp-cli/wp-completion.bash
      vvv_success " * [WP CLI]: Bash completions downloaded"
    else
      vvv_warn " ! [WP CLI]: wp-completion.bash failed to download, curl exited with a bad error code ${?}"
    fi
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
    if noroot wp package update; then
      vvv_info " ✓ [WP-CLI]: Package updates completed"
    else
      vvv_warn " ! [WP-CLI]: Package update did not complete, wp package update exited with a bad error code ${?}"
    fi
  fi

  vvv_success " ✓ [WP-CLI]: WP CLI setup completed"
}
export -f wp_cli_setup

vvv_add_hook tools_setup wp_cli_setup
