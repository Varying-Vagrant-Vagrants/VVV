#!/usr/bin/env bash

NAME=$1
REPO=$2
BRANCH="${3:-master}"
DIR="/srv/provision/utilities/${NAME}"

GREEN="\033[38;5;2m"
RED="\033[38;5;9m"
CRESET="\033[0m"

logfile="provisioner-utility-source-${NAME}"
. /srv/provision/helpers/log_to_file "${logfile}"

if [[ false != "${NAME}" && false != "${REPO}" ]]; then
  # Clone or pull the utility repository
  if [[ ! -d "${DIR}/.git" ]]; then
    echo -e "${GREEN} * Cloning the \"${NAME}\" utility, see \"${REPO}\"${CRESET}"
    git clone "${REPO}" --branch "${BRANCH}" "${DIR}" -q
    cd "${DIR}"
    git checkout "${BRANCH}" -q
  else
    echo -e "${GREEN} * Updating the \"${NAME}\" utility on the \"${BRANCH}\" branch...${CRESET}"
    cd "${DIR}"
    git pull origin "${BRANCH}" -q
    git checkout "${BRANCH}" -q
  fi
else
  if [[ false == "${NAME}" && false == "${REPO}" ]]; then
    echo -e "${RED}Error: VVV tried to provision a utility, but no name or git repo was supplied, double check your config/config.yml file is correct and has the right indentation${CRESET}"
    exit 1
  fi
  if [[ false == "${NAME}" ]]; then
    echo -e "${RED}Error: While processing a utility, a utility with a blank name was found, with the git repo ${REPO}${CRESET}"
    exit 1
  fi

  if [[ false == "${REPO}" ]]; then
    echo -e "${RED}Error: While processing the ${NAME} utility, VVV could not find a git repository to clone${CRESET}"
  fi
fi

exit 0
