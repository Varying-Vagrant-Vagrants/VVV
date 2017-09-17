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

if [[ true == $SKIP_PROVISIONING ]]; then
    REPO=false
fi

if [[ false != "${REPO}" ]]; then
  # Clone or pull the site repository
  if [[ ! -d ${VM_DIR}/.git ]]; then
    echo -e "\nDownloading ${SITE}, see ${REPO}"
    git clone --recursive --branch ${BRANCH} ${REPO} ${VM_DIR}
  else
    echo -e "\nUpdating ${SITE}..."
    cd ${VM_DIR}
    git pull origin ${BRANCH}
    git checkout ${BRANCH}
  fi
fi

if [[ false == "${SKIP_PROVISIONING}" ]]; then
  # Look for site setup scripts
  find ${VM_DIR} -maxdepth 4 -name 'vvv-init.sh' -print0 | while read -d $'\0' SITE_CONFIG_FILE; do
    DIR="$(dirname "$SITE_CONFIG_FILE")"
    (
    cd "$DIR"
    source vvv-init.sh
    )
  done

  if [[ -d ${VM_DIR} ]]; then
    # Look for Nginx vhost files, symlink them into the custom sites dir
    for SITE_CONFIG_FILE in $(find ${VM_DIR} -maxdepth 4 -name 'vvv-nginx.conf'); do
      DEST_CONFIG_FILE=${SITE_CONFIG_FILE//\/srv\/www\//}
      DEST_CONFIG_FILE=${DEST_CONFIG_FILE//\//\-}
      DEST_CONFIG_FILE=${DEST_CONFIG_FILE/%-vvv-nginx.conf/}
      DEST_CONFIG_FILE="vvv-auto-$DEST_CONFIG_FILE-$(md5sum <<< "$SITE_CONFIG_FILE" | cut -c1-32).conf"
      VVV_HOSTS=$(get_hosts)
      # We allow the replacement of the {vvv_path_to_folder} token with
      # whatever you want, allowing flexible placement of the site folder
      # while still having an Nginx config which works.
      DIR="$(dirname "$SITE_CONFIG_FILE")"
      sed "s#{vvv_path_to_folder}#$DIR#" "$SITE_CONFIG_FILE" > "/etc/nginx/custom-sites/${DEST_CONFIG_FILE}"
      sed -i "s#{vvv_path_to_site}#$VM_DIR#" "/etc/nginx/custom-sites/${DEST_CONFIG_FILE}"
      sed -i "s#{vvv_site_name}#$SITE#" "/etc/nginx/custom-sites/${DEST_CONFIG_FILE}"
      sed -i "s#{vvv_hosts}#$VVV_HOSTS#" "/etc/nginx/custom-sites/${DEST_CONFIG_FILE}"
      sed -i "s#{upstream}#$NGINX_UPSTREAM#" "/etc/nginx/custom-sites/${DEST_CONFIG_FILE}"

      # Resolve relative paths since not supported in Nginx root.
      while grep -sqE '/[^/][^/]*/\.\.' "/etc/nginx/custom-sites/${DEST_CONFIG_FILE}"; do
        sed -i 's#/[^/][^/]*/\.\.##g' "/etc/nginx/custom-sites/${DEST_CONFIG_FILE}"
      done
    done

    # Parse any vvv-hosts file located in the site repository for domains to
    # be added to the virtual machine's host file so that it is self aware.
    #
    # Domains should be entered on new lines.
    echo "Adding domains to the virtual machine's /etc/hosts file..."
    find ${VM_DIR} -maxdepth 4 -name 'vvv-hosts' | \
    while read hostfile; do
      while IFS='' read -r line || [ -n "$line" ]; do
        if [[ "#" != ${line:0:1} ]]; then
          if [[ -z "$(grep -q "^127.0.0.1 $line$" /etc/hosts)" ]]; then
            echo "127.0.0.1 $line # vvv-auto" >> "/etc/hosts"
            echo " * Added $line from $hostfile"
          fi
        fi
      done < "$hostfile"
    done

    for line in `cat ${VVV_CONFIG} | shyaml get-values sites.${SITE_ESCAPED}.hosts 2> /dev/null`; do
      if [[ -z "$(grep -q "^127.0.0.1 $line$" /etc/hosts)" ]]; then
      echo "127.0.0.1 $line # vvv-auto" >> "/etc/hosts"
      echo " * Added $line from ${VVV_CONFIG}"
    fi
    done
  fi
fi
service nginx restart
