#!/usr/bin/env bash

source /srv/provision/provision-helpers.sh

REPO=$1
BRANCH=${2:-master}
DIR="/srv/www/default/dashboard"

logfile="provisioner-dashboard"
log_to_file "${logfile}"

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

exit 0
