#!/usr/bin/env bash
# @description Install Grunt CLI and Node/Grunt Sass

set -eo pipefail

function install_grunt() {
  vvv_info " * [Grunt]: Installing Grunt CLI"
  npm install -g grunt grunt-cli grunt-sass grunt-cssjanus grunt-rtlcss --no-optional
  vvv_success " * [Grunt]: Completed Grunt installation"
}

function update_grunt() {
  vvv_info " * [Grunt]: Updating Grunt CLI grunt-sass grunt-cssjanus and grunt-rtlcss"
  npm update -g grunt grunt-cli grunt-sass grunt-cssjanus grunt-rtlcss --no-optional
  vvv_success " * [Grunt]: Completed Grunt CLI update"
}

# Grunt
#
# Install or Update Grunt based on current state.  Updates are direct
# from NPM
function hack_avoid_gyp_errors() {
  # exit early if it's already been cancelled
  if [ -f /tmp/stop_gyp_hack ]; then
    vvv_success " * [Grunt]: Stopping gyphack loop early"
    rm -f /tmp/stop_gyp_hack
    return 0
  fi

  # Without this, we get a bunch of errors when installing `grunt-sass`:
  # > node scripts/install.js
  # Unable to save binary /usr/lib/node_modules/.../node-sass/.../linux-x64-48 :
  # { Error: EACCES: permission denied, mkdir '/usr/lib/node_modules/... }
  # Then, node-gyp generates tons of errors like:
  # WARN EACCES user "root" sdoes not have permission to access the dev dir
  # "/usr/lib/node_modules/grunt-sass/node_modules/node-sass/.node-gyp/6.11.2"
  # TODO: Why do child processes of `npm` run as `nobody`?
  vvv_info " * [Grunt]: starting gyphack loop"
  while [ ! -f /tmp/stop_gyp_hack ]; do
    if [ -d /usr/lib/node_modules/grunt-sass/ ]; then
      chown -R nobody:vagrant /usr/lib/node_modules/grunt-sass/
    fi
    sleep .1
  done
  vvv_success " * [Grunt]: Stopped gyphack loop"
  rm -f /tmp/stop_gyp_hack
  return 0
}

function grunt_setup() {
  if [ -d /usr/lib/node_modules/grunt-sass/ ]; then
    chown -R vagrant:vagrant /usr/lib/node_modules/
  fi
  nvm use default
  if command -v grunt >/dev/null 2>&1; then
    update_grunt
  else
    install_grunt
  fi
}
export -f grunt_setup

vvv_add_hook tools_setup_synchronous grunt_setup
