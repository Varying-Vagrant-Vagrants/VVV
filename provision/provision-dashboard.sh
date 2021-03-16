#!/usr/bin/env bash

. "/srv/provision/provisioners.sh"

REPO=$1
BRANCH=${2:-master}
DIR="/srv/www/default/dashboard"

if [[ false != "dashboard" && false != "${REPO}" ]]; then
  noroot mkdir -p "${DIR}"
  # Clone or pull the resources repository
  if [[ ! -d "${DIR}/.git" ]]; then
    vvv_info " *  Cloning dashboard from '${REPO}' into '${DIR}' using the '${BRANCH}' branch."
    noroot git clone "${REPO}" --branch "${BRANCH}" "${DIR}" -q
    cd "${DIR}"
    noroot git checkout "${BRANCH}" -q
  else
    vvv_info " * Updating dashboard on the '${BRANCH}' branch."
    cd "${DIR}"
    noroot git reset "origin/${BRANCH}" --hard -q
    noroot git pull origin "${BRANCH}" -q
    noroot git checkout "${BRANCH}" -q
  fi
  vvv_warn " * Note that custom dashboards will be going away in a future update, use a site provisioner and a custom host instead such as dashboard.test."
else
  vvv_info " * Skipping dashboard provisioning, dashboard repository was set to false in config.yml"
fi

provisioner_success
