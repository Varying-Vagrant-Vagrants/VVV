#!/usr/bin/env bash

. "/srv/provision/provisioners.sh"

REPO=$1
BRANCH=${2:-master}
DIR="/srv/www/default/dashboard"

if [[ false != "dashboard" && false != "${REPO}" ]]; then
  noroot mkdir -p "${DIR}"
  # Clone or pull the resources repository
  if [[ ! -d "${DIR}/.git" ]]; then
    vvv_info " * Cloning dashboard from '${REPO}' into '${DIR}' using the '${BRANCH}' branch."

    # Clone or pull the site repository
    if noroot git clone --recursive --branch "${BRANCH}" "${REPO}" "${DIR}"; then
      vvv_success " ✔ Dashboard cloned successfully"
    else
      vvv_error " ! Git failed to clone the dashboard. It tried to clone the ${BRANCH} of ${REPO} into ${DIR}${CRESET}"
      exit 1
    fi
  else
    vvv_info " * Updating dashboard on the '${BRANCH}' branch."
    pushd "${DIR}"
    vvv_info " * Fetching origin ${BRANCH}"
    noroot git fetch origin "${BRANCH}"
    vvv_info " * performing a hard reset on origin/${BRANCH}"
    noroot git reset "origin/${BRANCH}" --hard
    popd
  fi
  vvv_warn " * Note that custom dashboards will be going away in a future update, use a site provisioner and a custom host instead such as dashboard.test."
else
  vvv_info " * Skipping dashboard provisioning, dashboard repository was set to false in config.yml"
fi

provisioner_success
