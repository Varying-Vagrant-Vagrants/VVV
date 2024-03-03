#!/usr/bin/env bash
set -eo pipefail

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
DNS_SERVERS=(
  # Quad9
  "9.9.9.9"
  "149.112.112.112"
  "2620:fe::fe"
  "2620:fe::9"

  # Cloudflare
  "1.1.1.1"
  "1.0.0.2"
  "2606:4700:4700::1112"
  "2606:4700:4700::1002"
)

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

# Function to test DNS resolution for a specified domain
function vvv_check_domain_dns() {
    local domain="${1}"

    if [ -z "${domain}" ]; then
        vvv_Error "Error: No domain provided."
        return 1
    fi

    if command -v nslookup &> /dev/null; then
        nslookup "${domain}" >/dev/null 2>&1
    elif command -v dig &> /dev/null; then
        dig +short "${domain}" >/dev/null 2>&1
    else
        vvv_error "Error: Neither nslookup nor dig is available."
        return 1
    fi
}

# @description Tests for working DNS and re-configures on failure
function setup_dns() {
  if vvv_check_domain_dns "github.com" && vvv_check_domain_dns "ppa.launchpadcontent.net"; then
    return
  fi

  vvv_warn "Could not resolve github.com or ppa.launchpadcontent.net domains via DNS, configuring 3rd party DNS resolvers."

  DNS_SERVERS_TO_ADD=()

  # Test reachability of DNS servers
  for dns_server in "${DNS_SERVERS[@]}"; do
      if ping -c 1 -W 2 "${dns_server}" >/dev/null 2>&1; then
          echo "DNS server ${dns_server} is reachable"
          DNS_SERVERS_TO_ADD+=("${dns_server}")
      fi
  done

  vvv_info "Adding DNS Servers: ${DNS_SERVERS_TO_ADD[*]}"

  for file in /etc/netplan/*.yaml; do
      for dns_server in "${DNS_SERVERS_TO_ADD[@]}"; do
          if ! grep -qF "${dns_server}" "${file}"; then
              sed -i "/nameservers:/a \ \ \ \ addresses: [${dns_server}]" "${file}"
              echo "DNS updated to ${dns_server} in ${file}"
          fi
      done
  done

  netplan apply
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
  cp -f "${DIR}/motd/00-vvv-bash-splash" "/etc/update-motd.d/00-vvv-bash-splash"
  chmod +x /etc/update-motd.d/00-vvv-bash-splash
}

# @description Sets up the VVV users bash profile, and configuration files
# @noargs
function profile_setup() {
  chown -R vagrant:vagrant /home/vagrant/

  # Copy custom dotfiles and bin file for the vagrant user from local
  rm -f "/home/vagrant/.bash_profile"
  noroot cp -f "${DIR}/homedir/.bash_profile" "/home/vagrant/.bash_profile"

  rm -f "/home/vagrant/.bash_aliases"
  noroot cp -f "${DIR}/homedir/.bash_aliases" "/home/vagrant/.bash_aliases"

  rm -f "${HOME}/.bash_aliases"
  cp -f "${DIR}/homedir/.bash_aliases" "${HOME}/.bash_aliases"

  rm -f "/home/vagrant/.vimrc"
  noroot cp -f "${DIR}/homedir/.vimrc" "/home/vagrant/.vimrc"

  if [[ ! -d "/home/vagrant/.subversion" ]]; then
    noroot mkdir -p "/home/vagrant/.subversion"
  fi

  rm -f /home/vagrant/.subversion/servers
  noroot cp "${DIR}/homedir/.subversion/subversion-servers" "/home/vagrant/.subversion/servers"

  rm -f /home/vagrant/.subversion/config
  noroot cp "${DIR}/homedir/.subversion/subversion-config" "/home/vagrant/.subversion/config"

  # If a bash_prompt file exists in the VVV config/ directory, copy to the VM.
  if [[ -f "/srv/config/bash_prompt" ]]; then
    rm -f /home/vagrant/.bash_prompt
    noroot cp "/srv/config/bash_prompt" "/home/vagrant/.bash_prompt"
  fi

  if [ -d "/etc/ssh" ]; then
    cp -f "${DIR}/ssh/ssh_known_hosts" /etc/ssh/ssh_known_hosts
    cp -f "${DIR}/ssh/sshd_config" /etc/ssh/sshd_config
    vvv_info " * Reloading SSH Daemon"
    if ! sudo service ssh reload; then
      vvv_error " ! SSH daemon failed to reload"
      return 1
    fi
  fi
}

# @description Sets up the main VVV user profile
# @noargs
function vvv_init_profile() {
  # Profile_setup
  vvv_info " * Bash profile setup and directories."
  setup_dns
  setup_vvv_env
  cleanup_terminal_splash
  profile_setup
}

export -f vvv_init_profile;

vvv_add_hook init vvv_init_profile 0
