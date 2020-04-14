#!/usr/bin/env bash

. "/srv/provision/provisioners.sh"

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
	vvv_error " ! VVV Tried to run the utility provisioner for '${1}/${2}' but ${PROVISIONER} doesn't exist."
	exit 1
fi
