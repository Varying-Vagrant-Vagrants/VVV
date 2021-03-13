#!/usr/bin/env bash
# @description Provides helper functions for when provisioners start and finish

# don't allow inclusion of this file more than once
if ( type provisioner_begin &>/dev/null ); then
	return
fi

# backup original file descriptors
exec 6>&1
exec 7>&2

source /srv/provision/provision-helpers.sh

VVV_PROVISIONER_RUNNING=""

# @description Signal that a provisioner has begun, and setup timings, failed provisioner flags, etc
# @arg $1 string Name of the provisioner
function provisioner_begin() {
  VVV_PROVISIONER_RUNNING="${1:-${FUNCNAME[1]}}"
  touch "/vagrant/failed_provisioners/provisioner-${VVV_PROVISIONER_RUNNING}"
  log_to_file "provisioner-${VVV_PROVISIONER_RUNNING}"
  vvv_success " ▷ Running the <b>'${VVV_PROVISIONER_RUNNING}'</b><success> provisioner...</success>"
  start_seconds="$(date +%s)"
  trap "provisioner_end" EXIT
}

# @description Signal that a provisioner has finished
# @arg $1 string Name of the provisioner
function provisioner_end() {
  local PROVISION_SUCCESS="${1:-"1"}"
  local end_seconds="$(date +%s)"
  local elapsed="$(( end_seconds - start_seconds ))"
  if [[ $PROVISION_SUCCESS -eq "0" ]]; then
    vvv_success " ✔ The <b>'${VVV_PROVISIONER_RUNNING}'</b><success> provisioner completed in </success><b>${elapsed}</b><success> seconds.</success>"
    rm -f "/vagrant/failed_provisioners/provisioner-${VVV_PROVISIONER_RUNNING}"
  else
    vvv_error " ! The <b>'${VVV_PROVISIONER_RUNNING}'</b><error> provisioner ran into problems, the full log is available at <b>'${VVV_CURRENT_LOG_FILE}'</b><error>. It completed in <b>${elapsed}</b><error> seconds."
  fi
  trap - EXIT
}

if [[ ! -z $VVV_LOG ]]; then
  provisioner_begin "${VVV_LOG}"
fi

# @description Signal that a provisioner has finished with success
function provisioner_success() {
  if [[ ! -z $VVV_LOG ]]; then
    provisioner_end 0
  fi
}
