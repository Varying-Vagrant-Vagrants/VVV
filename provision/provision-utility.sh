#!/usr/bin/env bash

date_time=`cat /vagrant/provisioned_at`
logfolder="/var/log/provisioners/${date_time}"
logfile="${logfolder}/provisioner-utility-${1}-${2}.log"
mkdir -p "${logfolder}"
touch "${logfile}"
exec > >(tee -a "${logfile}" )
exec 2> >(tee -a "${logfile}" >&2 )

PROVISIONER="/srv/provision/utilities/${1}/${2}/provision.sh"

if [[ -f $PROVISIONER ]]; then
	echo "Running utility provisioner for '${1}/${2}'"
    ${PROVISIONER}
else
	echo "Tried to run the utility provisioner for '${1}/${2}' but ${PROVISIONER} doesn't exist"
fi
