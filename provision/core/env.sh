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

cleanup_terminal_splash() {
  # Dastardly Ubuntu tries to be helpful and suggest users update packages
  # themselves, but this can break things
  if [[ -f /etc/update-motd.d/00-header ]]; then
    rm /etc/update-motd.d/00-header
  fi
  if [[ -f /etc/update-motd.d/10-help-text ]]; then
    rm /etc/update-motd.d/10-help-text
  fi
  if [[ -f /etc/update-motd.d/50-motd-news ]]; then
    rm /etc/update-motd.d/50-motd-news
  fi
  if [[ -f /etc/update-motd.d/51-cloudguest ]]; then
    rm /etc/update-motd.d/51-cloudguest
  fi
  if [[ -f /etc/update-motd.d/50-landscape-sysinfo ]]; then
    rm /etc/update-motd.d/50-landscape-sysinfo
  fi
  if [[ -f /etc/update-motd.d/80-livepatch ]]; then
    rm /etc/update-motd.d/80-livepatch
  fi
  if [[ -f /etc/update-motd.d/90-updates-available ]]; then
    rm /etc/update-motd.d/90-updates-available
  fi
  if [[ -f /etc/update-motd.d/91-release-upgrade ]]; then
    rm /etc/update-motd.d/91-release-upgrade
  fi
  if [[ -f /etc/update-motd.d/95-hwe-eol ]]; then
    rm /etc/update-motd.d/95-hwe-eol
  fi
  if [[ -f /etc/update-motd.d/98-cloudguest ]]; then
    rm /etc/update-motd.d/98-cloudguest
  fi
  cp -f "/srv/config/update-motd.d/00-vvv-bash-splash" "/etc/update-motd.d/00-vvv-bash-splash"
  chmod +x /etc/update-motd.d/00-vvv-bash-splash
}

profile_setup() {
  echo " * Setting ownership of files in /home/vagrant to vagrant"
  chown -R vagrant:vagrant /home/vagrant/
  # Copy custom dotfiles and bin file for the vagrant user from local
  echo " * Copying /srv/config/bash_profile                      to /home/vagrant/.bash_profile"
  rm -f "/home/vagrant/.bash_profile"
  noroot cp -f "/srv/config/bash_profile" "/home/vagrant/.bash_profile"

  echo " * Copying /srv/config/bash_aliases                      to /home/vagrant/.bash_aliases"
  rm -f "/home/vagrant/.bash_aliases"
  noroot cp -f "/srv/config/bash_aliases" "/home/vagrant/.bash_aliases"

  echo " * Copying /srv/config/bash_aliases                      to ${HOME}/.bash_aliases"
  rm -f "${HOME}/.bash_aliases"
  cp -f "/srv/config/bash_aliases" "${HOME}/.bash_aliases"

  echo " * Copying /srv/config/vimrc                             to /home/vagrant/.vimrc"
  rm -f "/home/vagrant/.vimrc"
  noroot cp -f "/srv/config/vimrc" "/home/vagrant/.vimrc"

  if [[ ! -d "/home/vagrant/.subversion" ]]; then
    noroot mkdir -p "/home/vagrant/.subversion"
  fi

  echo " * Copying /srv/config/subversion-servers                to /home/vagrant/.subversion/servers"
  rm -f /home/vagrant/.subversion/servers
  noroot cp "/srv/config/subversion-servers" "/home/vagrant/.subversion/servers"

  echo " * Copying /srv/config/subversion-config                 to /home/vagrant/.subversion/config"
  rm -f /home/vagrant/.subversion/config
  noroot cp "/srv/config/subversion-config" "/home/vagrant/.subversion/config"

  # If a bash_prompt file exists in the VVV config/ directory, copy to the VM.
  if [[ -f "/srv/config/bash_prompt" ]]; then
    echo " * Copying /srv/config/bash_prompt to /home/vagrant/.bash_prompt"
    rm -f /home/vagrant/.bash_prompt
    noroot cp "/srv/config/bash_prompt" "/home/vagrant/.bash_prompt"
  fi

  echo " * Copying /srv/config/ssh_known_hosts                   to /etc/ssh/ssh_known_hosts"
  cp -f /srv/config/ssh_known_hosts /etc/ssh/ssh_known_hosts
  echo " * Copying /srv/config/sshd_config                       to /etc/ssh/sshd_config"
  cp -f /srv/config/sshd_config /etc/ssh/sshd_config
  echo " * Reloading SSH Daemon"
  systemctl reload ssh
}
