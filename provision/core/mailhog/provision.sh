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
    mailhog_bin="https://github.com/mailhog/MailHog/releases/download/v1.0.1/MailHog_linux_amd64"
    if [[ "aarch64" == $(uname -m) ]]; then
      mailhog_bin="https://github.com/evertiro/MailHog/releases/download/v1.0.1-M1/MailHog_linux_arm64"
    fi
    if curl --retry 3 --retry-delay 1 --show-error --silent -L -o /usr/local/bin/mailhog "${mailhog_bin}"; then
      chmod +x /usr/local/bin/mailhog
      vvv_success " * Mailhog binary installed"
    else
      vvv_error " ! MailHog failed to download, error code: '${?}', check that you have a stable reliable network connection then try again. VVV tried to download '${mailhog_bin}'"
      return 1
    fi
  fi
  if [[ ! -e /usr/local/bin/mhsendmail ]]; then
    vvv_info " * Installing MHSendmail"
    mhsendmail_bin="https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64"
    if [[ "aarch64" == $(uname -m) ]]; then
      mhsendmail_bin="https://github.com/evertiro/mhsendmail/releases/download/v0.2.0-M1/mhsendmail_linux_arm64"
    fi
    if curl --retry 3 --retry-delay 1 --show-error --silent -L -o /usr/local/bin/mhsendmail "${mhsendmail_bin}"; then
      chmod +x /usr/local/bin/mhsendmail
      vvv_success " * MHSendmail downloaded"
    else
      vvv_error " ! MHSendmail failed to download, error code: '${?}', check that you have a stable reliable network connection then try again. VVV tried to download '${mhsendmail_bin}'"
      return 1
    fi
  fi

  if [[ -d /etc/systemd/system/ ]]; then
    vvv_info " * Adding Mailhog service file"
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

function mailhog_restart() {
  if [ "${VVV_DOCKER}" != 1 ]; then
    service mailhog restart
  fi
}

vvv_add_hook services_restart mailhog_restart

function mailhog_php_finalize() {
  # Enable PHP MailHog sendmail settings by default
  vvv_info " * Enabling MailHog for PHP"
  if phpenmod -s ALL mailhog; then
    vvv_success " * MailHog enabled"
  fi
}

vvv_add_hook php_finalize mailhog_php_finalize
