# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:

MOUNT_OPTIONS = {
  MYSQL: {
    VIRTUALBOX: ['dmode=775', 'fmode=664'],
    PARALLELS: ['nonempty'],
    DOCKER: ['dmode=775', 'fmode=664'],
    HYPERV: ['dir_mode=0775', 'file_mode=0664'],
    VMWARE_DESKTOP: ['umask=000']
  },
  LOG: {
    VIRTUALBOX: ['dmode=777', 'fmode=666'],
    PARALLELS: ['nonempty'],
    DOCKER: ['dmode=777', 'fmode=666'],
    HYPERV: ['dir_mode=0777', 'file_mode=0666'],
    VMWARE_DESKTOP: ['umask=000']
  },
  WWW: {
    VIRTUALBOX: ['dmode=775', 'fmode=774'],
    PARALLELS: ['nonempty'],
    DOCKER: ['dmode=775', 'fmode=774'],
    HYPERV: ['dir_mode=0775', 'file_mode=0774'],
    VMWARE_DESKTOP: ['umask=002']
  }
}

# https://portal.cloud.hashicorp.com/vagrant/discover
VVV_BOXES = {
  VIRTUALBOX: 'bento/ubuntu-24.04',
  PARALLELS: 'bento/ubuntu-24.04',
  DOCKER: 'pentatonicfunk/vagrant-ubuntu-base-images:24.04',
  HYPERV: 'gusztavvargadr/ubuntu-server-2404-lts',
  VMWARE_DESKTOP: 'bento/ubuntu-24.04'
}

VVV_BOX_VERSION = {
  VIRTUALBOX: '>= 0',
  PARALLELS: '202502.21.0',
  DOCKER: '>= 0',
  HYPERV: '>=2404.0.2503',
  VMWARE_DESKTOP: '>= 0'
}

BRANCH_C = "\033[38;5;6m" # 111m"
RED = "\033[38;5;9m" # 124m"
GREEN = "\033[1;38;5;2m" # 22m"
BLUE = "\033[38;5;4m" # 33m"
PURPLE = "\033[38;5;5m" # 129m"
DOCS = "\033[0m"
YELLOW = "\033[38;5;3m" # 136m"
YELLOW_UNDERLINED = "\033[4;38;5;3m" # 136m"
URL = YELLOW_UNDERLINED
CRESET = "\033[0m"

LOCAL_LOG_PATHS = {
  memcached: 'log/memcached',
  nginx: 'log/nginx',
  php: 'log/php',
  provisioners: 'log/provisioners'
}
