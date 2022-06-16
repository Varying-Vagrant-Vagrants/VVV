#!/usr/bin/env bash
set -eo pipefail
PROVISIONER="/srv/provision/extensions/${1}/${2}/provision.sh"

. "/srv/provision/provisioners.sh"

if [[ -f $PROVISIONER ]]; then
    ( bash "${PROVISIONER}" >> "${VVV_CURRENT_LOG_FILE}" )
    SUCCESS=$?
	if [ "${SUCCESS}" -eq 0 ]; then
		provisioner_success
		exit 0
	else
		exit 1
	fi
else
	vvv_error " ! VVV Tried to run the extension provisioner for '${1}/${2}' but ${PROVISIONER} doesn't exist."
	exit 1
fi

provisioner_success
