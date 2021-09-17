#!/bin/bash
. "/srv/provision/provisioners.sh"

export VVV_LOG=""

function vvv_run_provisioner() {
  local STATUS
  bash $@
  STATUS=$?
  if [ "${STATUS}" -eq 0 ]; then
    return $STATUS
  fi
  exit $STATUS
}

# provisioners

function pre_hook() {
  # provison-pre.sh
  #
  # acts as a pre-hook to our default provisioning script. Anything that
  # should run before the shell commands laid out in provision.sh (or your provision-custom.sh
  # file) should go in this script. If it does not exist, no extra provisioning will run.
  if [[ -f "/srv/provision/provision-pre.sh" ]]; then
    VVV_LOG="pre"
    vvv_run_provisioner /srv/provision/provision-pre.sh
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
    vvv_run_provisioner /srv/provision/provision-post.sh
  fi
}

function provision_dashboard() {
  local dashboard_repo=$(get_config_value "dashboard.repo" "https://github.com/Varying-Vagrant-Vagrants/dashboard.git")
  local dashboard_branch=$(get_config_value "dashboard.branch" "master")

  VVV_LOG="dashboard"
  vvv_run_provisioner /srv/provision/provision-dashboard.sh "${dashboard_repo}" "${dashboard_branch}"
}

function provision_extension_sources() {
  local name=()
  local repo=()
  local branch=()

  local key="extension-sources"

  local extensions=$(get_config_type "${key}")
  if [[ "${extensions}" == "struct" ]]; then
    extensions=($(get_config_keys "${key}"))
  else
    extensions=$(get_config_value "${key}")
    if [[ ! -z "${extensions}" ]]; then
      vvv_error "Malformed ${key} config"
    fi
    extensions=()
  fi

  containsElement "core" "${extensions}"
  if [[ $? -ne 0 ]]; then
    name+=("core")
    repo+=("https://github.com/Varying-Vagrant-Vagrants/vvv-utilities.git")
    branch+=("master")
  fi

  local extension
  for extension in "${extensions[@]}"; do
    type=$(get_config_type "${key}.${extension}")
    name+=(${extension})
    if [[ "${type}" == "str" ]]; then
      repo+=($(get_config_value "${key}.${extension}"))
      branch+=(master)
    else
      repo+=($(get_config_value "${key}.${extension}.repo"))
      branch+=($(get_config_value "${key}.${extension}.branch" "master"))
    fi
  done

  local i
  for i in ${!name[@]}; do
    VVV_LOG="extension-source-${name[$i]}"
    vvv_run_provisioner /srv/provision/provision-extension-source.sh "${name[$i]}" "${repo[$i]}" "${branch[$i]}"
  done
}

function provision_extensions() {
  local groups=($(get_config_keys extensions))
  local group
  local extension
  for group in ${groups[@]}; do
    local extensions=($(get_config_values extensions."${group}"))
    for extension in ${extensions[@]}; do
      provision_extension "${group}" "${extension}"
    done
  done
}

function provision_extension() {
  local group=$1
  local extension=$2
  VVV_LOG="extension-${group}-${extension}"
  vvv_run_provisioner /srv/provision/provision-extension.sh "${group}" "${extension}"
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
    return
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
  vvv_run_provisioner /srv/provision/provision-site.sh "${site}" "${repo}" "${branch}" "${vm_dir}" "${skip_provisioning}" "${nginx_upstream}"
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
    vvv_run_provisioner /srv/provision/provision-custom.sh
  else
    VVV_LOG="main"
    vvv_run_provisioner /srv/provision/provision.sh
  fi

  # refresh VVV_CONFIG, as the main provisioner actually creates the /vagrant/config.yml
  VVV_CONFIG=/vagrant/vvv-custom.yml
  if [[ -f /vagrant/config.yml ]]; then
    VVV_CONFIG=/vagrant/config.yml
  fi
  export VVV_CONFIG
}

function provision_tools() {
  VVV_LOG="tools"
  vvv_run_provisioner /srv/provision/provision-tools.sh

  # refresh VVV_CONFIG, as the main provisioner actually creates the /vagrant/config.yml
  VVV_CONFIG=/vagrant/vvv-custom.yml
  if [[ -f /vagrant/config.yml ]]; then
    VVV_CONFIG=/vagrant/config.yml
  fi
  export VVV_CONFIG
}
