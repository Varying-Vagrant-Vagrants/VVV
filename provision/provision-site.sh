#!/usr/bin/env bash

SITE=$1
SITE_ESCAPED=`echo ${SITE} | sed 's/\./\\\\./g'`
REPO=$2
BRANCH=$3
VM_DIR=$4
SKIP_PROVISIONING=$5
NGINX_UPSTREAM=$6
VVV_PATH_TO_SITE=${VM_DIR}
VVV_SITE_NAME=${SITE}

VVV_CONFIG=/vagrant/vvv-config.yml
if [[ -f /vagrant/vvv-custom.yml ]]; then
	VVV_CONFIG=/vagrant/vvv-custom.yml
fi

noroot() {
  sudo -EH -u "vagrant" "$@";
}

# Takes 2 values, a key to fetch a value for, and an optional default value
# e.g. echo `get_config_value 'key' 'defaultvalue'`
get_config_value() {
  local value=`cat ${VVV_CONFIG} | shyaml get-value sites.${SITE_ESCAPED}.custom.${1} 2> /dev/null`
  echo ${value:-$2}
}

get_hosts() {
  local value=`cat ${VVV_CONFIG} | shyaml get-values sites.${SITE_ESCAPED}.hosts 2> /dev/null`
  echo ${value:-$@}
}

get_primary_host() {
  local value=`cat ${VVV_CONFIG} | shyaml get-value sites.${SITE_ESCAPED}.hosts.0 2> /dev/null`
  echo ${value:-$1}
}

is_utility_installed() {
  local utilities=`cat ${VVV_CONFIG} | shyaml get-values utilities.${1} 2> /dev/null`
  for utility in ${utilities}; do
    if [[ "${utility}" == "${2}" ]]; then
      return 0
    fi
  done
  return 1
}

function vvv_provision_site_nginx() {
  SITE_NAME=$1
  SITE_NGINX_FILE=$2
  DEST_NGINX_FILE=${SITE_NGINX_FILE//\/srv\/www\//}
  DEST_NGINX_FILE=${DEST_NGINX_FILE//\//\-}
  DEST_NGINX_FILE=${DEST_NGINX_FILE/%-vvv-nginx.conf/}
  DEST_NGINX_FILE="vvv-auto-${DEST_NGINX_FILE}-$(md5sum <<< "$SITE_NGINX_FILE" | cut -c1-32).conf"
  VVV_HOSTS=$(get_hosts)
  # We allow the replacement of the {vvv_path_to_folder} token with
  # whatever you want, allowing flexible placement of the site folder
  # while still having an Nginx config which works.
  #env
  DIR="$(dirname "$SITE_NGINX_FILE")"
  sed "s#{vvv_path_to_folder}#$DIR#" "$SITE_NGINX_FILE" > "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  sed -i "s#{vvv_path_to_site}#$VM_DIR#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  sed -i "s#{vvv_site_name}#$SITE#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  sed -i "s#{vvv_hosts}#$VVV_HOSTS#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  sed -i "s#{upstream}#$NGINX_UPSTREAM#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  
  if [ -n "$(type -t is_utility_installed)" ] && [ "$(type -t is_utility_installed)" = function ] && `is_utility_installed core tls-ca`; then
    sed -i "s#{vvv_tls_cert}#ssl_certificate /vagrant/certificates/${VVV_SITE_NAME}/dev.crt;#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
    sed -i "s#{vvv_tls_key}#ssl_certificate_key /vagrant/certificates/${VVV_SITE_NAME}/dev.key;#" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
  else
    sed -i "s#{vvv_tls_cert}##" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
    sed -i "s#{vvv_tls_key}##" "/etc/nginx/custom-sites/${DEST_NGINX_FILE}"
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
        if [[ -z "$(grep -q "^127.0.0.1 $line$" /etc/hosts)" ]]; then
          echo "127.0.0.1 $line # vvv-auto" >> "/etc/hosts"
          echo " * Added $line from $HOSTFILE"
        fi
      fi
    done < "$HOSTFILE"
  done
}

if [[ true == $SKIP_PROVISIONING ]]; then
  echo "Skipping provisioning of ${SITE}"
  return
fi

if [[ false != "${REPO}" ]]; then
  # Clone or pull the site repository
  if [[ ! -d ${VM_DIR}/.git ]]; then
    echo -e "\nDownloading ${SITE}, see ${REPO}"
    noroot git clone --recursive --branch ${BRANCH} ${REPO} ${VM_DIR} -q
  else
    echo -e "\nUpdating ${SITE}..."
    cd ${VM_DIR}
    noroot git reset origin/${BRANCH} --hard -q
    noroot git pull origin ${BRANCH} -q
    noroot git checkout ${BRANCH} -q
  fi
else
  echo "The site: '${SITE}' does not have a site template, assuming custom provision/vvv-init.sh and provision/vvv-nginx.conf"
  if [[ ! -d ${VM_DIR} ]]; then
    echo "Error: The '${SITE}' has no folder, VVV does not create the folder for you, or set up the Nginx configs. Use a site template or create the folder and provisioner files, then reprovision VVV"
  fi
fi

if [[ -d ${VM_DIR} ]]; then
  # Look for site setup scripts
  if [[ -f "${VM_DIR}/.vvv/vvv-init.sh" ]]; then
    ( cd "${VM_DIR}/.vvv" && source vvv-init.sh )
  elif [[ -f "${VM_DIR}/provision/vvv-init.sh" ]]; then
    ( cd "${VM_DIR}/provision" && source vvv-init.sh )
  elif [[ -f "${VM_DIR}/vvv-init.sh" ]]; then
    ( cd "${VM_DIR}" && source vvv-init.sh )
  else
    echo "Warning: A site provisioner was not found at .vvv/vvv-init.conf provision/vvv-init.conf or vvv-init.conf, searching 3 folders down, please be patient..."
    SITE_INIT_SCRIPTS=$(find ${VM_DIR} -maxdepth 3 -name 'vvv-init.conf');
    if [[ -z $SITE_INIT_SCRIPTS ]] ; then
      echo "Warning: No site provisioner was found, VVV could not perform any scripted setup that might install software for this site"
    else
      for SITE_INIT_SCRIPT in $SITE_INIT_SCRIPTS; do
        DIR="$(dirname "$SITE_INIT_SCRIPT")"
        ( cd "${DIR}" &&  source vvv-init.sh )
      done
    fi
  fi

  # Look for Nginx vhost files, symlink them into the custom sites dir
  if [[ -f "${VM_DIR}/.vvv/vvv-nginx.conf" ]]; then
    vvv_provision_site_nginx $SITE "${VM_DIR}/.vvv/vvv-nginx.conf"
  elif [[ -f "${VM_DIR}/provision/vvv-nginx.conf" ]]; then
    vvv_provision_site_nginx $SITE "${VM_DIR}/provision/vvv-nginx.conf"
  elif [[ -f "${VM_DIR}/vvv-nginx.conf" ]]; then
    vvv_provision_site_nginx $SITE "${VM_DIR}/vvv-nginx.conf"
  else
    echo "Warning: An nginx config was not found at .vvv/vvv-nginx.conf provision/vvv-nginx.conf or vvv-nginx.conf, searching 3 folders down, please be patient..."
    NGINX_CONFIGS=$(find ${VM_DIR} -maxdepth 3 -name 'vvv-nginx.conf');
    if [[ -z $NGINX_CONFIGS ]] ; then
      echo "Warning: No nginx config was found, VVV will not know how to serve this site"
    else
      for SITE_CONFIG_FILE in $NGINX_CONFIGS; do
        vvv_provision_site_nginx $SITE $SITE_CONFIG_FILE
      done
    fi
  fi

  # Parse any vvv-hosts file located in the site repository for domains to
  # be added to the virtual machine's host file so that it is self aware.
  #
  # Domains should be entered on new lines.
  echo "Adding domains to the virtual machine's /etc/hosts file..."
  hosts=`cat ${VVV_CONFIG} | shyaml get-values sites.${SITE_ESCAPED}.hosts 2> /dev/null`
  if [ ${#hosts[@]} -eq 0 ]; then
    echo "No hosts were found in the VVV config, falling back to vvv-hosts"
    if [[ -f "${VM_DIR}/.vvv/vvv-hosts" ]]; then
      echo "Found a .vvv/vvv-hosts file"
      vvv_provision_hosts_file $SITE "${VM_DIR}/.vvv/vvv-hosts"
    elif [[ -f "${VM_DIR}/provision/vvv-hosts" ]]; then
      echo "Found a provision/vvv-hosts file"
      vvv_provision_hosts_file $SITE "${VM_DIR}/provision/vvv-hosts"
    elif [[ -f "${VM_DIR}/vvv-hosts" ]]; then
      echo "Found a vvv-hosts file"
      vvv_provision_hosts_file $SITE "${VM_DIR}/vvv-hosts"
    else
      echo "Searching subfolders 4 levels down for a vvv-hosts file ( this can be skipped by using ./vvv-hosts, .vvv/vvv-hosts, or provision/vvv-hosts"
      HOST_FILES=$(find ${VM_DIR} -maxdepth 4 -name 'vvv-hosts');
      if [[ -z $HOST_FILES ]] ; then
        echo "Warning: No vvv-hosts file was found, and no hosts were defined in the vvv config, this site may be inaccessible"
      else
        for HOST_FILE in $HOST_FILES; do
          vvv_provision_hosts_file $HOST_FILE
        done
      fi
    fi
  else
    echo "Adding hosts from the VVV config entry"
    for line in `cat ${VVV_CONFIG} | shyaml get-values sites.${SITE_ESCAPED}.hosts 2> /dev/null`; do
      if [[ -z "$(grep -q "^127.0.0.1 $line$" /etc/hosts)" ]]; then
        echo "127.0.0.1 $line # vvv-auto" >> "/etc/hosts"
        echo " * Added $line from ${VVV_CONFIG}"
      fi
    done
  fi

  service nginx restart
else
  echo "Error: The ${VM_DIR} folder does not exist, and no repo was specified in the config file, there is nothing to provision for this site!"
fi


