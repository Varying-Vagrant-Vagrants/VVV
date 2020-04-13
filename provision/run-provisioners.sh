#!/bin/bash

exec 6>&1
exec 7>&2

source /srv/provision/provision-helpers.sh

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
  log_to_file "provisioner-${1:-${FUNCNAME[1]}}"
  touch "/vagrant/failed_provisioners/provisioner-${1:-${FUNCNAME[1]}}"
  PROVISION_SUCCESS="1"
  echo -e "------------------------------------------------------------------------------------"
  echo -e "${GREEN} ▷ Running the '${1:-${FUNCNAME[1]}}' provisioner...${CRESET}"
  echo -e "------------------------------------------------------------------------------------"
  start_seconds="$(date +%s)"
}

provisioner_end() {
  end_seconds="$(date +%s)" 
  local elapsed="$(( end_seconds - start_seconds ))"
  if [[ $PROVISION_SUCCESS -eq "0" ]]; then
    echo -e "------------------------------------------------------------------------------------"
    echo -e "${GREEN} ✔ The '${1:-${FUNCNAME[1]}}' provisioner completed in ${elapsed} seconds.${CRESET}"
    echo -e "------------------------------------------------------------------------------------"
    rm -f "/vagrant/failed_provisioners/provisioner-${1:-${FUNCNAME[1]}}"
  else
    echo -e "------------------------------------------------------------------------------------"
    echo -e "${RED} ! The '${1:-${FUNCNAME[1]}}' provisioner ran into problems, check the full log for more details! It completed in ${elapsed} seconds.${CRESET}"
    echo -e "------------------------------------------------------------------------------------"
  fi
  echo ""
}

provisioner_init() {
  # fix no tty warnings in provisioner logs
  sudo sed -i '/tty/!s/mesg n/tty -s \\&\\& mesg n/' /root/.profile

  # add homebin to secure_path setting for sudo, clean first and then append at the end
  sed -i -E \
  -e "s|:/srv/config/homebin||" \
  -e "s|/srv/config/homebin:||" \
  -e "s|(.*Defaults.*secure_path.*?\".*?)(\")|\1:/srv/config/homebin\2|" \
  /etc/sudoers

  # add homebin to the default environment, clean first and then append at the end
  sed -i -E \
  -e "s|:/srv/config/homebin||" \
  -e "s|/srv/config/homebin:||" \
  -e "s|(.*PATH.*?\".*?)(\")|\1:/srv/config/homebin\2|" \
  /etc/environment

  # source bash_aliases before anything else so that PATH is properly configured on
  # this shell session
  . "/srv/config/bash_aliases"

  export DEBIAN_FRONTEND=noninteractive
  export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1
  export COMPOSER_ALLOW_SUPERUSER=1
  export COMPOSER_NO_INTERACTION=1

  # cleanup
  mkdir -p /vagrant
  mkdir -p /vagrant/failed_provisioners

  rm -f /vagrant/provisioned_at
  rm -f /vagrant/version
  rm -f /vagrant/vvv-custom.yml
  rm -f /vagrant/config.yml

  touch /vagrant/provisioned_at
  echo $(date "+%Y.%m.%d_%H-%M-%S") > /vagrant/provisioned_at

  # copy over version and config files
  cp -f /home/vagrant/version /vagrant
  cp -f /srv/config/config.yml /vagrant

  sudo chmod 0644 /vagrant/config.yml
  sudo chmod 0644 /vagrant/version
  sudo chmod 0644 /vagrant/provisioned_at

  # change ownership for /vagrant folder
  sudo chown -R vagrant:vagrant /vagrant

  VVV_CONFIG=/vagrant/config.yml
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
    PROVISION_SUCCESS=$?
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
    PROVISION_SUCCESS=$?
    provisioner_end "post"
  fi
}

dashboard() {
  provisioner_begin
  local dashboard_repo=$(get_config_value "dashboard.repo" "https://github.com/Varying-Vagrant-Vagrants/dashboard.git")
  local dashboard_branch=$(get_config_value "dashboard.branch" "master")

  bash /srv/provision/provision-dashboard.sh ${dashboard_repo} ${dashboard_branch}
  PROVISION_SUCCESS=$?
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
    bash /srv/provision/provision-utility-source.sh ${name[$i]} ${repo[$i]} ${branch[$i]}
    PROVISION_SUCCESS=$?
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
      PROVISION_SUCCESS=$?
      provisioner_end "utility-${group}-${utility}"
    done
  done
}

sites() {
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
    bash /srv/provision/provision-site.sh "${site}" "${repo}" "${branch}" "${vm_dir}" "${skip_provisioning}" "${nginx_upstream}"
    PROVISION_SUCCESS=$?
    provisioner_end "site-${site}"
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
    PROVISION_SUCCESS=$?
    provisioner_end "main-custom"
  else
    provisioner_begin
    bash /srv/provision/provision.sh
    PROVISION_SUCCESS=$?
    provisioner_end
  fi
}

provisioner_init
pre_hook
main
dashboard
utility_sources
utility
sites
post_hook
