#!/usr/bin/env bash

NAME=$1
REPO=$2
BRANCH="${3:-master}"
DIR="/srv/provision/utilities/${NAME}"

if [[ false != "${NAME}" && false != "${REPO}" ]]; then
  # Clone or pull the utility repository
  if [[ ! -d "${DIR}/.git" ]]; then
    echo "* Cloning the \"${NAME}\" utility, see \"${REPO}\""
    git clone "${REPO}" --branch "${BRANCH}" "${DIR}" -q
    cd "${DIR}"
    git checkout "${BRANCH}" -q
  else
    echo -e "* Updating the \"${NAME}\" utility on the \"${BRANCH}\" branch..."
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
