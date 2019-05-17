#!/usr/bin/env bash

REPO=$1
BRANCH=${2:-master}
DIR="/srv/www/default/dashboard"

date_time=`cat /vagrant/provisioned_at`
logfile="/var/log/provisioners/${date_time}/provisioner-dashboard.log"
mkdir -p "${logfile}"
touch "${logfile}"
exec &> >(tee -a "${logfile}" >&2 )

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
