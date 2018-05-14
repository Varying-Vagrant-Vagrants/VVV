#!/usr/bin/env bash

NAME=$1
REPO=$2
BRANCH=${3:-master}
DIR="/vagrant/provision/resources/${NAME}"

noroot() {
  sudo -EH -u "vagrant" "$@";
}

if [[ false != "${NAME}" && false != "${REPO}" ]]; then
  # Clone or pull the resources repository
  if [[ ! -d ${DIR}/.git ]]; then
    echo -e "\nDownloading ${NAME} resources, see ${REPO}"
    noroot git clone ${REPO} --branch ${BRANCH} ${DIR} -q
    cd ${DIR}
    noroot git checkout ${BRANCH} -q
  else
    echo -e "\nUpdating ${NAME} resources..."
    cd ${DIR}
    noroot git pull origin ${BRANCH} -q
    noroot git checkout ${BRANCH} -q
  fi
fi

exit 0
