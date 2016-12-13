#!/usr/bin/env bash

PROVISIONER="/vagrant/provision/resources/${1}/${2}/provision.sh"
if [[ -f $PROVISIONER ]]; then
    ${PROVISIONER}
fi
