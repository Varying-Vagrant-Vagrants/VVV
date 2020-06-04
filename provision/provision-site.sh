#!/usr/bin/env bash

. "/srv/provision/provisioners.sh"

SITE=$1
SITE_ESCAPED="${SITE//./\\.}"
REPO=$2
BRANCH=$3
VM_DIR=$4
SKIP_PROVISIONING=$5
NGINX_UPSTREAM=$6
VVV_PATH_TO_SITE=${VM_DIR} # used in site templates
VVV_SITE_NAME=${SITE}



SUCCESS=1

# By storing the date now, we can calculate the duration of provisioning at the
# end of this script.

VVV_CONFIG=/vagrant/config.yml

# Takes 2 values, a key to fetch a value for, and an optional default value
# e.g. echo $(get_config_value 'key' 'defaultvalue')
function get_config_value() {
  local value=$(shyaml get-value "sites.${SITE_ESCAPED}.custom.${1}" 2> /dev/null < ${VVV_CONFIG})
  echo "${value:-$2}"
}

function get_hosts() {
  local value=$(shyaml get-values-0 "sites.${SITE_ESCAPED}.hosts" 2> /dev/null < ${VVV_CONFIG} | tr '\0' ' ' | sed 's/ *$//')
  echo "${value:-$@}"
}

function get_primary_host() {
  local value=$(shyaml get-value "sites.${SITE_ESCAPED}.hosts.0" 2> /dev/null < ${VVV_CONFIG})
  echo "${value:-$1}"
}

function vvv_provision_site_nginx_config() {
  SITE_NGINX_FILE=$2
  DEST_NGINX_FILE=${SITE_NGINX_FILE//\/srv\/www\//}
  DEST_NGINX_FILE=${DEST_NGINX_FILE//\//\-}
  DEST_NGINX_FILE=${DEST_NGINX_FILE/%-vvv-nginx.conf/}
  DEST_NGINX_FILE="vvv-auto-${DEST_NGINX_FILE}-$(md5sum <<< "${SITE_NGINX_FILE}" | cut -c1-32).conf"
  VVV_HOSTS=$(get_hosts)
  # We allow the replacement of the {vvv_path_to_folder} token with
  # whatever you want, allowing flexible placement of the site folder
  # while still having an Nginx config which works.
  #env
  DIR="$(dirname "$SITE_NGINX_FILE")"
  sed "s#{vvv_path_to_folder}#${DIR}#" "$SITE_NGINX_FILE" > "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  sed -i "s#{vvv_path_to_site}#${VM_DIR}#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  sed -i "s#{vvv_site_name}#${SITE}#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  sed -i "s#{vvv_hosts}#${VVV_HOSTS}#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"

  if [ 'php' != "${NGINX_UPSTREAM}" ] && [ ! -f "/etc/nginx/upstreams/${NGINX_UPSTREAM}.conf" ]; then
    vvv_warn " * Upstream value '${NGINX_UPSTREAM}' doesn't match a valid upstream. Defaulting to 'php'.${CRESET}"
    NGINX_UPSTREAM='php'
  fi
  sed -i "s#{upstream}#${NGINX_UPSTREAM}#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"

  if /srv/config/homebin/is_utility_installed core tls-ca; then
    sed -i "s#{vvv_tls_cert}#ssl_certificate /srv/certificates/${VVV_SITE_NAME}/dev.crt;#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
    sed -i "s#{vvv_tls_key}#ssl_certificate_key /srv/certificates/${VVV_SITE_NAME}/dev.key;#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  else
    sed -i "s#{vvv_tls_cert}#\# TLS cert not included as the core tls-ca is not installed#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
    sed -i "s#{vvv_tls_key}## TLS key not included as the core tls-ca is not installed#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  fi

  # Resolve relative paths since not supported in Nginx root.
  while grep -sqE '/[^/][^/]*/\.\.' "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"; do
    sed -i 's#/[^/][^/]*/\.\.##g' "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  done
}

function vvv_provision_hosts_file() {
  HOSTFILE=$1
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

function vvv_process_site_hosts() {
  # Parse any vvv-hosts file located in the site repository for domains to
  # be added to the virtual machine's host file so that it is self aware.
  #
  # Domains should be entered on new lines.
  echo " * Adding domains to the virtual machine's /etc/hosts file..."
  hosts=$(shyaml get-values "sites.${SITE_ESCAPED}.hosts" 2> /dev/null < ${VVV_CONFIG})
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
      HOST_FILES=$(find "${VM_DIR}" -maxdepth 4 -name 'vvv-hosts');
      if [[ -z $HOST_FILES ]] ; then
        vvv_error " ! Warning: No vvv-hosts file was found, and no hosts were defined in the vvv config, this site may be inaccessible"
      else
        for HOST_FILE in $HOST_FILES; do
          vvv_provision_hosts_file "$HOST_FILE"
        done
      fi
    fi
  else
    echo " * Adding hosts from the VVV config entry"
    for line in $(shyaml get-values "sites.${SITE_ESCAPED}.hosts" 2> /dev/null < ${VVV_CONFIG}); do
      if [[ -z "$(grep -q "^127.0.0.1 $line$" /etc/hosts)" ]]; then
        echo "127.0.0.1 ${line} # vvv-auto" >> "/etc/hosts"
        echo "   - Added ${line} from ${VVV_CONFIG}"
      fi
    done
  fi
}

function vvv_provision_site_repo() {
  if [[ false != "${REPO}" ]]; then
    if [[ -d "${VM_DIR}" ]] && [[ ! -z "$(ls -A "${VM_DIR}")" ]]; then
      if [[ -d "${VM_DIR}/.git" ]]; then
        echo " * Updating ${SITE} in ${VM_DIR}..."
        cd "${VM_DIR}"
        git reset "origin/${BRANCH}" --hard -q
        git pull origin "${BRANCH}" -q
        git checkout "${BRANCH}" -q
      else
        echo "${RED}Problem! A site folder for ${SITE} was found at ${VM_DIR} that doesn't use a site template, but a site template is defined in the config file. Either the config file is mistaken, or a previous attempt to provision has failed, VVV will not try to git clone the site template to avoid data destruction, either remove the folder, or fix the config/config.yml entry${CRESET}"
      fi
    else
      # Clone or pull the site repository
      echo -e " * Downloading ${SITE}, git cloning from ${REPO} into ${VM_DIR}"
      git clone --recursive --branch "${BRANCH}" "${REPO}" "${VM_DIR}" -q
      if [ $? -eq 0 ]; then
        echo " * ${SITE} Site Template clone successful"
      else
        echo "${RED} ! Git failed to clone the site template for ${SITE}. It tried to clone the ${BRANCH} of ${REPO} into ${VM_DIR}${CRESET}"
        echo "${RED} ! VVV won't be able to provision ${SITE} without the template. Check that you have permission to access the repo, and that the filesystem is writable${CRESET}"
        exit 1
      fi
    fi
  else
    echo " * The site: '${SITE}' does not have a site template, assuming custom provision/vvv-init.sh and provision/vvv-nginx.conf"
    if [[ ! -d "${VM_DIR}" ]]; then
      echo "${RED} ! Error: The '${SITE}' has no folder, VVV does not create the folder for you, or set up the Nginx configs. Use a site template or create the folder and provisioner files, then reprovision VVV${CRESET}"
    fi
  fi
}

function vvv_run_site_template_script() {
  echo " * Found ${1} at ${2}/${1}"
  ( cd "${2}" && source "${1}" )
  if [ $? -eq 0 ]; then
    return 0
  else
    return 1
  fi
}

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
    echo " * Warning: A site provisioner was not found at .vvv/vvv-init.conf provision/vvv-init.conf or vvv-init.conf, searching 3 folders down, please be patient..."
    SITE_INIT_SCRIPTS=$(find "${VM_DIR}" -maxdepth 3 -name 'vvv-init.conf');
    if [[ -z $SITE_INIT_SCRIPTS ]] ; then
      echo " * Warning: No site provisioner was found, VVV could not perform any scripted setup that might install software for this site"
    else
      for SITE_INIT_SCRIPT in $SITE_INIT_SCRIPTS; do
        DIR="$(dirname "$SITE_INIT_SCRIPT")"
        vvv_run_site_template_script "vvv-init.sh" "${DIR}"
      done
    fi
  fi
}

function vvv_provision_site_nginx() {
  # Look for Nginx vhost files, symlink them into the custom sites dir
  if [[ -f "${VM_DIR}/.vvv/vvv-nginx.conf" ]]; then
    vvv_provision_site_nginx_config "${SITE}" "${VM_DIR}/.vvv/vvv-nginx.conf"
  elif [[ -f "${VM_DIR}/provision/vvv-nginx.conf" ]]; then
    vvv_provision_site_nginx_config "${SITE}" "${VM_DIR}/provision/vvv-nginx.conf"
  elif [[ -f "${VM_DIR}/vvv-nginx.conf" ]]; then
    vvv_provision_site_nginx_config "${SITE}" "${VM_DIR}/vvv-nginx.conf"
  else
    echo " * Warning: An nginx config was not found at .vvv/vvv-nginx.conf provision/vvv-nginx.conf or vvv-nginx.conf, searching 3 folders down, please be patient..."
    NGINX_CONFIGS=$(find "${VM_DIR}" -maxdepth 3 -name 'vvv-nginx.conf');
    if [[ -z $NGINX_CONFIGS ]] ; then
      vvv_error " ! Warning: No nginx config was found, VVV will not know how to serve this site"
    else
      for SITE_CONFIG_FILE in $NGINX_CONFIGS; do
        vvv_provision_site_nginx_config "${SITE}" "${SITE_CONFIG_FILE}"
      done
    fi
  fi
}

# -------------------------------

if [[ true == "${SKIP_PROVISIONING}" ]]; then
  vvv_warn " * Skipping provisioning of ${SITE}${CRESET}"
  return 0
fi

vvv_provision_site_repo

if [[ ! -d "${VM_DIR}" ]]; then
  echo "${RED} ! Error: The ${VM_DIR} folder does not exist, there is nothing to provision for the '${SITE}' site! ${CRESET}"
  return 1
fi

vvv_process_site_hosts
vvv_provision_site_script
vvv_provision_site_nginx

echo " * Reloading Nginx"
service nginx reload

if [ "${SUCCESS}" -ne "0" ]; then
  vvv_error " ! ${SITE} provisioning had some issues, check the log as the site may not function correctly."
  return 1
fi

provisioner_success
