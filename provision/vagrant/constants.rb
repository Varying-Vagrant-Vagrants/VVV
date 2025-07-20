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
    DOCKER: [],
    HYPERV: ['dir_mode=0775', 'file_mode=0774'],
    VMWARE_DESKTOP: ['umask=002']
  }
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
