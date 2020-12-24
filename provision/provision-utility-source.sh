#!/usr/bin/env bash
set -eo pipefail

NAME=$1
REPO=$2
BRANCH="${3:-master}"
DIR="/srv/provision/utilities/${NAME}"

. "/srv/provision/provisioners.sh"

if [[ false != "${NAME}" && false != "${REPO}" ]]; then
  # Clone or pull the utility repository
  if [[ ! -d "${DIR}/.git" ]]; then
    vvv_info "* Cloning the \"${NAME}\" utility, see \"${REPO}\""
    git clone "${REPO}" --branch "${BRANCH}" "${DIR}" -q
    cd "${DIR}"
    git checkout "${BRANCH}" -q
    vvv_success " * Git clone and checkout complete"
  else
    vvv_info "* Updating the \"${NAME}\" utility on the \"${BRANCH}\" branch..."
    cd "${DIR}"
    git pull origin "${BRANCH}" -q
    git checkout "${BRANCH}" -q
    vvv_success " * Git pull and checkout complete"
  fi
else
  if [[ false == "${NAME}" && false == "${REPO}" ]]; then
    vvv_error "Error: VVV tried to provision a utility, but no name or git repo was supplied, double check your config/config.yml file is correct and has the right indentation"
    exit 1
  fi
  if [[ false == "${NAME}" ]]; then
    vvv_error "Error: While processing a utility, a utility with a blank name was found, with the git repo ${REPO}"
    exit 1
  fi

  if [[ false == "${REPO}" ]]; then
    vvv_error "Error: While processing the ${NAME} utility, VVV could not find a git repository to clone"
    exit 1
  fi
fi

provisioner_success
