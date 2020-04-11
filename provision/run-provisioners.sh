#!/bin/bash
GREEN="\033[38;5;2m"
RED="\033[38;5;9m"
CRESET="\033[0m"
BOLD="\033[1m"
VVV_CONFIG=/vagrant/config.yml;

containsElement () {
  declare -a array=("${2}")
  local i
  for i in "${array[@]}"
  do
      if [ "${i}" == "${1}" ] ; then
          return 0
      fi
  done
  return 1
}

get_config_value() {
  local value=$(shyaml get-value "${1}" 2> /dev/null < ${VVV_CONFIG})
  echo "${value:-$2}"
}

get_config_values() {
  local value=$(shyaml get-values "${1}" 2> /dev/null < ${VVV_CONFIG})
  echo "${value:-$2}"
}

get_config_type() {
  local value=$(shyaml get-type "${1}" 2> /dev/null < ${VVV_CONFIG})
  echo "${value}"
}

get_config_keys() {
  local value=$(shyaml keys "${1}" 2> /dev/null < ${VVV_CONFIG})
  echo "${value:-$2}"
}

provisioner_begin() {
  echo -e "${BOLD}Running provisioner: ${1:-${FUNCNAME[1]}}...${CRESET}"
  start_seconds="$(date +%s)"
}

provisioner_end() {
  end_seconds="$(date +%s)" 
  local elapsed="$(( end_seconds - start_seconds ))"
  echo -e "${BOLD}Provisioner ended: ${1:-${FUNCNAME[1]}}. Took ${elapsed}s.${CRESET}"
}

# provisioners

pre_hook() {
  # provison-pre.sh
  #
  # acts as a pre-hook to our default provisioning script. Anything that
  # should run before the shell commands laid out in provision.sh (or your provision-custom.sh
  # file) should go in this script. If it does not exist, no extra provisioning will run.
  if [[ -f "/srv/provision/provision-pre.sh" ]]; then
    provisioner_begin "pre"
    bash /srv/provision/provision-pre.sh
    provisioner_end "pre"
  fi
}

post_hook() {
  # provision-post.sh
  #
  # acts as a post-hook to the default provisioning. Anything that should
  # run after the shell commands laid out in provision.sh or provision-custom.sh should be
  # put into this file. This provides a good opportunity to install additional packages
  # without having to replace the entire default provisioning script.
  if [[ -f "/srv/provision/provision-post.sh" ]]; then
    provisioner_begin "post"
    bash /srv/provision/provision-post.sh
    provisioner_end "post"
  fi
}

dashboard() {
  provisioner_begin
  local dashboard_repo=$(get_config_value "dashboard.repo" "https://github.com/Varying-Vagrant-Vagrants/dashboard.git")
  local dashboard_branch=$(get_config_value "dashboard.branch" "master")

  bash /srv/provision/provision-dashboard.sh ${dashboard_repo} ${dashboard_branch}
  provisioner_end
}

utility_sources() {
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
      echo -e "${RED}Malformed ${key} config${CRESET}"
    fi
    utilities=()
  fi

  containsElement "core" ${utilities}
  if [[ $? -ne 0 ]]; then
    name+=("core")
    repo+=("https://github.com/Varying-Vagrant-Vagrants/vvv-utilities.git")
    branch+=("master")
  fi

  local utility
  for utility in "${utilities[@]}"; do
    type=$(get_config_type "${key}.${utility}")
    name+=(${utility})
    if [[ "${utilities}" == "str" ]]; then
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
    bash /srv/provision/provision-utility-source.sh ${name[$i]} ${repo[$i]} ${branch[$i]}
    provisioner_end "utility-source-${name[$i]}"
  done
}

utility() {
  local groups=($(get_config_keys utilities))
  local group
  local utility
  for group in ${groups[@]}; do
    local utilities=($(get_config_values utilities.${group}))
    for utility in ${utilities[@]}; do
      provisioner_begin "utility-${group}-${utility}"
      bash /srv/provision/provision-utility.sh ${group} ${utility}
      provisioner_end "utility-${group}-${utility}"
    done
  done
}

main() {
  # provision.sh or provision-custom.sh
  #
  # By default, Vagrantfile is set to use the provision.sh bash script located in the
  # provision directory. If it is detected that a provision-custom.sh script has been
  # created, that is run as a replacement. This is an opportunity to replace the entirety
  # of the provisioning provided by default.
  if [[ -f "/srv/provision/provision-custom.sh" ]]; then
    provisioner_begin "main-custom"
    bash /srv/provision/provision-custom.sh
    provisioner_end "main-custom"
  else
    provisioner_begin
    bash /srv/provision/provision.sh
    provisioner_end
  fi
}

pre_hook
main
dashboard
utility_sources
utility
post_hook
