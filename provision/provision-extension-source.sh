#!/usr/bin/env bash
set -eo pipefail

NAME=$1
REPO=$2
BRANCH="${3:-master}"
DIR="/srv/provision/extensions/${NAME}"

. "/srv/provision/provisioners.sh"

if [[ false != "${NAME}" && false != "${REPO}" ]]; then
  # Clone or pull the extension repository
  if [[ ! -d "${DIR}/.git" ]]; then
    vvv_info " * Cloning the \"${NAME}\" extension, see \"${REPO}\""
    noroot mkdir -p "${DIR}"
    noroot git clone "${REPO}" --branch "${BRANCH}" "${DIR}" -q
    cd "${DIR}"
    noroot git checkout "${BRANCH}" -q
    vvv_success " * Extension git clone and checkout complete"
  else
    vvv_info " * Updating the \"${NAME}\" extension on the \"${BRANCH}\" branch..."
    cd "${DIR}"
    noroot git pull origin "${BRANCH}" -q
    noroot git checkout "${BRANCH}" -q
    vvv_success " * Extension git pull and checkout complete"
  fi
else
  if [[ false == "${NAME}" && false == "${REPO}" ]]; then
    vvv_error " ! Error: VVV tried to provision a extension, but no name or git repo was supplied, double check your config/config.yml file is correct and has the right indentation"
    exit 1
  fi
  if [[ false == "${NAME}" ]]; then
    vvv_error " ! Error: While processing a extension, a extension with a blank name was found, with the git repo ${REPO}"
    exit 1
  fi

  if [[ false == "${REPO}" ]]; then
    vvv_error " ! Error: While processing the ${NAME} extension, VVV could not find a git repository to clone"
    exit 1
  fi
fi

provisioner_success
