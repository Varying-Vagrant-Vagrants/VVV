#!/usr/bin/env bash

date_time=$(cat /vagrant/provisioned_at)
logfolder="/var/log/provisioners/${date_time}"
logfile="${logfolder}/provisioner-utility-${1}-${2}.log"
mkdir -p "${logfolder}"
touch "${logfile}"
exec > >(tee -a "${logfile}" )
exec 2> >(tee -a "${logfile}" >&2 )

GREEN="\033[38;5;2m"
RED="\033[38;5;9m"
CRESET="\033[0m"

PROVISIONER="/srv/provision/utilities/${1}/${2}/provision.sh"

if [[ -f $PROVISIONER ]]; then
	echo -e "${GREEN} * Running utility provisioner for '${1}/${2}'${CRESET}"
	start_seconds="$(date +%s)"
    ( ${PROVISIONER} >> "${logfile}" )
    SUCCESS=$?
    end_seconds="$(date +%s)"
	if [ "${SUCCESS}" -eq 0 ]; then
		echo -e "${GREEN} * The '${1}/${2}' provisioner completed in "$(( end_seconds - start_seconds ))" seconds${CRESET}"
		exit 0
	else
		echo -e "${RED} * The '${1}/${2}' provisioner ran into problems, check the full log in log/provisioners/${date_time}/provisioner-utility-${1}-${2}.log for more details! It completed in "$(( end_seconds - start_seconds ))" seconds${CRESET}"
		exit 1
	fi
else
	echo -e "${RED} ! VVV Tried to run the utility provisioner for '${1}/${2}' but ${PROVISIONER} doesn't exist${CRESET}"
fi
