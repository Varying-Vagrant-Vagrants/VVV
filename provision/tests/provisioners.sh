#!/bin/bash
. "/srv/provision/provisioners.sh"

export VVV_LOG=""

# provisioners

function pre_hook() {
  # provison-pre.sh
  #
  # acts as a pre-hook to our default provisioning script. Anything that
  # should run before the shell commands laid out in provision.sh (or your provision-custom.sh
  # file) should go in this script. If it does not exist, no extra provisioning will run.
  if [[ -f "/srv/provision/provision-pre.sh" ]]; then
    VVV_LOG="pre"
    bash /srv/provision/provision-pre.sh
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
    VVV_LOG="post"
    bash /srv/provision/provision-post.sh
  fi
}

function provision_dashboard() {
  local dashboard_repo=$(get_config_value "dashboard.repo" "https://github.com/Varying-Vagrant-Vagrants/dashboard.git")
  local dashboard_branch=$(get_config_value "dashboard.branch" "master")

  VVV_LOG="dashboard"
  bash /srv/provision/provision-dashboard.sh "${dashboard_repo}" "${dashboard_branch}"
}

function provision_utility_sources() {
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
    VVV_LOG="utility-source-${name[$i]}"
    bash /srv/provision/provision-utility-source.sh "${name[$i]}" "${repo[$i]}" "${branch[$i]}"
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
  VVV_LOG="utility-${group}-${utility}"
  bash /srv/provision/provision-utility.sh "${group}" "${utility}"
}

function provision_sites() {
  local sites=($(get_config_keys sites))
  local site
  for site in ${sites[@]}; do
    provision_site "${site}"
  done
}

function provision_site() {
  local site=$1
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

  VVV_LOG="site-${site}"
  bash /srv/provision/provision-site.sh "${site}" "${repo}" "${branch}" "${vm_dir}" "${skip_provisioning}" "${nginx_upstream}"
}

function provision_main() {
  # provision.sh or provision-custom.sh
  #
  # By default, Vagrantfile is set to use the provision.sh bash script located in the
  # provision directory. If it is detected that a provision-custom.sh script has been
  # created, that is run as a replacement. This is an opportunity to replace the entirety
  # of the provisioning provided by default.
  if [[ -f "/srv/provision/provision-custom.sh" ]]; then
    VVV_LOG="main-custom"
    bash /srv/provision/provision-custom.sh
  else
    VVV_LOG="main"
    bash /srv/provision/provision.sh
  fi
}
