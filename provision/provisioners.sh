#!/bin/bash

exec 6>&1
exec 7>&2

export VVV_PROVISION_ARGS="$@"
export VVV_PROVISION_ARGN="$#"

source /srv/provision/provision-helpers.sh

function should_run() {
  if [[ "${VVV_PROVISION_ARGN}" -eq "0" ]]; then
    return 0
  fi

  local prov
  for prov in $VVV_PROVISION_ARGS; do
    if [[ "$prov" == "$1" ]]; then
      return 0
    fi
  done

  return 1
}

function provisioner_begin() {
  log_to_file "provisioner-${1:-${FUNCNAME[1]}}"
  if ! should_run "${1:-${FUNCNAME[1]}}"; then
    return 1
  fi
  touch "/vagrant/failed_provisioners/provisioner-${1:-${FUNCNAME[1]}}"
  PROVISION_SUCCESS="1"
  echo -e "------------------------------------------------------------------------------------"
  vvv_success " ▷ Running the '${1:-${FUNCNAME[1]}}' provisioner..."
  echo -e "------------------------------------------------------------------------------------"
  start_seconds="$(date +%s)"
}

function provisioner_end() {
  end_seconds="$(date +%s)" 
  local elapsed="$(( end_seconds - start_seconds ))"
  if [[ $PROVISION_SUCCESS -eq "0" ]]; then
    echo -e "------------------------------------------------------------------------------------"
    vvv_success " ✔ The '${1:-${FUNCNAME[1]}}' provisioner completed in ${elapsed} seconds."
    echo -e "------------------------------------------------------------------------------------"
    rm -f "/vagrant/failed_provisioners/provisioner-${1:-${FUNCNAME[1]}}"
  else
    echo -e "------------------------------------------------------------------------------------"
    vvv_error " ! The '${1:-${FUNCNAME[1]}}' provisioner ran into problems, check the full log for more details! It completed in ${elapsed} seconds."
    echo -e "------------------------------------------------------------------------------------"
  fi
  echo ""
}


# provisioners

function pre_hook() {
  # provison-pre.sh
  #
  # acts as a pre-hook to our default provisioning script. Anything that
  # should run before the shell commands laid out in provision.sh (or your provision-custom.sh
  # file) should go in this script. If it does not exist, no extra provisioning will run.
  if [[ -f "/srv/provision/provision-pre.sh" ]]; then
    provisioner_begin "pre"
    if [[ $? -ne "0" ]]; then
      return 0
    fi
    bash /srv/provision/provision-pre.sh
    PROVISION_SUCCESS=$?
    provisioner_end "pre"
  fi
}

function post_hook() {
  # provision-post.sh
  #
  # acts as a post-hook to the default provisioning. Anything that should
  # run after the shell commands laid out in provision.sh or provision-custom.sh should be
  # put into this file. This provides a good opportunity to install additional packages
  # without having to replace the entire default provisioning script.
  if [[ -f "/srv/provision/provision-post.sh" ]]; then
    provisioner_begin "post"
    if [[ $? -ne "0" ]]; then
      return 0
    fi
    bash /srv/provision/provision-post.sh
    PROVISION_SUCCESS=$?
    provisioner_end "post"
  fi
}

function dashboard() {
  provisioner_begin
  if [[ $? -ne "0" ]]; then
    return 0
  fi
  local dashboard_repo=$(get_config_value "dashboard.repo" "https://github.com/Varying-Vagrant-Vagrants/dashboard.git")
  local dashboard_branch=$(get_config_value "dashboard.branch" "master")

  bash /srv/provision/provision-dashboard.sh "${dashboard_repo}" "${dashboard_branch}"
  PROVISION_SUCCESS=$?
  provisioner_end
}

function utility_sources() {
  local name=()
  local repo=()
  local branch=()

  local key="utility-sources"

  local utilities=$(get_config_type "${key}")
  if [[ "${utilities}" == "struct" ]]; then
    utilities=($(get_config_keys "${key}"))
  else
    utilities=$(get_config_value "${key}")
    if [[ ! -z "${utilities}" ]]; then
      vvv_error "Malformed ${key} config"
    fi
    utilities=()
  fi

  containsElement "core" "${utilities}"
  if [[ $? -ne 0 ]]; then
    name+=("core")
    repo+=("https://github.com/Varying-Vagrant-Vagrants/vvv-utilities.git")
    branch+=("master")
  fi

  local utility
  for utility in "${utilities[@]}"; do
    type=$(get_config_type "${key}.${utility}")
    name+=(${utility})
    if [[ "${type}" == "str" ]]; then
      repo+=($(get_config_value "${key}.${utility}"))
      branch+=(master)
    else
      repo+=($(get_config_value "${key}.${utility}.repo"))
      branch+=($(get_config_value "${key}.${utility}.branch" "master"))
    fi
  done

  local i
  for i in ${!name[@]}; do
    provisioner_begin "utility-source-${name[$i]}"
    if [[ $? -ne "0" ]]; then
      continue
    fi
    bash /srv/provision/provision-utility-source.sh "${name[$i]}" "${repo[$i]}" "${branch[$i]}"
    PROVISION_SUCCESS=$?
    provisioner_end "utility-source-${name[$i]}"
  done
}

function provision_utilities() {
  local groups=($(get_config_keys utilities))
  local group
  local utility
  for group in ${groups[@]}; do
    local utilities=($(get_config_values utilities."${group}"))
    for utility in ${utilities[@]}; do
      provision_utility "${group}" "${utility}"
    done
  done
}

function provision_utility() {
  local group=$1
  local utility=$2
  provisioner_begin "utility-${group}-${utility}"
  if [[ $? -ne "0" ]]; then
    return 0
  fi
  bash /srv/provision/provision-utility.sh "${group}" "${utility}"
  PROVISION_SUCCESS=$?
  provisioner_end "utility-${group}-${utility}"
}

function sites() {
  local sites=($(get_config_keys sites))
  local site
  for site in ${sites[@]}; do
    local skip_provisioning=$(get_config_value "sites.${site}.skip_provisioning" "False")
    if [[ $skip_provisioning == "True" ]]; then
      continue
    fi
    local repo=$(get_config_type "sites.${site}")
    if [[ "${repo}" == "str" ]]; then
      repo=$(get_config_value "sites.${site}" "")
    else
      repo=$(get_config_value "sites.${site}.repo" "")
    fi
    local branch=$(get_config_value "sites.${site}.branch" "master")
    local vm_dir=$(get_config_value "sites.${site}.vm_dir" "/srv/www/${site}")
    local nginx_upstream=$(get_config_value "sites.${site}.nginx_upstream" "php")

    provisioner_begin "site-${site}"
    if [[ $? -ne "0" ]]; then
      continue
    fi
    bash /srv/provision/provision-site.sh "${site}" "${repo}" "${branch}" "${vm_dir}" "${skip_provisioning}" "${nginx_upstream}"
    PROVISION_SUCCESS=$?
    provisioner_end "site-${site}"
  done
}

function main() {
  # provision.sh or provision-custom.sh
  #
  # By default, Vagrantfile is set to use the provision.sh bash script located in the
  # provision directory. If it is detected that a provision-custom.sh script has been
  # created, that is run as a replacement. This is an opportunity to replace the entirety
  # of the provisioning provided by default.
  if [[ -f "/srv/provision/provision-custom.sh" ]]; then
    provisioner_begin "main-custom"
    if [[ $? -ne "0" ]]; then
      return 0
    fi
    bash /srv/provision/provision-custom.sh
    PROVISION_SUCCESS=$?
    provisioner_end "main-custom"
  else
    provisioner_begin
    if [[ $? -ne "0" ]]; then
      return 0
    fi
    bash /srv/provision/provision.sh
    PROVISION_SUCCESS=$?
    provisioner_end
  fi
}
