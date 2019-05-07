#!/usr/bin/env bash

PROVISIONER="/srv/provision/resources/${1}/${2}/provision.sh"
if [[ -f $PROVISIONER ]]; then
    ${PROVISIONER}
fi
