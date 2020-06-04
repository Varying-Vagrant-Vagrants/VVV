#!/bin/bash
#
# provision-network-functions.sh
#
# This file is for common network helper functions that get called in
# other provisioners

export YELLOW="\033[38;5;3m"
export GREEN="\033[38;5;2m"
export RED="\033[38;5;9m"
export CRESET="\033[0m"
export BOLD="\033[1m"

VVV_CONFIG=/vagrant/vvv-custom.yml
if [[ -f /vagrant/config.yml ]]; then
	VVV_CONFIG=/vagrant/config.yml
fi

export VVV_CONFIG
export VVV_CURRENT_LOG_FILE=""

function containsElement () {
  declare -a array=(${2})
  local i
  for i in "${array[@]}"
  do
      if [ "${i}" == "${1}" ] ; then
          return 0
      fi
  done
  return 1
}
export -f containsElement

function network_detection() {
  url=${1:-"https://ppa.launchpad.net"}
  check_network_connection_to_host "${url}"
}
export -f network_detection

function check_network_connection_to_host() {
  url=${1:-"https://ppa.launchpad.net"}
  echo " * Testing network connection to ${url}"

  # Network Detection
  #
  # If 3 attempts with a timeout of 5 seconds are not successful,
  # then we'll skip a few things further in provisioning rather
  # than create a bunch of errors.
  if [[ "$(wget --tries=3 --timeout=10 "${url}" -O /dev/null 2>&1 | grep 'connected')" ]]; then
    vvv_success " * Successful Network connection to ${url} detected"
    return 0
  fi
  vvv_error " ! Network connection issues found. Unable to reach ${url}"
  return 1
}
export -f check_network_connection_to_host

function network_check() {
  # Make an HTTP request to ppa.launchpad.net to determine if
  # outside access is available to us. Also check the mariadb
  declare -a hosts_to_test=(
    "https://ppa.launchpad.net"
    "https://wordpress.org"
    "https://github.com"
    "https://raw.githubusercontent.com"
    "https://getcomposer.org"
    "http://ams2.mirrors.digitalocean.com"
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
    echo "VVV tried to ping several domains it needs but some failed:"
    echo " "
    for i in "${hosts_to_test[@]}"; do
      local url="${i}"
      if containsElement "${i}" "${failed_hosts}"; then
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
    echo -e "${RED} "
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
    vvv_error "#################################################################"
    return 1
  fi
  vvv_success " * Network checks succeeded"
  return 0
}
export -f network_check

function log_to_file() {
	local date_time=$(cat /vagrant/provisioned_at)
	local logfolder="/var/log/provisioners/${date_time}"
	local logfile="${logfolder}/${1}.log"
	mkdir -p "${logfolder}"
	touch "${logfile}"
	# reset output otherwise it will log to previous files. from backup made in provisioners.sh
	exec 1>&6
	exec 2>&7
	# pipe to file
	if [[ "${1}" == "provisioner-main" ]]; then
		exec > >( tee -a "${logfile}" ) # main provisioner outputs everything
	else
		exec > >( tee -a "${logfile}" >/dev/null ) # others, only stderr
	fi
	exec 2> >( tee -a "${logfile}" >&2 )
	VVV_CURRENT_LOG_FILE="${logfile}"
}
export -f log_to_file

function noroot() {
  sudo -EH -u "vagrant" "$@";
}
export -f noroot

function vvv_info() {
  echo -e "${CRESET}${1}${CRESET}"
  if [ "${VVV_LOG}" != "main" ]; then
    >&6 echo -e "${CRESET}${1}${CRESET}"
  fi
}
export -f vvv_info

function vvv_error() {
	echo -e "${RED}${1}${CRESET}"
}
export -f vvv_error

function vvv_warn() {
	echo -e "${YELLOW}${1}${CRESET}"
  if [ "${VVV_LOG}" != "main" ]; then
  	>&6 echo -e "${YELLOW}${1}${CRESET}"
  fi
}
export -f vvv_warn

function vvv_success() {
	echo -e "${GREEN}${1}${CRESET}"
  if [ "${VVV_LOG}" != "main" ]; then
  	>&6 echo -e "${GREEN}${1}${CRESET}"
  fi
}
export -f vvv_success

function get_config_value() {
  local value=$(shyaml get-value "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value:-$2}"
}
export -f get_config_value

function get_config_values() {
  local value=$(shyaml get-values "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value:-$2}"
}
export -f get_config_values

function get_config_type() {
  local value=$(shyaml get-type "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value}"
}
export -f get_config_type

function get_config_keys() {
  local value=$(shyaml keys "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value:-$2}"
}
export -f get_config_keys
