#!/usr/bin/env bash

REPO=$1
BRANCH=${2:-master}
DIR="/srv/www/default/dashboard"

date_time=`cat /vagrant/provisioned_at`
logfolder="/var/log/provisioners/${date_time}"
logfile="${logfolder}/provisioner-dashboard.log"
mkdir -p "${logfolder}"
touch "${logfile}"
exec > >(tee -a "${logfile}" )
exec 2> >(tee -a "${logfile}" >&2 )

if [[ false != "dashboard" && false != "${REPO}" ]]; then
  # Clone or pull the resources repository
  if [[ ! -d ${DIR}/.git ]]; then
    echo -e "\nDownloading dashboard, see ${REPO}"
    git clone ${REPO} --branch ${BRANCH} ${DIR} -q
    cd ${DIR}
    git checkout ${BRANCH} -q
  else
    echo -e "\nUpdating dashboard on the ${BRANCH} branch..."
    cd ${DIR}
    git pull origin ${BRANCH} -q
    git checkout ${BRANCH} -q
  fi
fi

exit 0
