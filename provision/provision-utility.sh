#!/usr/bin/env bash

GREEN="\033[38;5;2m"
RED="\033[38;5;9m"
CRESET="\033[0m"

PROVISIONER="/srv/provision/utilities/${1}/${2}/provision.sh"

if [[ -f $PROVISIONER ]]; then
    ( source "${PROVISIONER}" )
    SUCCESS=$?
	if [ "${SUCCESS}" -eq 0 ]; then
		exit 0
	else
		exit 1
	fi
else
	echo -e "${RED} ! VVV Tried to run the utility provisioner for '${1}/${2}' but ${PROVISIONER} doesn't exist.${CRESET}"
fi
