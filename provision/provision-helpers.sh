#!/usr/bin/env bash
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

# @description Checks whether a Bash array contains a specific value.
#
# @arg $1 string The value to search for
# @arg $2 string The name of the array variable to search
#
# @exitcode 0 If the array contains the element
# @exitcode 1 If the array does not contain the element
function vvv_array_contains() {
  local needle="$1"
  local array_name="$2"

  # Sanity check
  if [[ -z "$needle" || -z "$array_name" || ! "$(declare -p "$array_name" 2>/dev/null)" =~ "declare -a" ]]; then
    return 1
  fi

  # Create nameref to the array
  declare -n arr="$array_name"
  for item in "${arr[@]}"; do
    if [[ "$item" == "$needle" ]]; then
      return 0
    fi
  done
  return 1
}
export -f vvv_array_contains

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
  local url="${1:-http://ppa.launchpadcontent.net}"

  if [[ -z "${url}" ]]; then
    vvv_error " ! No URL provided to check_network_connection_to_host"
    return 1
  fi

  vvv_info " * Checking network connectivity to <url>${url}</url><info>..."

  if command -v curl >/dev/null 2>&1; then
    if curl -s --connect-timeout 5 --max-time 10 --head "${url}" >/dev/null; then
      vvv_success " ✔ curl connected successfully to <url>${url}</url>"
      return 0
    else
      if command -v wget >/dev/null 2>&1; then
        vvv_warn " - curl failed to connect to <url>${url}</url><warn>, trying wget..."
      else
        vvv_warn " - curl failed to connect to <url>${url}</url>"
      fi
    fi
  fi

  if command -v wget >/dev/null 2>&1; then
    if wget -q --spider --timeout=5 --tries=2 "${url}"; then
      vvv_success " ✔ wget connected successfully to <url>${url}</url>"
      return 0
    fi
  fi

  vvv_error " ✘ Network connection to <url>${url}</url><error> failed via wget and curl"
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
    "https://ppa.launchpadcontent.net"     # Needed for core ubuntu packages
    "https://wordpress.org"                # WordPress!!
    "https://github.com"                   # Needed for dashboard, extensions, etc
    "https://raw.githubusercontent.com"    # Some scripts and provisioners rely on this
    "https://getcomposer.org"              # Composer is used for lots of sites and provisioners
    "https://packagist.org"                # Composer Packages
    "http://mariadb.mirrors.ovh.net"       # MariaDB mirror[ovh]
    "http://ports.ubuntu.com/"
    "https://nginx.org/packages/mainline/" # Nginx
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
    vvv_error "! Warning! Network Problems:"
    vvv_error " "
    vvv_error "VVV tried to check several domains it needs for provisioning but ${#failed_hosts[@]} of ${#hosts_to_test[@]} failed:"
    vvv_error " "
    for url in "${failed_hosts[@]}"; do
      echo -e "${CRESET} [${RED}x${CRESET}] ${url}${RED}|"
    done
    vvv_error " "
    vvv_error "Make sure you have a working internet connection, that you "
    vvv_error "restarted after installing VirtualBox/Parallels/Vagrant, and that "
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
    if command -v ifconfig &> /dev/null; then
      vvv_error "Network ifconfig output:"
      vvv_error " "
      ifconfig
      vvv_error " "
    fi
    vvv_error "Try provisioning again once network connectivity is restored."
    vvv_error "If that doesn't work, and you're sure you have no VPNs and a strong "
    vvv_error "internet connection, open an issue on GitHub, and include the "
    vvv_error "output above so that the problem can be debugged"
    vvv_error " "
    vvv_error "vagrant halt"
    vvv_error "vagrant up --provision"
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
  local provisioner="$1"
  local date_time

  if [[ ! -s /vagrant/provisioned_at ]]; then
    echo "Error: /vagrant/provisioned_at is missing or empty" >&2
    return 1
  fi

  date_time=$(cat /vagrant/provisioned_at)
  local logfolder="/var/log/provisioners/${date_time}"
  local logfile="${logfolder}/${provisioner}.log"


  mkdir -p "${logfolder}" || return 1
  touch "${logfile}" || return 1

  # reset output otherwise it will log to previous files. from backup made in provisioners.sh
  exec 1>&6
  exec 2>&7

  local SED_STRIP_ANSI='s/\x1B\[[0-9;]*[a-zA-Z]//g'

  # pipe to file
  if [[ "${provisioner}" == "provisioner-main" ]]; then
    # Preserve color in terminal, strip in log
    exec > >(
      while IFS= read -r line; do
        printf '%s\n' "$line" | sed -r "${SED_STRIP_ANSI}" >> "${logfile}"
        printf '%s\n' "$line"
      done
    )
  else
    # Suppress stdout to terminal but log stripped version
    exec > >(
      while IFS= read -r line; do
        printf '%s\n' "$line" | sed -r "${SED_STRIP_ANSI}" >> "${logfile}"
      done
    )
  fi

  # stderr: preserve color in terminal, strip in log
  exec 2> >(
    while IFS= read -r line; do
      printf '%s\n' "$line" | sed -r "${SED_STRIP_ANSI}" >> "${logfile}"
      printf '%s\n' "$line" >&2
    done
  )

  VVV_CURRENT_LOG_FILE="${logfile}"

  return 0
}
export -f log_to_file

# @description Run a command that cannot be ran as root, falling back to the current user if not vagrant user is found.
function noroot() {
  if id "vagrant" &>/dev/null; then
    sudo -EH -u "vagrant" "$@"
  else
    vvv_error " ! [noroot] no vagrant user detected, falling back to $(whoami)"
    "$@"  # fallback to running as current user
  fi
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

declare -A VVV_FORMATTING_TAGS=(
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

# @description Takes an input string and attempts to apply terminal formatting for various colours
#
# @example
#   MSG=$(vvv_format_output "<success>green!</success>, <error>red :(</error>, <url>example.com</url></>normal text")
#
# @arg $1 string Text to format
function vvv_format_output() {
  local MSG
  MSG="${1:-}</>"
  local ordered_tags=( '<b>' '</b>' '<info>' '</info>' '<success>' '</success>' '<warn>' '</warn>' '<error>' '</error>' '<url>' '</url>' '</>' )
  for TAG in "${ordered_tags[@]}"; do
    local VAL="${VVV_FORMATTING_TAGS[$TAG]}"
    MSG="${MSG//${TAG}/${VAL}}"
  done
  echo -e "${MSG}"
}
export -f vvv_format_output

# @description Output to the terminal, and log to a provisioner log at the same time, with applied formatting
#
# @arg $1 string The message to print
function vvv_output() {
  local MSG
  MSG=$(vvv_format_output "${1:-}")
  echo -e "${MSG}"

  if [[ -n "${VVV_LOG}" && "${VVV_LOG}" != "main" && -e /proc/$$/fd/6 ]]; then
    >&6 echo -e "${MSG}"
  fi
}
export -f vvv_output

# @description Prints an information message
#
# @arg $1 string The message to print
function vvv_info() {
  vvv_output "<info>${1:-}</info>"
}
export -f vvv_info

# @description Prints out an error message
#
# @arg $1 string The message to print
function vvv_error() {
  vvv_output "<error>${1:-}</error>"
}
export -f vvv_error

# @description Prints our a warning message
#
# @arg $1 string The message to print
function vvv_warn() {
  vvv_output "<warn>${1:-}</warn>"
}
export -f vvv_warn

# @description Prints our a warning message
#
# @arg $1 string The message to print
function vvv_warning() {
  vvv_warn "${1:-}"
}
export -f vvv_warning

# @description Prints out a success message
#
# @arg $1 string The message to print
function vvv_success() {
  vvv_output "<success>${1:-}</success>"
}
export -f vvv_success

# @description Retrieves a config value from the main config YAML file
# Uses `shyaml get-value` internally
#
# @arg $1 string the path/key to read from, e.g. sites.wordpress-one.repo
# @arg $2 string a default value to fall back upon
function get_config_value() {
  local value
  value=$(shyaml get-value "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value:-${2:-}}"
}
export -f get_config_value

# @description Retrieves config values from the main config YAML file
# Uses `shyaml get-values` internally
#
# @arg $1 string the path/key to read from, e.g. sites.wordpress-one.hosts
# @arg $2 string a default value to fall back upon
function get_config_values() {
  local value
  value=$(shyaml get-values "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value:-${2:-}}"
}
export -f get_config_values

# @description Retrieves the type of a config value from the main config YAML file
# Uses `shyaml get-type` internally
#
# @arg $1 string the path/key to read from, e.g. sites.wordpress-one.repo
function get_config_type() {
  local value
  value=$(shyaml get-type "${1}" 2> /dev/null < "${VVV_CONFIG}")
  echo "${value}"
}
export -f get_config_type

# @description Retrieves config keys from the main config YAML file
# Uses `shyaml keys` internally
#
# @arg $1 string the path/key to read from, e.g. sites.wordpress-one.repo
# @arg $2 string a default value to fall back upon
function get_config_keys() {
  local value
  value=$(shyaml keys "${1}" 2> /dev/null < "${VVV_CONFIG}")
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
  # Validate hook name: must start with a letter/underscore, and contain only alphanumeric + underscore
  if [[ ! "$1" =~ ^[a-zA-Z_][a-zA-Z0-9_]*$ ]]; then
    vvv_warn "Invalid hook name '${1}', hooks must match: ^[a-zA-Z_][a-zA-Z0-9_]*$"
    return 1
  fi

  local hook_name="$1"
  local function_name="$2"
  local hook_prio="${3:-10}"

  # Validate priority is a number
  if ! [[ "$hook_prio" =~ ^[0-9]+$ ]]; then
    hook_prio=10
  fi

  local hook_var_prios="VVV_HOOKS_${hook_name}"
  local hook_var="${hook_var_prios}_${hook_prio}"

  # Create arrays if not already defined
  eval "declare -g -a ${hook_var_prios} ${hook_var}"
  eval "if [[ ! \" \${${hook_var_prios}[*]} \" =~ \" ${hook_prio} \" ]]; then ${hook_var_prios}+=(\"${hook_prio}\"); fi"
  eval "${hook_var}+=(\"${function_name}\")"
}
export -f vvv_add_hook

# @description Executes a hook. Functions added to this hook will be executed
#
# @example
#   vvv_hook before_packages
#
# @arg $1 string the hook to execute
vvv_hook() {
  if [[ ! "$1" =~ ^[a-zA-Z_][a-zA-Z0-9_]*$ ]]; then
    vvv_error " x Disallowed hook name '${1}'"
    return 1
  fi

  local hook_name="$1"
  local hook_var_prios="VVV_HOOKS_${hook_name}"
  local start_time end_time elapsed_str=""

  start_time="$(date +%s.%N)"
  vvv_info " ▷ Running <b>${hook_name}</b><info> hook"

  # Check if any hooks registered
  eval "local prios=(\"\${${hook_var_prios}[@]}\")"
  if [[ ${#prios[@]} -eq 0 ]]; then
    return 0
  fi

  # Sort priorities
  IFS=$'\n' read -r -d '' -a sorted < <(printf "%s\n" "${prios[@]}" | sort -n && printf '\0')
  unset IFS

  for prio in "${sorted[@]}"; do
    local hook_var="${hook_var_prios}_${prio}"
    eval "local funcs=(\"\${${hook_var}[@]}\")"

    for f in "${funcs[@]}"; do
      if declare -f "$f" >/dev/null; then
        "$f"
      else
        vvv_warn "Function '${f}' not defined, skipping"
      fi
    done
  done

  end_time="$(date +%s.%N)"
  elapsed_str=$(awk -v start="$start_time" -v end="$end_time" 'BEGIN {
    diff = end - start
    m = int(diff / 60)
    s = int(diff % 60)
    ms = int((diff - int(diff)) * 1000)

    str = ""
    if (m > 0) str = str m "m "
    if (s > 0 || m > 0) str = str s "s "
    str = str ms "ms"
    print str
  }')

  vvv_success " ✔ Finished <b>${hook_name}</b><success> hook in </success><b>${elapsed_str}</b>"
  vvv_log_timing_event "hook" "${hook_name}" "${start_time}" "${end_time}" "${elapsed_str}" "success"
}
export -f vvv_hook

# @description Necessary for vvv_parallel_hook, do not use.
# @internal
function vvv_run_parallel_hook_function() {
  eval "${1}"

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
  local start
  start=$(date +%s)
  eval "if [ -z \"\${${hook_var_prios}}\" ]; then return 0; fi"
  vvv_info " ▷ Running <b>${1}</b><info> hook"
  local sorted
  eval "if [ ! -z \"\${${hook_var_prios}}\" ]; then IFS=$'\n' sorted=(\$(sort -n <<<\"\${${hook_var_prios}[*]}\")); unset IFS; fi"

  for i in "${!sorted[@]}"; do
    local prio="${sorted[$i]}"
    hooks_on_prio="${hook_var_prios}_${prio}[@]"
    for f in ${!hooks_on_prio}; do
      vvv_info "   - Starting subhook ${f} with priority ${prio}"
      vvv_run_parallel_hook_function "${f}" &
    done
    wait
    vvv_info "   - Subhooks completed for ${1} with priority ${prio}"

  done
  local end
  end=$(date +%s)
  vvv_success " ✔ Finished <b>${1}</b><success> hook in </success><b>$((end - start))s</b>"
}
export -f vvv_parallel_hook

# @description Updates Apt keys then fetches Apt updates.
vvv_apt_update() {
  vvv_info " * Updating apt keys"
  apt-key update -y

  # Update all of the package references before installing anything
  vvv_info " * Running apt-get update..."
  rm -rf /var/lib/apt/lists/*
  apt-get update -y --fix-missing
}

# @description Upgrades all Apt packages.
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

# @description Performs Apt autoremoval and cleanup.
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

# @description Checks if an APT package or virtual package is installed. Returns 0 if installed or provided, 1 if not.
# @arg $1 string The package or virtual package name to check
vvv_is_apt_pkg_installed() {
  local pkg="$1"

  # Reject empty or invalid input
  if [[ -z "$pkg" || "$pkg" =~ [^a-zA-Z0-9+.-] ]]; then
    vvv_warn "Invalid or missing package name passed to vvv_is_apt_pkg_installed: '$pkg'"
    return 1
  fi

  # Check if package is installed directly
  if dpkg-query -W -f='${Status}' "$pkg" 2>/dev/null | grep -q "install ok installed"; then
    return 0
  fi

  # Check if it's a virtual package
  if apt-cache show "$pkg" 2>/dev/null | grep -q "^Provides:"; then
    local providers
    providers=$(apt-cache show "$pkg" | awk '/^Provides:/ {for(i=2;i<=NF;++i) print $i}')

    for prov in $providers; do
      if dpkg-query -W -f='${Status}' "$prov" 2>/dev/null | grep -q "install ok installed"; then
        return 0
      fi
    done
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

  vvv_info " * Removing ${#packages[@]} apt packages: '${packages[*]}'."

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

  if sudo service nginx status > /dev/null; then
    sudo service nginx reload
  else
    sudo service nginx start
  fi

  return 0
}
export -f vvv_maybe_install_nginx_config;

# @description Retrieves a list of sites.
# @noargs
function vvv_get_sites() {
  local sites
  sites=$(shyaml -q keys "sites" <${VVV_CONFIG})
  echo "${sites}"
}
export -f vvv_get_sites

# @description Updates the guest environments hosts file.
# @noargs
function vvv_update_guest_hosts() {
  local SITES
  SITES=$(vvv_get_sites)
  cp -f /etc/hosts /tmp/hosts

  # Add each site.
  for SITE in $SITES; do
    SITE_ESCAPED="${SITE//./\\.}"
    VVV_SITE_NAME=${SITE}
    local value
    value=$(shyaml -q get-values "sites.${SITE_ESCAPED}.hosts" <${VVV_CONFIG})
    for v in $value; do
      sed -i "/127.0.0.1 ${v:-"${VVV_SITE_NAME}.test"}/d" /tmp/hosts
      if [[ -z "$(grep -q "^127.0.0.1 ${v:-"${VVV_SITE_NAME}.test"}$" /tmp/hosts)" ]]; then
        echo "127.0.0.1 ${v:-"${VVV_SITE_NAME}.test"} # vvv-auto" >> "/tmp/hosts"
        echo "::1 ${v:-"${VVV_SITE_NAME}.test"} # vvv-auto" >> "/tmp/hosts"
      fi
    done
  done

  # Remove duplicate lines then replace hosts file.
  awk -i inplace '!seen[$0]++' /tmp/hosts

  cp -f /tmp/hosts /etc/hosts
  rm /tmp/hosts
}
export -f vvv_update_guest_hosts

# @description Performs an in place sed command via a temporary
# file to avoid permission issues
function vvv_safe_sed() {
  local expression="${1}"
  local file="${2}"
  local tempfile
  tempfile=$(mktemp /tmp/safe-sed.XXXXXX)
  /usr/bin/sed "${expression}" "${file}" > "${tempfile}"
  cat "${tempfile}" > "${file}"
  rm "${tempfile}"
}
export -f vvv_safe_sed

# @description Takes a string and replaces all instances of a token with a value
function vvv_search_replace() {
  local content="$1"
  local token="$2"
  local value="$3"

  # Read the file contents and replace the token with the value
  content=${content//$token/$value}
  echo "${content}"
}
export -f vvv_search_replace

# @description Takes a file, and replaces all instances of a token with a value
function vvv_search_replace_in_file() {
  local file="$1"

  # Read the file contents and replace the token with the value
  local content
  if [[ -f "${file}" ]]; then
    content=$(<"${file}")
    vvv_search_replace "${content}" "${2}" "${3}"
  else
    return 1
  fi
}
export -f vvv_search_replace_in_file

# @description log a time duration for a hook or provisioner for performance tracking to a csv file.
vvv_log_timing_event() {
  local type="$1"
  local name="$2"
  local start="$3"
  local end="$4"
  local duration="$5"
  local status="$6"

  if [[ "$type" != "hook" && "$type" != "provisioner" ]]; then
    vvv_error " ! Invalid timing event type: '$type'"
    return 1
  fi

  if [[ "$status" != "success" && "$status" != "failure" ]]; then
    vvv_error " ! Invalid timing event status: '$status'"
    return 1
  fi

  if [[ ! -f /vagrant/provisioned_at ]]; then
    vvv_warn " ! /vagrant/provisioned_at is missing, cannot log timing event"
    return 1
  fi

  local date_time
  date_time="$(cat /vagrant/provisioned_at)"
  local log_dir="/var/log/provisioners/timing"
  mkdir -p "$log_dir"

  local csv_log="${log_dir}/timing-${date_time}.csv"
  local json_log="${log_dir}/timing-${date_time}.jsonl"

  # Escape and quote name and duration for CSV
  local quoted_name="\"${name//\"/\"\"}\""
  local quoted_duration="\"${duration//\"/\"\"}\""

  # CSV logging
  if [[ ! -f "$csv_log" ]]; then
    echo "type,name,start,end,duration,status" > "$csv_log"
  fi
  echo "${type},${quoted_name},${start},${end},${quoted_duration},${status}" >> "$csv_log"

  # JSONL logging
  printf '{"type":"%s","name":"%s","start":%s,"end":%s,"duration":"%s","status":"%s"}\n' \
    "$type" "$name" "$start" "$end" "$duration" "$status" >> "$json_log"
}
export -f vvv_log_timing_event

vvv_cleanup_old_timing_logs() {
  local log_dir="/var/log/provisioners/timing"
  local max_age_days=180

  if [[ ! -d "$log_dir" ]]; then
    return 0
  fi

  vvv_info " - Cleaning up timing logs older than ${max_age_days} days in ${log_dir}"

  find "$log_dir" -type f \( -name "timing-*.csv" -o -name "timing-*.jsonl" \) -mtime +$max_age_days -print -delete
}
export -f vvv_cleanup_old_timing_logs

# @description Cleans up provisioner logs older than 1 year.
vvv_cleanup_old_provision_logs() {
  local base_dir="/var/log/provisioners"
  local cutoff_date
  local folder

  # Compute the cutoff timestamp (1 year ago)
  cutoff_date=$(date -d "1 year ago" +%s)

  # Sanity check
  [[ -d "$base_dir" ]] || return 0

  vvv_info " - Cleaning up provisioner logs older than 1 year in '${base_dir}'"

  shopt -s nullglob
  for folder in "$base_dir"/20??.??.??_*; do
    if [[ -d "$folder" ]]; then
      local basename
      basename=$(basename "$folder")

      # Match pattern like 2022.05.18_12-17-36
      if [[ $basename =~ ^([0-9]{4})\.([0-9]{2})\.([0-9]{2})_ ]]; then
        local year="${BASH_REMATCH[1]}"
        local month="${BASH_REMATCH[2]}"
        local day="${BASH_REMATCH[3]}"

        # Convert to epoch
        local folder_date
        folder_date=$(date -d "${year}-${month}-${day}" +%s 2>/dev/null || echo 0)

        if (( folder_date < cutoff_date )); then
          vvv_warn " - Removing old provisioner log: <b>${folder}</b>"
          rm -rf "$folder"
        fi
      else
        vvv_info " - Skipping folder with unrecognized name format: ${basename}"
      fi
    fi
  done
  shopt -u nullglob
}
export -f vvv_cleanup_old_provision_logs

# @description Check if this Ubuntu is near EOL and warn the user.
vvv_check_ubuntu_eol() {
  # Confirm we are running Ubuntu
  if ! grep -qi '^ID=ubuntu' /etc/os-release 2>/dev/null; then
    vvv_info " - Not running Ubuntu; skipping EOL check."
    return 0
  fi

  local UBUNTU_VERSION
  UBUNTU_VERSION=$(lsb_release -rs 2>/dev/null)
  if [[ -z "$UBUNTU_VERSION" ]]; then
    vvv_error " x Could not determine Ubuntu version."
    return 1
  fi

  local CSV_FILE="/usr/share/distro-info/ubuntu.csv"
  if [[ ! -f "$CSV_FILE" ]]; then
    vvv_error " x EOL data file '$CSV_FILE' not found. Please install 'distro-info' package."
    return 1
  fi

  local EOL_DATE
  EOL_DATE=$(awk -F, -v ver="$UBUNTU_VERSION" '
    {
      # Trim spaces from $1
      gsub(/^ +| +$/, "", $1);
      v = $1;
      # Remove " LTS" suffix for comparison
      sub(/ LTS$/, "", v);
      if (v == ver) print $7
    }
  ' "$CSV_FILE")

  if [[ -z "$EOL_DATE" ]]; then
    vvv_warn " ! Could not find EOL date for Ubuntu version $UBUNTU_VERSION."
    return 1
  fi

  local NOW EOL DIFF
  NOW=$(date +%s)
  EOL=$(date -d "$EOL_DATE" +%s 2>/dev/null)
  if [[ -z "$EOL" ]]; then
    vvv_error " x Failed to parse EOL date '$EOL_DATE'."
    return 1
  fi

  DIFF=$(( (EOL - NOW) / 86400 ))

  if (( DIFF < 0 )); then
    vvv_error " x Ubuntu $UBUNTU_VERSION reached EOL on $EOL_DATE."
  elif (( DIFF <= 90 )); then
    vvv_warn "Ubuntu $UBUNTU_VERSION will reach EOL within $DIFF days (on $EOL_DATE)."
  else
    vvv_success "Ubuntu $UBUNTU_VERSION is supported until $EOL_DATE."
  fi
}
export -f vvv_check_ubuntu_eol
