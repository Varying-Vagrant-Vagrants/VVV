#!/usr/bin/env bash
# @file Core Site Provisioner
# @brief The main site provisioner script.
# @description A script executed as a provisioner that provisions a site. This includes cloning its provisioner template, installing and processing Nginx config files, running setup scripts, etc
# This script takes several arguments:
#
# - The name of the site in the config file
# - The git repository URI for the site provisioner template
# - The git branch to use
# - The location inside the guest to set up the site
# - Wether to skip provisioning for this site
# - The Nginx upstream to use

set -eo pipefail

SITE=$1
SITE_ESCAPED="${SITE//./\\.}"
REPO=$2
BRANCH=$3
VM_DIR=$4
SKIP_PROVISIONING=$5
NGINX_UPSTREAM=$6
VVV_PATH_TO_SITE=${VM_DIR} # used in site templates
VVV_SITE_NAME=${SITE}
VVV_HOSTS=""

SUCCESS=1

VVV_CONFIG=/vagrant/config.yml

. "/srv/provision/provisioners.sh"

# @description Takes 2 values, a key to fetch a value for, and an optional default value
#
# @example
#    echo $(get_config_value 'key' 'defaultvalue')
#
# @arg $1 string the name of the custom parameter
# @arg $2 string the default value
#
# @see vvv_get_site_config_value
function get_config_value() {
  vvv_get_site_config_value "custom.${1}" "${2}"
}

# @description Retrieves a list of hosts for this site from the config file. Internally this relies on `shyaml get-values-0`
#
# @noargs
# @see get_hosts_list
# @stdout a space separated string of domains, defaulting to `sitename.test` if none are specified
function get_hosts() {
  local value=$(shyaml -q get-values-0 "sites.${SITE_ESCAPED}.hosts" < ${VVV_CONFIG} | tr '\0' ' ' | sed 's/ *$//')
  echo "${value:-"${VVV_SITE_NAME}.test"}"
}

# @description Retrieves a list of hosts for this site from the config file. Internally this relies on `shyaml get-values`
#
# @noargs
# @see get_hosts
# @stdout a space separated string of domains, defaulting to `sitename.test` if none are specified
function get_hosts_list() {
  local value=$(shyaml -q get-values "sites.${SITE_ESCAPED}.hosts" < ${VVV_CONFIG})
  echo "${value:-"${VVV_SITE_NAME}.test"}"
}

# @description Retrieves the first host listed for a site.
#
# @noargs
# @see get_hosts
# @see get_hosts_list
# @stdout the first host listed in the config file for this site, defaulting to `sitename.test` if none are specified
function get_primary_host() {
  local value=$(shyaml -q get-value "sites.${SITE_ESCAPED}.hosts.0" "${1}" < ${VVV_CONFIG})
  echo "${value:-"${VVV_SITE_NAME}.test"}"
}

# @description processes and installs an Nginx config for a site.
# The function performs variable substitutions, and installs the resulting config.
# This includes inserting SSL key references, host names, and paths.
#
# @arg $1 string the name of the site
# @arg $2 string the Nginx config file to process and install
# @internal
function vvv_provision_site_nginx_config() {
  local SITE_NAME=$1
  local SITE_NGINX_FILE=$2
  VVV_HOSTS=$(get_hosts)
  local TMPFILE=$(mktemp /tmp/vvv-site-XXXXX)
  cat "${SITE_NGINX_FILE}" >> "${TMPFILE}"

  vvv_info " * VVV is adding an Nginx config from ${SITE_NGINX_FILE}"

  # We allow the replacement of the {vvv_path_to_folder} token with
  # whatever you want, allowing flexible placement of the site folder
  # while still having an Nginx config which works.
  local DIR="$(dirname "${SITE_NGINX_FILE}")"
  sed "s#{vvv_path_to_folder}#${DIR}#" "${SITE_NGINX_FILE}" >  "${TMPFILE}"
  sed -i "s#{vvv_path_to_site}#${VM_DIR}#"  "${TMPFILE}"
  sed -i "s#{vvv_site_name}#${SITE_NAME}#"  "${TMPFILE}"
  sed -i "s#{vvv_hosts}#${VVV_HOSTS}#"  "${TMPFILE}"

  if [ 'php' != "${NGINX_UPSTREAM}" ] && [ ! -f "/etc/nginx/upstreams/${NGINX_UPSTREAM}.conf" ]; then
    vvv_error " * Upstream value '${NGINX_UPSTREAM}' doesn't match a valid upstream. Defaulting to 'php'.${CRESET}"
    NGINX_UPSTREAM='php'
  fi
  sed -i "s#{upstream}#${NGINX_UPSTREAM}#"  "${TMPFILE}"

  if [ -f "/srv/certificates/${SITE_NAME}/dev.crt" ]; then
    sed -i "s#{vvv_tls_cert}#ssl_certificate \"/srv/certificates/${SITE_NAME}/dev.crt\";#"  "${TMPFILE}"
    sed -i "s#{vvv_tls_key}#ssl_certificate_key \"/srv/certificates/${SITE_NAME}/dev.key\";#" "${TMPFILE}"
  else
    sed -i "s#{vvv_tls_cert}#\# TLS cert not included as the certificate file is not present#"  "${TMPFILE}"
    sed -i "s#{vvv_tls_key}#\# TLS key not included as the certificate file is not present#"  "${TMPFILE}"
  fi

  # Resolve relative paths since not supported in Nginx root.
  while grep -sqE '/[^/][^/]*/\.\.'  "${TMPFILE}"; do
    sed -i 's#/[^/][^/]*/\.\.##g'  "${TMPFILE}"
  done

  # "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  local DEST_NGINX_FILE=${SITE_NGINX_FILE//\/srv\/www\//}
  local DEST_NGINX_FILE=${DEST_NGINX_FILE//\//\-}
  local DEST_NGINX_FILE=${DEST_NGINX_FILE//-provision/} # remove the provision folder name
  local DEST_NGINX_FILE=${DEST_NGINX_FILE//-.vvv/} # remove the .vvv folder name
  local DEST_NGINX_FILE=${DEST_NGINX_FILE/%-vvv-nginx.conf/}
  local DEST_NGINX_FILE="vvv-${DEST_NGINX_FILE}-$(md5sum <<< "${SITE_NGINX_FILE}" | cut -c1-8).conf"

  if ! vvv_maybe_install_nginx_config "${TMPFILE}" "${DEST_NGINX_FILE}" "sites"; then
    vvv_warn " ! This sites nginx config had problems, it may not load. Look at the above errors to diagnose the problem"
    vvv_info " ! VVV will now continue with provisioning so that other sites have an opportunity to run"
  fi
  rm -f "${TMPFILE}"
}

# @description add hosts from a file to VVVs hosts file (the guest, not the host machine)
#
# @arg $1 string a vvv-hosts file to process
# @internal
function vvv_provision_hosts_file() {
  local HOSTFILE=$1
  while read HOSTFILE; do
    while IFS='' read -r line || [ -n "$line" ]; do
      if [[ "#" != ${line:0:1} ]]; then
        if [[ -z "$(grep -q "^127.0.0.1 ${line}$" /etc/hosts)" ]]; then
          echo "127.0.0.1 $line # vvv-auto" >> "/etc/hosts"
          echo "   - Added ${line} from ${HOSTFILE}"
        fi
      fi
    done < "$HOSTFILE"
  done
}

# @description Parse any `vvv-hosts` files located in the site repository for domains to
# be added to the virtual machine's host file so that it is self aware.
#
# @internal
# @noargs
function vvv_process_site_hosts() {
  echo " * Adding domains to the virtual machine's /etc/hosts file..."
  local hosts=$(get_hosts_list)
  if [ ${#hosts[@]} -eq 0 ]; then
    echo " * No hosts were found in the VVV config, falling back to vvv-hosts"
    if [[ -f "${VM_DIR}/.vvv/vvv-hosts" ]]; then
      vvv_success " * Found a .vvv/vvv-hosts file"
      vvv_provision_hosts_file "${SITE}" "${VM_DIR}/.vvv/vvv-hosts"
    elif [[ -f "${VM_DIR}/provision/vvv-hosts" ]]; then
      vvv_success " * Found a provision/vvv-hosts file"
      vvv_provision_hosts_file "${SITE}" "${VM_DIR}/provision/vvv-hosts"
    elif [[ -f "${VM_DIR}/vvv-hosts" ]]; then
      vvv_success " * Found a vvv-hosts file"
      vvv_provision_hosts_file "${SITE}" "${VM_DIR}/vvv-hosts"
    else
      echo " * Searching subfolders 4 levels down for a vvv-hosts file ( this can be skipped by using ./vvv-hosts, .vvv/vvv-hosts, or provision/vvv-hosts"
      local HOST_FILES=$(find "${VM_DIR}" -maxdepth 4 -name 'vvv-hosts');
      if [[ -z $HOST_FILES ]] ; then
        vvv_error " ! Warning: No vvv-hosts file was found, and no hosts were defined in the vvv config, this site may be inaccessible"
      else
        for HOST_FILE in $HOST_FILES; do
          vvv_provision_hosts_file "$HOST_FILE"
        done
      fi
    fi
  else
    echo " * Adding hosts for the site to the VM hosts file"
    for line in $hosts; do
      if [[ -z "$(grep -q "^127.0.0.1 $line$" /etc/hosts)" ]]; then
        echo "127.0.0.1 ${line} # vvv-auto" >> "/etc/hosts"
        echo "   - Added ${line} from ${VVV_CONFIG}"
      fi
    done
  fi
}

# @description Clones a site provisioner repository or git repo as specified in the repo: field of a site
#
# @internal
# @noargs
function vvv_provision_site_repo() {
  if [[ false != "${REPO}" ]]; then
    vvv_info " * Pulling down the ${BRANCH} branch of ${REPO}"
    if [[ -d "${VM_DIR}" ]] && [[ ! -z "$(ls -A "${VM_DIR}")" ]]; then
      if [[ -d "${VM_DIR}/.git" ]]; then
        echo " * Updating ${SITE} provisioner repo in ${VM_DIR} (${REPO}, ${BRANCH})"
        echo " * Any local changes not present on the server will be discarded in favor of the remote branch"
        cd "${VM_DIR}"
        echo " * Checking that remote origin is ${REPO}"
        CURRENTORIGIN=$(noroot git remote get-url origin)
        if [[ "${CURRENTORIGIN}" != "${REPO}" ]]; then
          vvv_error " ! The site config said to use <b>${REPO}</b>"
          vvv_error " ! But the origin remote is actually <b>${CURRENTORIGIN}</b>"
          vvv_error " ! Remove the unknown origin remote and re-add it."
          vvv_error ""
          vvv_error " ! You can do this by running these commands inside the VM:"
          vvv_error " "
          vvv_error " cd ${VM_DIR}"
          vvv_error " git remote remove origin"
          vvv_error " git remote add origin ${REPO}"
          vvv_error " exit"
          vvv_error " "
          vvv_error " ! You can get inside the VM using <b>vagrant ssh</b>"
          vvv_error " "
          SUCCESS=1
          return 1
        fi
        echo " * Fetching origin ${BRANCH}"
        noroot git fetch origin "${BRANCH}"
        echo " * performing a hard reset on origin/${BRANCH}"
        noroot git reset "origin/${BRANCH}" --hard
        echo " * Updating provisioner repo complete"
      else
        vvv_error " ! Problem! A site folder for ${SITE} was found at ${VM_DIR} that doesn't use a site template, but a site template is defined in the config file. Either the config file is mistaken, or a previous attempt to provision has failed, VVV will not try to git clone the site template to avoid data destruction, either remove the folder, or fix the config/config.yml entry${CRESET}"
      fi
    else
      # Clone or pull the site repository
      vvv_info " * Downloading ${SITE} provisioner, git cloning from ${REPO} into ${VM_DIR}"
      if noroot git clone --recursive --branch "${BRANCH}" "${REPO}" "${VM_DIR}"; then
        vvv_success " * ${SITE} provisioner clone successful"
      else
        vvv_error " ! Git failed to clone the site template for ${SITE}. It tried to clone the ${BRANCH} of ${REPO} into ${VM_DIR}${CRESET}"
        vvv_error " ! VVV won't be able to provision ${SITE} without the template. Check that you have permission to access the repo, and that the filesystem is writable${CRESET}"
        exit 1
      fi
    fi
  else
    vvv_info " * The site: '${SITE}' does not have a site template, assuming custom provision/vvv-init.sh and provision/vvv-nginx.conf"
    if [[ ! -d "${VM_DIR}" ]]; then
      vvv_error " ! Error: The '${SITE}' has no folder, VVV does not create the folder for you, or set up the Nginx configs. Use a site template or create the folder and provisioner files, then reprovision VVV"
      exit 1
    fi
  fi
}

# @description Runs a site provisioner script to install and configure a site automatically.
#
# @arg $1 string the provisioner to run
# @arg $2 string the folder containing the provisioner to runn
# @internal
function vvv_run_site_template_script() {
  echo " * Found ${1} at ${2}/${1}"
  cd "${2}"
  if source "${1}"; then
    vvv_info " * sourcing of ${1} reported success"
    return 0
  else
    vvv_error " ! sourcing of ${1} reported failure with an error code of ${?}"
    return 1
  fi
}

# @description Searches for and executes a site provisioner setup script
#
# @internal
# @noargs
function vvv_provision_site_script() {
  # Look for site setup scripts
  echo " * Searching for a site template provisioner, vvv-init.sh"
  if [[ -f "${VM_DIR}/.vvv/vvv-init.sh" ]]; then
    vvv_run_site_template_script "vvv-init.sh" "${VM_DIR}/.vvv"
    SUCCESS=$?
  elif [[ -f "${VM_DIR}/provision/vvv-init.sh" ]]; then
    vvv_run_site_template_script "vvv-init.sh" "${VM_DIR}/provision"
    SUCCESS=$?
  elif [[ -f "${VM_DIR}/vvv-init.sh" ]]; then
    vvv_run_site_template_script "vvv-init.sh" "${VM_DIR}"
    SUCCESS=$?
  else
    vvv_warn " * Warning: A site provisioner was not found at .vvv/vvv-init.sh provision/vvv-init.sh or vvv-init.sh, searching 3 folders down, please be patient..."
    local SITE_INIT_SCRIPTS=$(find "${VM_DIR}" -maxdepth 3 -name 'vvv-init.sh');
    if [[ -z $SITE_INIT_SCRIPTS ]] ; then
      vvv_warn " * Warning: No site provisioner was found, VVV could not perform any scripted setup that might install software for this site"
    else
      for SITE_INIT_SCRIPT in $SITE_INIT_SCRIPTS; do
        local DIR="$(dirname "$SITE_INIT_SCRIPT")"
        vvv_run_site_template_script "vvv-init.sh" "${DIR}"
      done
    fi
  fi
}

# @description Searches for and installs a site Nginx configuration.
#
# @internal
# @noargs
function vvv_provision_site_nginx() {
  # Look for Nginx vhost files, symlink them into the custom sites dir
  if [[ -f "${VM_DIR}/.vvv/vvv-nginx.conf" ]]; then
    vvv_provision_site_nginx_config "${SITE}" "${VM_DIR}/.vvv/vvv-nginx.conf"
  elif [[ -f "${VM_DIR}/provision/vvv-nginx.conf" ]]; then
    vvv_provision_site_nginx_config "${SITE}" "${VM_DIR}/provision/vvv-nginx.conf"
  elif [[ -f "${VM_DIR}/vvv-nginx.conf" ]]; then
    vvv_provision_site_nginx_config "${SITE}" "${VM_DIR}/vvv-nginx.conf"
  else
    vvv_warn " ! Warning: An nginx config was not found!! VVV needs an Nginx config for the site or it will not know how to serve it."
    vvv_warn " * VVV searched for an Nginx config in these locations:"
    vvv_warn "   - ${VM_DIR}/.vvv/vvv-nginx.conf"
    vvv_warn "   - ${VM_DIR}/provision/vvv-nginx.conf"
    vvv_warn "   - ${VM_DIR}/vvv-nginx.conf"
    vvv_warn " * VVV will search 3 folders down to find an Nginx config, please be patient..."
    local NGINX_CONFIGS=$(find "${VM_DIR}" -maxdepth 3 -name 'vvv-nginx.conf');
    if [[ -z $NGINX_CONFIGS ]] ; then
      vvv_error " ! Error: No nginx config was found, VVV will not know how to serve this site"
      exit 1
    else
      vvv_warn " * VVV found Nginx config files in subfolders, move these files to the expected locations to avoid these warnings."
      for SITE_CONFIG_FILE in $NGINX_CONFIGS; do
        vvv_info
        vvv_provision_site_nginx_config "${SITE}" "${SITE_CONFIG_FILE}"
      done
    fi
  fi
}

# @description Retrieves a config value for the given site as specified in `config.yml`
#
# @arg $1 string the config value to fetch
# @arg $2 string the default value
function vvv_get_site_config_value() {
  local value=$(shyaml -q get-value "sites.${SITE_ESCAPED}.${1}" "${2}" < ${VVV_CONFIG})
  echo "${value}"
}

# @description Clones a git repository into a sites sub-folder
#
# @arg $1 string the git repository URL to clone
# @arg $2 string the folder to clone into
# @internal
# @see vvv_custom_folder_git
function vvv_clone_site_git_folder() {
  local repo="${1}"
  local folder="${2}"
  vvv_info " * git cloning <b>'${repo}'</b><info> into </info><b>'${VVV_PATH_TO_SITE}/${folder}'</b>"
  noroot mkdir -p "${VVV_PATH_TO_SITE}/${folder}"
  noroot git clone  --recurse-submodules -j2 "${repo}" "${VVV_PATH_TO_SITE}/${folder}"
}

# @description Processes a folder sections composer option for a site as specified in `config.yml`
#
# @arg $1 string the folder name to process specified in `config.yml`
function vvv_custom_folder_composer() {
  local folder="${1}"
  if keys=$(shyaml keys -y -q "sites.${SITE_ESCAPED}.folders.${folder}.composer" < "${VVV_CONFIG}"); then
      for key in $keys; do
        cd "${folder}"
        local value=$(vvv_get_site_config_value "folders.${folder}.composer.${key}" "")
        if [[ "install" == "${key}" ]]; then
          if [[ "True" == "${value}" ]]; then
            vvv_info " * Running composer install in ${folder}"
            noroot composer install
          fi
        elif [[ "update" == "${key}" ]]; then
          if [[ "True" == "${value}" ]]; then
            vvv_info " * Running composer update in ${folder}"
            noroot composer update
          fi
        elif [[ "create-project" == "${key}" ]]; then
          vvv_info " * Running composer create-project ${value} in ${folder}"
          noroot composer create-project "${value}" .
        else
          vvv_warn " * Unknown key in Composer section: <b>${key}</b><warn> for </warn><b>${folder}</b>"
        fi
        cd -
      done
  fi
}


# @description Processes a folder sections npm option for a site as specified in `config.yml`
#
# @arg $1 string the folder name to process specified in `config.yml`
function vvv_custom_folder_npm() {
  local folder="${1}"
  if keys=$(shyaml keys -y -q "sites.${SITE_ESCAPED}.folders.${folder}.npm" < "${VVV_CONFIG}"); then
      for key in $keys; do
        cd "${folder}"
        local value=$(vvv_get_site_config_value "folders.${folder}.npm.${key}" "")
        if [[ "install" == "${key}" ]]; then
          if [[ "True" == "${value}" ]]; then
            vvv_info " * Running npm install in ${folder}"
            noroot npm install
          fi
        elif [[ "update" == "${key}" ]]; then
          if [[ "True" == "${value}" ]]; then
            vvv_info " * Running npm update in ${folder}"
            noroot npm update
          fi
        elif [[ "run" == "${key}" ]]; then
          vvv_info " * Running npm run ${value} in ${folder}"
          noroot npm run "${value}"
        else
          vvv_warn " * Unknown key in NPM section: <b>${key}</b><warn> for </warn><b>${folder}</b>"
        fi
        cd -
      done
  fi
}

# @description Processes a folder sections git option for a site as specified in `config.yml`
#
# @arg $1 string the folder name to process specified in `config.yml`
# @internal
# @see vvv_clone_site_git_folder
function vvv_custom_folder_git() {
  local folder="${1}"
  local repo=$(vvv_get_site_config_value "folders.${folder}.git.repo" "?")
  local overwrite_on_clone=$(vvv_get_site_config_value "folders.${folder}.git.overwrite_on_clone" "False")
  local hard_reset=$(vvv_get_site_config_value "folders.${folder}.git.hard_reset" "False")
  local pull=$(vvv_get_site_config_value "folders.${folder}.git.pull" "False")

  if [ ! -d "${VVV_PATH_TO_SITE}/${folder}" ]; then
    vvv_clone_site_git_folder "${repo}" "${folder}"
  else
    if [[ $overwrite_on_clone == "True" ]]; then
      if [ ! -d "${VVV_PATH_TO_SITE}/${folder}/.git" ]; then
        vvv_info " - VVV was asked to clone into a folder that already exists (${folder}), but does not contain a git repo"
        vvv_info " - overwrite_on_clone is turned on so VVV will purge with extreme predjudice and clone over the folders grave"
        rm -rf "${VVV_PATH_TO_SITE}/${folder}"
        vvv_clone_site_git_folder "${repo}" "${folder}"
      fi
    else
      vvv_warn " - Cannot clone into <b>'${folder}'</b><warn>, a folder that is not a git repo already exists. Set overwrite: true to force the folders deletion and a clone will take place"
    fi
  fi

  if [[ $hard_reset == "True" ]]; then
    vvv_info " - resetting git checkout and discarding changes in ${folder}"
    cd "${VVV_PATH_TO_SITE}/${folder}"
    noroot git reset --hard -q
    noroot git checkout -q
    cd -
  fi
  if [[ $pull == "True" ]]; then
    vvv_info " - runnning git pull in ${folder}"
    cd "${VVV_PATH_TO_SITE}/${folder}"
    noroot git pull -q
    cd -
  fi
}

# @description Processes the folders option from the sites `config.yml`
#
# @internal
# @noargs
function vvv_custom_folders() {
  if folders=$(shyaml keys -y -q "sites.${SITE_ESCAPED}.folders" < "${VVV_CONFIG}"); then
    for folder in $folders; do
      if [[ $folder != '...' ]]; then
        if keys=$(shyaml keys -y -q "sites.${SITE_ESCAPED}.folders.${folder}" < "${VVV_CONFIG}"); then
          for key in $keys; do
            if [[ "${key}" == "git" ]]; then
              vvv_custom_folder_git "${folder}"
            elif [[ "${key}" == "composer" ]]; then
              vvv_custom_folder_composer "${folder}"
            elif [[ "${key}" == "npm" ]]; then
              vvv_custom_folder_npm "${folder}"
            else
              vvv_warn " * Unknown folders sub-parameter <b>${key}<b><warn> ignoring"
            fi
          done
        fi
      fi
    done
  fi
}

# -------------------------------

if [[ true == "${SKIP_PROVISIONING}" ]]; then
  vvv_warn " * Skipping provisioning of <b>${SITE}</b>"
  exit 0
fi

# Ensure npm is available
if ! command -v nvm &> /dev/null; then
  if [ -f /home/vagrant/.nvm/nvm.sh ]; then
    source /home/vagrant/.nvm/nvm.sh
  fi
fi
nvm use default

vvv_provision_site_repo

if [[ ! -d "${VM_DIR}" ]]; then
  vvv_error " "
  vvv_error " "
  vvv_error " ! Error: The <b>${VM_DIR}</b><error> folder does not exist, there is nothing to provision for the <b>'${SITE}'</b><error> site!</error>"
  vvv_error " ! It is not enough to declare a site, if you do not specify a provisioner repo/site template then you have to create the folder and fill it yourself."
  vvv_error " ! At a very minimum, VVV needs an Nginx config so it knows how to serve the website"
  vvv_error " "
  vvv_error " "
  exit 1
fi

vvv_process_site_hosts
vvv_provision_site_script
vvv_custom_folders
vvv_provision_site_nginx

if [ "${SUCCESS}" -ne "0" ]; then
  vvv_error " ! ${SITE} provisioning had some issues, check the log files as the site may not function correctly."
  exit 1
fi

provisioner_success
