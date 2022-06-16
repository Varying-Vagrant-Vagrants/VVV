#!/bin/bash
# @description This file is for common helper functions that
# get called in other provisioners

export DEBIAN_FRONTEND=noninteractive

export DEFAULT_TEXT="\033[39m"
export BOLD="\033[1m"
export UNBOLD="\033[21m"
export DIM="\033[2m"
export UNDIM="\033[21m"
export UNDERLINE="\033[4m"
export NOUNDERLINNE="\033[24m"
export YELLOW="\033[0;38;5;3m"
export YELLOW_UNDERLINE="\033[4;38;5;3m"
export GREEN="\033[0;38;5;2m"
export RED="\033[0;38;5;9m"
export BLUE="\033[0;38;5;4m" # 33m"
export PURPLE="\033[0;38;5;5m" # 129m"
export CRESET="\033[0m"


VVV_CONFIG=/vagrant/vvv-custom.yml
if [[ -f /vagrant/config.yml ]]; then
	VVV_CONFIG=/vagrant/config.yml
fi

export VVV_CONFIG
export VVV_CURRENT_LOG_FILE=""

# @description Does a bash array contain a value?
#
# @arg $1 string The value to search for
# @arg $2 string The list/array to search in
#
# @exitcode 0 If the list contains the element
# @exitcode 1 If the list does not containn the element
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

# @description Test that we have network connectivity with a URL.
# Deprecated, use check_network_connection_to_host instead
#
# @arg $1 string The address to test
# @see check_network_connection_to_host
function network_detection() {
  local url=${1:-"https://ppa.launchpadcontent.net"}
  check_network_connection_to_host "${url}"
}
export -f network_detection

# @description Test that we have network connectivity with a URL.
#
# @arg $1 string The address to test, defaults to `https://ppa.launchpadcontent.net`
#
# @exitcode 0 If the address is reachable
# @exitcode 1 If network issues are found
function check_network_connection_to_host() {
  local url=${1:-"http://ppa.launchpadcontent.net"}
  vvv_info " * Testing network connection to <url>${url}</url> with wget -q --spider --timeout=5 --tries=3 ${url}"

  # Network Detection
  #
  # If 3 attempts with a timeout of 5 seconds are not successful,
  # then we'll skip a few things further in provisioning rather
  # than create a bunch of errors.
  if wget -q --spider --timeout=5 --tries=3 "${url}"; then
    vvv_success " * Successful Network connection to <url>${url}</url><success> detected"
    return 0
  fi
  vvv_error " ! Network connection issues found. Unable to reach <url>${url}</url>"
  return 1
}
export -f check_network_connection_to_host

# @description Tests network connectivity with several hosts needed for provisioning
# @noargs
# @see check_network_connection_to_host
function network_check() {
  if [ "${VVV_DOCKER}" == 1 ]; then
    return 0
  fi

  # Make an HTTP request to ppa.launchpadcontent.net to determine if
  # outside access is available to us. Also check the mariadb mirrors.
  #
  # If you need to modify this list, contact us on GitHub with the changes.
  declare -a hosts_to_test=(
    "https://ppa.launchpadcontent.net" # needed for core ubuntu packages
    "https://wordpress.org" # WordPress!!
    "https://github.com" # needed for dashboard, extensions, etc
    "https://raw.githubusercontent.com" # some scripts and provisioners rely on this
    "https://getcomposer.org" # composer is used for lots of sites and provisioners
    "https://deb.nodesource.com" # Node JS installation
    "https://mirror.rackspace.com" # MariaDB mirror
  )
  declare -a failed_hosts=()
  for url in "${hosts_to_test[@]}"; do
    if ! check_network_connection_to_host "${url}" ; then
      failed_hosts+=( "$url" )
    fi
  done

  if (( ${#failed_hosts[@]} )); then
    vvv_error "#################################################################"
    vvv_error " "
    vvv_error "! Network Problem:"
    vvv_error " "
    vvv_error "VVV tried to check several domains it needs to provision but ${#failed_hosts[@]} of ${#hosts_to_test[@]} failed:"
    vvv_error " "
    for url in "${hosts_to_test[@]}"; do
      echo -e "${CRESET} [${RED}x${CRESET}] ${url}${RED}|"
    done
    vvv_error " "
    vvv_error "Make sure you have a working internet connection, that you "
    vvv_error "restarted after installing VirtualBox and Vagrant, and that "
    vvv_error "they aren't blocked by a firewall or security software."
    vvv_error "If you can load the address in your browser, then VVV should"
    vvv_error "be able to connect."
    vvv_error " "
    vvv_error "Also note that some users have reported issues when combined"
    vvv_error "with VPNs, disable your VPN and reprovision to see if this is"
    vvv_error "the cause."
    vvv_error " "
    vvv_error "Additionally, if you're at a contributor day event, be kind,"
    vvv_error "provisioning involves downloading things, a full provision may "
    vvv_error "ruin the wifi for everybody else :("
    vvv_error " "
    if ! command -v ifconfig &> /dev/null; then
      vvv_error "Network ifconfig output:"
      vvv_error " "
      ifconfig
      vvv_error " "
    fi
    vvv_error "Aborting provision. "
    vvv_error "Try provisioning again once network connectivity is restored."
    vvv_error "If that doesn't work, and you're sure you have a strong "
    vvv_error "internet connection, open an issue on GitHub, and include the "
    vvv_error "output above so that the problem can be debugged"
    vvv_error " "
    vvv_error "vagrant reload --provision"
    vvv_error " "
    vvv_error "<url>https://github.com/Varying-Vagrant-Vagrants/VVV/issues</url>"
    vvv_error " "
    vvv_error "#################################################################"
    return 1
  fi
  vvv_success " * Network checks succeeded"
  return 0
}
export -f network_check

# @description Redirects stdout to a log file in the provisioner log folder
#
# @arg $1 string name of the provisioner
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

# @description Run a command that cannot be ran as root
function noroot() {
  sudo -EH -u "vagrant" "$@";
}
export -f noroot

# @description Tests if an apt-key has been added
#
# @arg $1 string a key string to test
function vvv_apt_keys_has() {
  local keys=$( apt-key list )
  if [[ ! $( echo "${keys}" | grep "$1") ]]; then
    return 1
  fi
}
export -f vvv_apt_keys_has

# @description Tests if an apt-source has been added
#
# @arg $1 string a source to test for
function vvv_src_list_has() {
  local STATUS=1
  if [ ! -z "$(ls -A /etc/apt/sources.list.d/)" ]; then
    grep -Rq "^deb.*$1" /etc/apt/sources.list.d/*.list
    STATUS=$?
  fi

  return $STATUS
}
export -f vvv_src_list_has

# @description Takes an input string and attempts to apply terminal formatting for various colours
#
# @example
#   MSG=$(vvv_format_output "<success>green!</success>, <error>red :(</error>, <url>example.com</url></>normal text")
#
# @arg $1 string Text to format
function vvv_format_output() {
  declare -A TAGS=(
    ['<b>']="${CRESET}${BOLD}${PURPLE}"
    ['</b>']="${UNBOLD}"
    ['<info>']="${CRESET}${DEFAULT_TEXT}${DIM}"
    ['</info>']="${UNDIM}"
    ['<success>']="${GREEN}"
    ['</success>']="${CRESET}"
    ['<warn>']="${YELLOW}"
    ['</warn>']="${CRESET}"
    ['<error>']="${RED}"
    ['</error>']="${CRESET}"
    ['<url>']="${CRESET}${YELLOW_UNDERLINE}"
    ['</url>']="${CRESET}"
    ['</>']="${CRESET}"
  )

  local MSG="${1}</>"
  for TAG in "${!TAGS[@]}"; do
    local VAL="${TAGS[$TAG]}"
    MSG=$(echo "${MSG//"${TAG}"/"${VAL}"}" )
  done
  echo -e "${MSG}"
}
export -f vvv_format_output

# @description Output to the terminal, and log to a provisioner log at the same time, with applied formatting
#
# @arg $1 string The message to print
function vvv_output() {
  local MSG=$(vvv_format_output "${1}")
	echo -e "${MSG}"
  if [[ ! -z "${VVV_LOG}" ]]; then
    if [ "${VVV_LOG}" != "main" ]; then
      test -e /proc/$$/fd/6 && >&6 echo -e "${MSG}"
    fi
  fi
}
export -f vvv_output

# @description Prints an information message
#
# @arg $1 string The message to print
function vvv_info() {
  vvv_output "<info>${1}</info>"
}
export -f vvv_info

# @description Prints out an error message
#
# @arg $1 string The message to print
function vvv_error() {
  local MSG=$(vvv_format_output )
  vvv_output "<error>${1}</error>"
}
export -f vvv_error

# @description Prints our a warning message
#
# @arg $1 string The message to print
function vvv_warn() {
  vvv_output "<warn>${1}</warn>"
}
export -f vvv_warn

# @description Prints out a success message
#
# @arg $1 string The message to print
function vvv_success() {
  vvv_output "<success>${1}</success>"
}
export -f vvv_success

# @description Retrieves a config value from the main config YAML file
# Uses `shyaml get-value` internally
#
# @arg $1 string the path/key to read from, e.g. sites.wordpress-one.repo
# @arg $2 string a default value to fall back upon
function get_config_value() {
  local value=$(shyaml get-value "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value:-${2:-}}"
}
export -f get_config_value

# @description Retrieves config values from the main config YAML file
# Uses `shyaml get-values` internally
#
# @arg $1 string the path/key to read from, e.g. sites.wordpress-one.hosts
# @arg $2 string a default value to fall back upon
function get_config_values() {
  local value=$(shyaml get-values "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value:-${2:-}}"
}
export -f get_config_values

# @description Retrieves the type of a config value from the main config YAML file
# Uses `shyaml get-type` internally
#
# @arg $1 string the path/key to read from, e.g. sites.wordpress-one.repo
function get_config_type() {
  local value=$(shyaml get-type "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value}"
}
export -f get_config_type

# @description Retrieves config keys from the main config YAML file
# Uses `shyaml keys` internally
#
# @arg $1 string the path/key to read from, e.g. sites.wordpress-one.repo
# @arg $2 string a default value to fall back upon
function get_config_keys() {
  local value=$(shyaml keys "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value:-${2:-}}"
}
export -f get_config_keys

#
# hook engine
#

# @description Add a bash function to execute on a hook
#
# @example
#   vvv_add_hook init vvv_init_profile 0
#
# @arg $1 string the name of the hook
# @arg $2 string the name of the bash function to call
# @arg $3 number the priority of the function when the hook executes, determines order, lower values execute earlier
vvv_add_hook() {
  if [[ "${1}" =~ [^a-zA-Z_] ]]; then
    vvv_warn "Invalid hookname '${1}', hooks must only contain the characters A-Z and a-z"
    return 1
  fi

  local hook_prio=10
  if [[ ! -z "${3}" && "${3}" =~ [0-9]+ ]]; then

    hook_prio=$((${3} + 0))
    if [[ -z "$hook_prio" ]]; then
      hook_prio=0
    fi
  fi

  local hook_var_prios="VVV_HOOKS_${1}"
  eval "if [ -z \"\${${hook_var_prios}}\" ]; then ${hook_var_prios}=(); fi"

  local hook_var="${hook_var_prios}_${hook_prio}"
  eval "if [ -z \"\${${hook_var}}\" ]; then ${hook_var_prios}+=(${hook_prio}); ${hook_var}=(); fi"
  eval "${hook_var}+=(\"${2}\")"
}
export -f vvv_add_hook

# @description Executes a hook. Functions added to this hook will be executed
#
# @example
#   vvv_hook before_packages
#
# @arg $1 string the hook to execute
vvv_hook() {
  if [[ "${1}" =~ [^a-zA-Z_] ]]; then
    vvv_error " x Disallowed hookname '${1}'"
    return 1
  fi

  local hook_var_prios="VVV_HOOKS_${1}"
  local start=`date +%s`
  vvv_info " ▷ Running <b>${1}</b><info> hook"
  eval "if [ -z \"\${${hook_var_prios}}\" ]; then return 0; fi"
  local sorted
  eval "if [ ! -z \"\${${hook_var_prios}}\" ]; then IFS=$'\n' sorted=(\$(sort -n <<<\"\${${hook_var_prios}[*]}\")); unset IFS; fi"

  for i in ${!sorted[@]}; do
    local prio="${sorted[$i]}"
    hooks_on_prio="${hook_var_prios}_${prio}[@]"
    for f in ${!hooks_on_prio}; do
      $f
    done
  done
  local end=`date +%s`
  vvv_success " ✔ Finished <b>${1}</b><success> hook in </success><b>`expr $end - $start`s</b>"
}
export -f vvv_hook


function vvv_run_parallel_hook_function() {
  eval $1

  # kill all sub-processes
  pkill -P $$
}

export -f vvv_run_parallel_hook_function

# @description Executes a hook. Functions added to this hook will be executed in parallel
#
# @example
#   vvv_parallel_hook before_packages
#
# @arg $1 string the hook to execute
function vvv_parallel_hook() {
  if [[ "${1}" =~ [^a-zA-Z_] ]]; then
    vvv_error " x Disallowed hookname '${1}', aborting"
    return 1
  fi

  local hook_var_prios="VVV_HOOKS_${1}"
  local start=`date +%s`
  eval "if [ -z \"\${${hook_var_prios}}\" ]; then return 0; fi"
  vvv_info " ▷ Running <b>${1}</b><info> hook"
  local sorted
  eval "if [ ! -z \"\${${hook_var_prios}}\" ]; then IFS=$'\n' sorted=(\$(sort -n <<<\"\${${hook_var_prios}[*]}\")); unset IFS; fi"

  for i in ${!sorted[@]}; do
    local prio="${sorted[$i]}"
    hooks_on_prio="${hook_var_prios}_${prio}[@]"
    for f in ${!hooks_on_prio}; do
      vvv_info "   - Starting subhook ${f} with priority ${prio}"
      vvv_run_parallel_hook_function "${f}" &
    done
    wait
    vvv_info "   - Subhooks completed for ${1} with priority ${prio}"

  done
  local end=`date +%s`
  vvv_success " ✔ Finished <b>${1}</b><success> hook in </success><b>`expr $end - $start`s</b>"
}
export -f vvv_parallel_hook

vvv_apt_update() {
  vvv_info " * Updating apt keys"
  apt-key update -y

  # Update all of the package references before installing anything
  vvv_info " * Running apt-get update..."
  rm -rf /var/lib/apt/lists/*
  apt-get update -y --fix-missing
}

vvv_apt_packages_upgrade() {
  vvv_info " * Upgrading apt packages"
  vvv_apt_update
  dpkg --configure -a
  if ! apt-get  -y --allow-downgrades --allow-remove-essential --allow-change-held-packages -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew upgrade --fix-missing --no-install-recommends --fix-broken; then
    vvv_error " * Upgrading apt packages returned a failure code, cleaning up apt caches then exiting"
    apt-get clean -y
    return 1
  fi
}
export -f vvv_apt_packages_upgrade

vvv_apt_cleanup() {
  # Remove unnecessary packages
  vvv_info " * Removing unnecessary apt packages..."
  apt-get autoremove -y

  # Clean up apt caches
  vvv_info " * Cleaning apt caches..."
  apt-get clean -y
}

# @description Installs a selection of packages via `apt`
# @example
#   vvv_package_install wget curl etc
vvv_package_install() {
  declare -a initialPackages=($@)
  declare -a packages

  # Ensure packages are not installed before adding them
  if [ ${#initialPackages[@]} -ne 0 ]; then
    for package in "${initialPackages[@]}"; do
      if ! vvv_is_apt_pkg_installed "${package}"; then
        packages+=("${package}")
      fi
    done
  fi

  if [ ${#packages[@]} -eq 0 ]; then
    vvv_info " * No apt packages to install"
    return 0
  fi

  vvv_cleanup_dpkg_locks
  vvv_apt_update

  # Install required packages
  vvv_info " * Installing apt-get packages..."

  # To avoid issues on provisioning and failed apt installation
  dpkg --configure -a
  if ! apt-get -y --allow-downgrades --allow-remove-essential --allow-change-held-packages -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew install --fix-missing --no-install-recommends --fix-broken ${packages[@]}; then
    vvv_error " * Installing apt-get packages returned a failure code, cleaning up apt caches then exiting"
    apt-get clean -y
    return 1
  fi

  vvv_apt_cleanup

  return 0
}
export -f vvv_package_install;

# @description checks if an apt package is installed, returns 0 if installed, 1 if not
# @arg $1 string the package to check for
vvv_is_apt_pkg_installed() {
    # Get the number of packages installed that match $1
    num=$(dpkg --dry-run -l "${1}" 2>/dev/null | egrep '^ii' | wc -l)

    if [[ $num -eq 1 ]]; then
        # it is installed
        return 0
    elif [[ $num -gt 1 ]]; then
        # there is more than one package matching $1
        return 0
    fi
    return 1
}

# @description cleans up dpkg lock files to avoid provisioning issues
# based on a fix from https://github.com/Varying-Vagrant-Vagrants/VVV/issues/2150
vvv_cleanup_dpkg_locks() {
  vvv_info " * Cleaning up dpkg lock file"
  lockfiles=(/var/lib/dpkg/lock*)
  if [ "${#lockfiles[@]}" ]; then
    rm /var/lib/dpkg/lock*
  fi
}

# @description removes a selection of packages via `apt`
# @example
#   vvv_apt_package_remove wget curl etc
vvv_apt_package_remove() {
  declare -a initialPackages=($@)
  declare -a packages

  # Ensure packages are actually installed before removing them
  if [ ${#initialPackages[@]} -ne 0 ]; then
    for package in "${initialPackages[@]}"; do
      if vvv_is_apt_pkg_installed "${package}"; then
        packages+=("${package}")
      fi
    done
  fi

  if [ ${#packages[@]} -eq 0 ]; then
    vvv_info " * No apt packages to remove"
    return 0
  fi

  vvv_info " * Removing ${#packages[@]} apt packages: '${packages[@]}'."

  vvv_cleanup_dpkg_locks

  # Install required packages
  vvv_info " * Removing apt-get packages..."

  # To avoid issues on provisioning and failed apt installation
  dpkg --configure -a
  if ! apt-get -y --allow-downgrades --allow-remove-essential --allow-change-held-packages -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew remove --fix-missing --no-install-recommends --fix-broken ${packages[@]}; then
    vvv_error " * Removing apt-get packages returned a failure code, cleaning up apt caches then exiting"
    apt-get clean -y
    return 1
  fi

  vvv_apt_cleanup

  return 0
}
export -f vvv_apt_package_remove;

# @description Installs an Nginx config file and reload Nginx.
# If Nginx fails to load after doing this it will print an
# error and attempt to undo the change.
#
# @arg $1 string the path and filename of the nginx config that needs to be installed
# @arg $2 string the file name of the config when installed
# @arg $3 the type of config, valid values as sites and utilities
#
# @example
#    vvv_maybe_install_nginx_config /tmp/nginx-site-config.conf vvv-site-mysite.conf sites
function vvv_maybe_install_nginx_config() {
  SOURCE_FILE="${1}"
  TARGET_NAME="${2}"
  TARGET="${3}"
  TARGET_DIR="/etc/nginx/custom-${3}/"
  TARGET_FILE="${TARGET_DIR}${TARGET_NAME}"
  if [ -f "${TARGET_FILE}" ]; then
    sudo rm -f "${TARGET_FILE}"
  fi

  sudo mkdir -p "${TARGET_DIR}"

  sudo cp -f "${SOURCE_FILE}" "${TARGET_FILE}"

  if ! sudo nginx -t; then
    vvv_error " ! Installing an Nginx config failed! VVV tried to install ${TARGET_NAME} into ${TARGET} from ${SOURCE_FILE} but a syntax test with sudo nginx -t failed!"
    vvv_error " ! VVV is now deleting the config to avoid further breakage"
    sudo rm -f "${TARGET_FILE}"
    return 1
  fi

  sudo service nginx reload

  return 0
}

export -f vvv_maybe_install_nginx_config;
