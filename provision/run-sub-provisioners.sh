#!/bin/bash
GREEN="\033[38;5;2m"
RED="\033[38;5;9m"
CRESET="\033[0m"
BOLD="\033[1m"
VVV_CONFIG=/vagrant/config.yml;

get_config_value() {
  local value=$(shyaml get-value "${1}" 2> /dev/null < ${VVV_CONFIG})
  echo "${value:-$2}"
}

provisioner_begin() {
  echo -e "${BOLD}Running provisioner: ${FUNCNAME[1]}...${CRESET}"
  start_seconds="$(date +%s)"
}

provisioner_end() {
  end_seconds="$(date +%s)" 
  elapsed="$(( end_seconds - start_seconds ))"
  echo -e "${BOLD}Provisioner ended: ${FUNCNAME[1]}. Took ${elapsed}s.${CRESET}"
}

# provisioners

dashboard() {
  provisioner_begin
  dashboard_repo=$(get_config_value "dashboard.repo" "https://github.com/Varying-Vagrant-Vagrants/dashboard.git")
  dashboard_branch=$(get_config_value "dashboard.branch" "master")

  bash /srv/provision/provision-dashboard.sh ${dashboard_repo} ${dashboard_branch}
  provisioner_end
}

dashboard
