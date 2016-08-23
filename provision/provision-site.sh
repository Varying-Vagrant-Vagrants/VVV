#!/usr/bin/env bash

noroot() {
  sudo -EH -u "vagrant" "$@";
}

# Clone or pull the site repository
if [[ ! -d $4 ]]; then
  echo -e "\nDownloading ${1}, see ${2}"
  git clone $2 $4
  cd $4
  git checkout $3
else
  echo -e "\nUpdating ${1}..."
  cd $4
  git pull origin $3
  git checkout $3
fi

# Look for site setup scripts
find $4 -maxdepth 4 -name 'vvv-init.sh' -print0 | while read -d $'\0' SITE_CONFIG_FILE; do
  DIR="$(dirname "$SITE_CONFIG_FILE")"
  (
  cd "$DIR"
  source vvv-init.sh
  )
done

# Look for Nginx vhost files, symlink them into the custom sites dir
for SITE_CONFIG_FILE in $(find $4 -maxdepth 4 -name 'vvv-nginx.conf'); do
  DEST_CONFIG_FILE=${SITE_CONFIG_FILE//\/srv\/www\//}
  DEST_CONFIG_FILE=${DEST_CONFIG_FILE//\//\-}
  DEST_CONFIG_FILE=${DEST_CONFIG_FILE/%-vvv-nginx.conf/}
  DEST_CONFIG_FILE="vvv-auto-$DEST_CONFIG_FILE-$(md5sum <<< "$SITE_CONFIG_FILE" | cut -c1-32).conf"
  # We allow the replacement of the {vvv_path_to_folder} token with
  # whatever you want, allowing flexible placement of the site folder
  # while still having an Nginx config which works.
  DIR="$(dirname "$SITE_CONFIG_FILE")"
  sed "s#{vvv_path_to_folder}#$DIR#" "$SITE_CONFIG_FILE" > "/etc/nginx/custom-sites/""$DEST_CONFIG_FILE"

  # Resolve relative paths since not supported in Nginx root.
  while grep -sqE '/[^/][^/]*/\.\.' "/etc/nginx/custom-sites/""$DEST_CONFIG_FILE"; do
    sed -i 's#/[^/][^/]*/\.\.##g' "/etc/nginx/custom-sites/""$DEST_CONFIG_FILE"
  done
done

# Parse any vvv-hosts file located in the site repository for domains to
# be added to the virtual machine's host file so that it is self aware.
#
# Domains should be entered on new lines.
echo "Adding domains to the virtual machine's /etc/hosts file..."
find $4 -maxdepth 4 -name 'vvv-hosts' | \
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