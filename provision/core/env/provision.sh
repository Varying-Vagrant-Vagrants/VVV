#!/usr/bin/env bash
set -eo pipefail

# @description Adds the homebin folder to PATH
# @noargs
function setup_vvv_env() {
  # fix no tty warnings in provisioner logs
  sed -i '/tty/!s/mesg n/tty -s \\&\\& mesg n/' /root/.profile

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

# @description Remove MOTD output from Ubuntu and add our own
# @noargs
function cleanup_terminal_splash() {
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
  if [[ -f /etc/update-motd.d/00-vvv-bash-splash ]]; then
    rm /etc/update-motd.d/00-vvv-bash-splash
  fi
  cp -f "/srv/provision/core/env/motd/00-vvv-bash-splash" "/etc/update-motd.d/00-vvv-bash-splash"
  chmod +x /etc/update-motd.d/00-vvv-bash-splash
}

# @description Sets up the VVV users bash profile, and configuration files
# @noargs
function profile_setup() {
  vvv_info " * Setting ownership of files in /home/vagrant to vagrant"
  chown -R vagrant:vagrant /home/vagrant/

  # Copy custom dotfiles and bin file for the vagrant user from local
  vvv_info " * Copying /srv/provision/core/env/homedir/.bash_profile                      to /home/vagrant/.bash_profile"
  rm -f "/home/vagrant/.bash_profile"
  noroot cp -f "/srv/provision/core/env/homedir/.bash_profile" "/home/vagrant/.bash_profile"

  vvv_info " * Copying /srv/provision/core/env/homedir/.bash_aliases                      to /home/vagrant/.bash_aliases"
  rm -f "/home/vagrant/.bash_aliases"
  noroot cp -f "/srv/provision/core/env/homedir/.bash_aliases" "/home/vagrant/.bash_aliases"

  vvv_info " * Copying /srv/provision/core/env/homedir/.bash_aliases                      to ${HOME}/.bash_aliases"
  rm -f "${HOME}/.bash_aliases"
  cp -f "/srv/provision/core/env/homedir/.bash_aliases" "${HOME}/.bash_aliases"

  vvv_info " * Copying /srv/provision/core/env/homedir/.vimrc                             to /home/vagrant/.vimrc"
  rm -f "/home/vagrant/.vimrc"
  noroot cp -f "/srv/provision/core/env/homedir/.vimrc" "/home/vagrant/.vimrc"

  if [[ ! -d "/home/vagrant/.subversion" ]]; then
    noroot mkdir -p "/home/vagrant/.subversion"
  fi

  vvv_info " * Copying /srv/provision/core/env/homedir/.subversion/subversion-servers                to /home/vagrant/.subversion/servers"
  rm -f /home/vagrant/.subversion/servers
  noroot cp "/srv/provision/core/env/homedir/.subversion/subversion-servers" "/home/vagrant/.subversion/servers"

  vvv_info " * Copying /srv/provision/core/env/homedir/.subversion/subversion-config                 to /home/vagrant/.subversion/config"
  rm -f /home/vagrant/.subversion/config
  noroot cp "/srv/provision/core/env/homedir/.subversion/subversion-config" "/home/vagrant/.subversion/config"

  # If a bash_prompt file exists in the VVV config/ directory, copy to the VM.
  if [[ -f "/srv/config/bash_prompt" ]]; then
    vvv_info " * Copying /srv/config/bash_prompt to /home/vagrant/.bash_prompt"
    rm -f /home/vagrant/.bash_prompt
    noroot cp "/srv/config/bash_prompt" "/home/vagrant/.bash_prompt"
  fi

  if [ -d "/etc/ssh" ]; then
    vvv_info " * Copying /srv/provision/core/env/ssh/ssh_known_hosts                   to /etc/ssh/ssh_known_hosts"
    cp -f /srv/provision/core/env/ssh/ssh_known_hosts /etc/ssh/ssh_known_hosts
    vvv_info " * Copying /srv/provision/core/env/ssh/sshd_config                       to /etc/ssh/sshd_config"
    cp -f /srv/provision/core/env/ssh/sshd_config /etc/ssh/sshd_config
    vvv_info " * Reloading SSH Daemon"
    service ssh reload
  fi
}

# @description Sets up the main VVV user profile
# @noargs
function vvv_init_profile() {
  # Profile_setup
  vvv_info " * Bash profile setup and directories."
  setup_vvv_env
  cleanup_terminal_splash
  profile_setup
}

vvv_add_hook init vvv_init_profile 0
