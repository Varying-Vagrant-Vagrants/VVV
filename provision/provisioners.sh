#!/bin/bash

if ( type provisioner_begin &>/dev/null ); then
	return
fi

exec 6>&1
exec 7>&2

source /srv/provision/provision-helpers.sh

function provisioner_begin() {
  VVV_PROVISIONER_RUNNING="${1:-${FUNCNAME[1]}}"
  log_to_file "provisioner-${VVV_PROVISIONER_RUNNING}"
  touch "/vagrant/failed_provisioners/provisioner-${VVV_PROVISIONER_RUNNING}"
  echo -e "------------------------------------------------------------------------------------"
  vvv_success " ▷ Running the '${VVV_PROVISIONER_RUNNING}' provisioner..."
  echo -e "------------------------------------------------------------------------------------"
  start_seconds="$(date +%s)"
  trap "provisioner_end" EXIT
}

function provisioner_end() {
  PROVISION_SUCCESS="$?"
  end_seconds="$(date +%s)" 
  local elapsed="$(( end_seconds - start_seconds ))"
  if [[ $PROVISION_SUCCESS -eq "0" ]]; then
    echo -e "------------------------------------------------------------------------------------"
    vvv_success " ✔ The '${VVV_PROVISIONER_RUNNING}' provisioner completed in ${elapsed} seconds."
    echo -e "------------------------------------------------------------------------------------"
    rm -f "/vagrant/failed_provisioners/provisioner-${VVV_PROVISIONER_RUNNING}"
  else
    echo -e "------------------------------------------------------------------------------------"
    vvv_error " ! The '${VVV_PROVISIONER_RUNNING}' provisioner ran into problems, check the full log for more details! It completed in ${elapsed} seconds."
    echo -e "------------------------------------------------------------------------------------"
  fi
  echo ""
  trap - EXIT
}

if [[ ! -z $VVV_LOG ]]; then
  provisioner_begin "${VVV_LOG}"
fi
