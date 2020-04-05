#!/bin/bash
#
# provision-network-functions.sh
#
# This file is for common network helper functions that get called in
# other provisioners

GREEN="\033[38;5;2m"
RED="\033[38;5;9m"
CRESET="\033[0m"


test() {
  echo $1
  echo $2
}
containsElement () {
  declare -a array=("${!2}")
  for i in "${array[@]}"
  do
      if [ "${i}" == "${1}" ] ; then
          return 0
      fi
  done
  return 1
}

network_detection() {
  url=${1:-"https://ppa.launchpad.net"}
  check_network_connection_to_host $url
}
check_network_connection_to_host() {
  url=${1:-"https://ppa.launchpad.net"}
  echo " * Testing network connection to ${url}"
  # Network Detection
  #
  # If 3 attempts with a timeout of 5 seconds are not successful,
  # then we'll skip a few things further in provisioning rather
  # than create a bunch of errors.
  if [[ "$(wget --tries=3 --timeout=10 --spider --recursive --level=2 "${url}" 2>&1 | grep 'connected')" ]]; then
    echo -e "${GREEN} * Succesful Network connection to ${url} detected...${CRESET}"
    return 0
  fi
  echo -e "${RED} ! Network connection issues found. Unable to reach ${url}...${CRESET}"
  return 1
}

network_check() {
  # Make an HTTP request to ppa.launchpad.net to determine if
  # outside access is available to us. Also check the mariadb
  declare -a hosts_to_test=(
    "https://ppa.launchpad.net"
    "https://mirror.herrbischoff.com"
    "https://wordpress.org"
    "https://github.xxxcom"
    "https://raw.githubusercontent.com"
    "https://beyondgrep.com"
    "https://getcomposer.org"
  )
  declare -a failed_hosts=()
  for url in "${hosts_to_test[@]}"; do
    if ! check_network_connection_to_host "${url}" ; then
      failed_hosts+=( "$url" )
    fi
  done

  if (( ${#failed_hosts[@]} )); then
    echo -e "${RED} "
    echo "#################################################################"
    echo " "
    echo "! Network Problem:"
    echo " "
    echo "Provisioning needs a network connection but none was found."
    echo "VVV tried to ping several domains but some failed:"
    echo " "
    for i in "${hosts_to_test[@]}"; do
      local url="${i}"
      if containsElement "${i}" failed_hosts[@]; then
        echo -e "${CRESET} [${RED}x${CRESET}] ${url}${RED}"
      else
        echo -e "${CRESET} [${GREEN}✓${CRESET}] ${url}${RED}"
      fi
    done
    echo -e "${RED} "
    echo "Make sure you have a working internet connection, that you "
    echo "restarted after installing VirtualBox and Vagrant, and that "
    echo "they aren't blocked by a firewall or security software."
    echo "If you can load the address in your browser, then VVV should"
    echo "be able to connect."
    echo " "
    echo "Also note that some users have reported issues when combined"
    echo "with VPNs, disable your VPN and reprovision to see if this is"
    echo "the cause."
    echo " "
    echo "Additionally, if you're at a contributor day event, be kind,"
    echo "provisioning involves downloading things, a full provision may "
    echo "ruin the wifi for everybody else :("
    echo " "
    echo "Network ifconfig output:"
    echo " "
    ifconfig
    echo " "
    echo "Aborting provision. "
    echo "Try provisioning again once network connectivity is restored."
    echo "If that doesn't work, and you're sure you have a strong "
    echo "internet connection, open an issue on GitHub, and include the "
    echo "output above so that the problem can be debugged"
    echo " "
    echo "vagrant reload --provision"
    echo " "
    echo "https://github.com/Varying-Vagrant-Vagrants/VVV/issues"
    echo " "
    echo "#################################################################${CRESET}"

    return 1
  fi
  echo -e "${GREEN} * Network checks succeeded${CRESET}"
  return 0
}
