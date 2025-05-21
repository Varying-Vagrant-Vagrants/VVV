#!/usr/bin/env bash
# @description Provides helper functions for when provisioners start and finish

# don't allow inclusion of this file more than once
if ( type provisioner_begin &>/dev/null ); then
	return
fi

# backup original file descriptors
if [[ -z "$VVV_FD_BACKUP_DONE" ]]; then
  exec 6>&1
  exec 7>&2
  export VVV_FD_BACKUP_DONE=1
fi

source /srv/provision/provision-helpers.sh

VVV_PROVISIONER_RUNNING=""
VVV_PROVISIONER_START_TIMESTAMP=0

# @description Signal that a provisioner has begun, and setup timings, failed provisioner flags, etc
# @arg $1 string Name of the provisioner
function provisioner_begin() {
  VVV_PROVISIONER_RUNNING="${1:-${FUNCNAME[1]}}"

  if [[ -z "$VVV_PROVISIONER_RUNNING" ]]; then
    vvv_error "Provisioner name not provided or detected"
    return 1
  fi

  touch "/vagrant/failed_provisioners/provisioner-${VVV_PROVISIONER_RUNNING}"
  log_to_file "provisioner-${VVV_PROVISIONER_RUNNING}"
  vvv_success " ▷ Running the <b>'${VVV_PROVISIONER_RUNNING}'</b><success> provisioner...</success>"
  VVV_PROVISIONER_START_TIMESTAMP="$(date -u +%s.%N)"
  trap "provisioner_end" EXIT
}

# @description Signal that a provisioner has finished
# @arg $1 string Name of the provisioner
function provisioner_end() {
  local PROVISION_SUCCESS="${1:-"1"}"
  local end_timestamp
  local elapsed

  end_timestamp="$(date -u +%s.%N)"

  local start_s=${VVV_PROVISIONER_START_TIMESTAMP%.*}
  local start_ns=${VVV_PROVISIONER_START_TIMESTAMP#*.}
  local end_s=${end_timestamp%.*}
  local end_ns=${end_timestamp#*.}

  local elapsed_s=$((end_s - start_s))
  local elapsed_ns=$((10#${end_ns} - 10#${start_ns}))
  if (( elapsed_ns < 0 )); then
    elapsed_s=$((elapsed_s - 1))
    elapsed_ns=$((elapsed_ns + 1000000000))
  fi

  local elapsed_min=$((elapsed_s / 60))
  local elapsed_sec=$((elapsed_s % 60))
  local elapsed_ms
  elapsed_ms=$(printf "%d" $((elapsed_ns / 1000000)))
  elapsed=""
  if [[ "${elapsed_min}" -gt 0 ]]; then
    elapsed+="${elapsed_min}m "
  fi
  if [[ "${elapsed_sec}" -gt 0 || "${elapsed_min}" -gt 0 ]]; then
    elapsed+="${elapsed_sec}s "
  fi
  elapsed+="${elapsed_ms}ms"
  elapsed="${elapsed%" "}"

  if [[ $PROVISION_SUCCESS -eq "0" ]]; then
    vvv_success " ✔ The <b>'${VVV_PROVISIONER_RUNNING}'</b><success> provisioner completed in </success><b>${elapsed}</b><success>.</success>"
    rm -f "/vagrant/failed_provisioners/provisioner-${VVV_PROVISIONER_RUNNING}"
    vvv_log_timing_event "provisioner" "${VVV_PROVISIONER_RUNNING}" "${VVV_PROVISIONER_START_TIMESTAMP}" "${end_timestamp}" "${elapsed}" "success"
  else
    vvv_error " ! The <b>'${VVV_PROVISIONER_RUNNING}'</b><error> provisioner ran into problems, the full log is available at <b>'${VVV_CURRENT_LOG_FILE}'</b><error>. It completed in <b>${elapsed}</b><error> seconds."
    vvv_log_timing_event "provisioner" "${VVV_PROVISIONER_RUNNING}" "${VVV_PROVISIONER_START_TIMESTAMP}" "${end_timestamp}" "${elapsed}" "failure"
  fi

  if [[ -x /srv/config/homebin/vvv_restore_php_default ]]; then
    /srv/config/homebin/vvv_restore_php_default
  else
    vvv_warn "Restore script not found or not executable"
  fi

  trap - EXIT
}

if [[ -n $VVV_LOG ]]; then
  provisioner_begin "${VVV_LOG}"
fi

# @description Signal that a provisioner has finished with success
function provisioner_success() {
  if [[ -n $VVV_LOG ]]; then
    provisioner_end 0
  fi
}
