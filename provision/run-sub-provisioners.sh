#!/bin/bash
VVV_CONFIG=/vagrant/config.yml;

get_config_value() {
  local value=$(shyaml get-value "${1}" 2> /dev/null < ${VVV_CONFIG})
  echo "${value:-$2}"
}

dashboard_repo=$(get_config_value "dashboard.repo" "https://github.com/Varying-Vagrant-Vagrants/dashboard.git")
dashboard_branch=$(get_config_value "dashboard.branch" "master")

bash /srv/provision/provision-dashboard.sh ${dashboard_repo} ${dashboard_branch}