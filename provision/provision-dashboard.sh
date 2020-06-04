#!/usr/bin/env bash

. "/srv/provision/provisioners.sh"

REPO=$1
BRANCH=${2:-master}
DIR="/srv/www/default/dashboard"

if [[ false != "dashboard" && false != "${REPO}" ]]; then
  # Clone or pull the resources repository
  if [[ ! -d "${DIR}/.git" ]]; then
    echo -e " *  Downloading dashboard, see ${REPO}"
    git clone "${REPO}" --branch "${BRANCH}" "${DIR}" -q
    cd "${DIR}"
    git checkout "${BRANCH}" -q
  else
    echo -e " * Updating dashboard on the '${BRANCH}' branch..."
    cd "${DIR}"
    git pull origin "${BRANCH}" -q
    git checkout "${BRANCH}" -q
  fi
else
  echo " * Skipping dashboard provisioning, set to false in config"
fi

provisioner_success

