#!/bin/bash
#
# Get PHP version from config.yml or fallback to the system version
set -eo pipefail

source /srv/provision/provision-helpers.sh

phpversion=$(get_config_value "general.default_php")
if [ -z "$phpversion" ] then
  echo $phpversion
else
  phpversion=$(php --version | head -n 1 | cut -d " " -f 2 | cut -c 1-3)
fi

return $phpversion
