#!/usr/bin/env bash

NAME=$1
REPO=$2
DIR="/vagrant/provision/resources/${NAME}"

noroot() {
  sudo -EH -u "vagrant" "$@";
}

if [[ false != "${NAME}" && false != "${REPO}" ]]; then
  # Clone or pull the resources repository
  if [[ ! -d ${DIR}/.git ]]; then
    echo -e "\nDownloading ${NAME} resources, see ${REPO}"
    noroot git clone ${REPO} ${DIR}
    cd ${DIR}
    noroot git checkout master
  else
    echo -e "\nUpdating ${NAME} resources..."
    cd ${DIR}
    noroot git pull origin master
    noroot git checkout master
  fi
fi

exit 0