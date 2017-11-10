#!/usr/bin/env bash

NAME=$1
REPO=$2
DIR="/vagrant/provision/resources/${NAME}"

if [[ false != "${NAME}" && false != "${REPO}" ]]; then
  # Clone or pull the resources repository
  if [[ ! -d ${DIR}/.git ]]; then
    echo -e "\nDownloading ${NAME} resources, see ${REPO}"
    git clone ${REPO} ${DIR} -q
    cd ${DIR}
    git checkout master -q
  else
    echo -e "\nUpdating ${NAME} resources..."
    cd ${DIR}
    git pull origin master -q
    git checkout master -q
  fi
fi

exit 0
