#!/bin/bash

# don't allow inclusion of this file more than once
if ( type provisioner_begin &>/dev/null ); then
	return
fi

# backup original file descriptors
exec 6>&1
exec 7>&2

source /srv/provision/provision-helpers.sh

function provisioner_begin() {
  VVV_PROVISIONER_RUNNING="${1:-${FUNCNAME[1]}}"
  touch "/vagrant/failed_provisioners/provisioner-${VVV_PROVISIONER_RUNNING}"
  log_to_file "provisioner-${VVV_PROVISIONER_RUNNING}"
  vvv_success " ▷ Running the '${VVV_PROVISIONER_RUNNING}' provisioner..."
  start_seconds="$(date +%s)"
  trap "provisioner_end" EXIT
}

function provisioner_end() {
  PROVISION_SUCCESS="${1:-"1"}"
  end_seconds="$(date +%s)" 
  local elapsed="$(( end_seconds - start_seconds ))"
  if [[ $PROVISION_SUCCESS -eq "0" ]]; then
    vvv_success " ✔ The '${VVV_PROVISIONER_RUNNING}' provisioner completed in ${elapsed} seconds."
    rm -f "/vagrant/failed_provisioners/provisioner-${VVV_PROVISIONER_RUNNING}"
  else
    vvv_error " ! The '${VVV_PROVISIONER_RUNNING}' provisioner ran into problems, check the full log for more details! It completed in ${elapsed} seconds."
  fi
  echo ""
  trap - EXIT
}

if [[ ! -z $VVV_LOG ]]; then
  provisioner_begin "${VVV_LOG}"
fi

function provisioner_success() {
  if [[ ! -z $VVV_LOG ]]; then
    provisioner_end 0
  fi
}
