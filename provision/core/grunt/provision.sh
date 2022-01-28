#!/usr/bin/env bash
# @description Install Grunt CLI and Node/Grunt Sass

function install_grunt() {
  vvv_info " * [Grunt]: Installing Grunt CLI"
  npm_config_loglevel=error npm install -g grunt grunt-cli --no-optional --force

  npm_config_loglevel=error hack_avoid_gyp_errors &
  vvv_info " * [Grunt]: Installing grunt-sass"
  npm install -g grunt-sass --no-optional  --unsafe
  vvv_info " * [Grunt]: grunt-sass installed"
  touch /tmp/stop_gyp_hack

  vvv_info " * [Grunt]: Installing grun-cssjanus and grunt-rtlcss"
  npm_config_loglevel=error npm install -g grunt-cssjanus --no-optional
  npm_config_loglevel=error npm install -g grunt-rtlcss --no-optional
  vvv_success " * [Grunt]: Completed Grunt installation"
}

function update_grunt() {
  vvv_info " * [Grunt]: Updating Grunt CLI"
  npm_config_loglevel=error npm update -g grunt grunt-cli --no-optional --force

  npm_config_loglevel=error hack_avoid_gyp_errors &
  vvv_info " * [Grunt]: Updating grunt-sass"
  npm update -g grunt-sass  --unsafe
  vvv_info " * [Grunt]: grunt-sass Updated"
  touch /tmp/stop_gyp_hack

  vvv_info " * [Grunt]: Updating grun-cssjanus and grunt-rtlcss"
  npm_config_loglevel=error npm update -g grunt-cssjanus --no-optional
  npm_config_loglevel=error npm update -g grunt-rtlcss --no-optional
  vvv_success " * [Grunt]: Completed Grunt CLI update"
}

# Grunt
#
# Install or Update Grunt based on current state.  Updates are direct
# from NPM
function hack_avoid_gyp_errors() {
  # exit early if it's already been cancelled
  if [ -f /tmp/stop_gyp_hack ]; then
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
    sleep .2
  done
  vvv_info " * [Grunt]: Stopped gyphack loop"
  rm -f /tmp/stop_gyp_hack
  return 0
}

function grunt_setup() {
  chown -R vagrant:vagrant /usr/lib/node_modules/
  if command -v grunt >/dev/null 2>&1; then
    update_grunt
  else
    install_grunt
  fi
}

export -f grunt_setup

vvv_add_hook tools_setup_synchronous grunt_setup
