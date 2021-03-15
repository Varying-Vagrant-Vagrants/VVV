#!/usr/bin/env bash
# @description Installs MailHog for email interception
set -eo pipefail

function mailhog_setup() {
  if [[ -f "/etc/init/mailcatcher.conf" ]]; then
    vvv_info " * Cleaning up old mailcatcher.conf"
    rm -f /etc/init/mailcatcher.conf
  fi

  if [[ ! -e /usr/local/bin/mailhog ]]; then
    vvv_info " * Installing MailHog"

    if [[ "arm64" == $(uname -m) ]]; then
      curl --silent -L -o /usr/local/bin/mailhog https://github.com/mailhog/MailHog/releases/download/v1.0.0/MailHog_linux_arm
    else
      curl --silent -L -o /usr/local/bin/mailhog https://github.com/mailhog/MailHog/releases/download/v1.0.0/MailHog_linux_amd64
    fi
    chmod +x /usr/local/bin/mailhog
    vvv_success " * Mailhog binary installed"
  fi
  if [[ ! -e /usr/local/bin/mhsendmail ]]; then
    vvv_info " * Installing MHSendmail"
    if [[ "arm64" == $(uname -m) ]]; then
      curl --silent -L -o /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_arm
    else
      curl --silent -L -o /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64
    fi
    chmod +x /usr/local/bin/mhsendmail
    vvv_success " * MHSendmail installed"
  fi

  if [[ ! -e /etc/systemd/system/mailhog.service ]]; then
    vvv_info " * Mailhog service file missing, setting up"
    # Make it start on reboot
    cp -f "/srv/provision/core/mailhog/mailhog.service" "/etc/systemd/system/mailhog.service"
  fi

  # Start on reboot
  if [ "${VVV_DOCKER}" != 1 ]; then
    vvv_info " * Enabling MailHog Service"
    systemctl enable mailhog

    vvv_info " * Starting MailHog Service"
    systemctl start mailhog
  fi
}
export -f mailhog_setup

vvv_add_hook after_packages mailhog_setup

if [ "${VVV_DOCKER}" != 1 ]; then
  vvv_add_hook services_restart "service mailhog restart"
fi

function mailhog_php_finalize() {
  # Enable PHP MailHog sendmail settings by default
  vvv_info " * Enabling MailHog for PHP"
  phpenmod -s ALL mailhog
}

vvv_add_hook php_finalize mailhog_php_finalize
