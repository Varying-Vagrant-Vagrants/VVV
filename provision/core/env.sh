#!/bin/bash
#
# core/env.sh

setup_vvv_env() {
  # fix no tty warnings in provisioner logs
  sudo sed -i '/tty/!s/mesg n/tty -s \\&\\& mesg n/' /root/.profile

  # add homebin to secure_path setting for sudo, clean first and then append at the end
  sed -i -E \
    -e "s|:/srv/config/homebin||" \
    -e "s|/srv/config/homebin:||" \
    -e "s|(.*Defaults.*secure_path.*?\".*?)(\")|\1:/srv/config/homebin\2|" \
    /etc/sudoers

  # add homebin to the default environment, clean first and then append at the end
  sed -i -E \
    -e "s|:/srv/config/homebin||" \
    -e "s|/srv/config/homebin:||" \
    -e "s|(.*PATH.*?\".*?)(\")|\1:/srv/config/homebin\2|" \
    /etc/environment
}
