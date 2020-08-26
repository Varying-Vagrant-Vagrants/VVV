#!/bin/bash
wp_cli_setup() {
  echo " "
  echo " * Installing/updating WP-CLI"
  # WP-CLI Install
  local exists_wpcli

  # Remove old wp-cli symlink, if it exists.
  if [[ -L "/usr/local/bin/wp" ]]; then
    echo " * Removing old wp-cli symlink"
    rm -f /usr/local/bin/wp
  fi

  exists_wpcli="$(which wp)"
  if [[ "/usr/local/bin/wp" != "${exists_wpcli}" ]]; then
    echo " * Downloading wp-cli nightly, see http://wp-cli.org"
    curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli-nightly.phar
    chmod +x wp-cli-nightly.phar
    sudo mv wp-cli-nightly.phar /usr/local/bin/wp

    echo " * Grabbing WP CLI bash completions"
    # Install bash completions
    curl -s https://raw.githubusercontent.com/wp-cli/wp-cli/master/utils/wp-completion.bash -o /srv/config/wp-cli/wp-completion.bash
  else
    echo " * Updating wp-cli..."
    wp --allow-root cli update --nightly --yes
  fi
}
export -f wp_cli_setup

vvv_add_hook after_packages wp_cli_setup