#!/usr/bin/env bash

NAME=$1
REPO=$2
DIR="/vagrant/provision/resources/${NAME}"

if [[ false != "${NAME}" && false != "${REPO}" ]]; then
  # Clone or pull the resources repository
  if [[ ! -d ${DIR}/.git ]]; then
    echo -e "\nDownloading ${NAME} resources, see ${REPO}"
    git clone ${REPO} ${DIR}
    cd ${DIR}
    git checkout master
  else
    echo -e "\nUpdating ${NAME} resources..."
    cd ${DIR}
    git pull origin master
    git checkout master
  fi
fi

exit 0