#!/usr/bin/env bash

PROVISIONER="/srv/provision/utilities/${1}/${2}/provision.sh"

if [[ -f $PROVISIONER ]]; then
	echo "Running utility provisioner for '${1}/${2}'"
    ${PROVISIONER}
else
	echo "Tried to run the utility provisioner for '${1}/${2}' but ${PROVISIONER} doesn't exist"
fi
