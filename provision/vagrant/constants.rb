MOUNT_OPTIONS_PARALLELS_MYSQL = ['nonempty']
MOUNT_OPTIONS_PARALLELS_LOG = ['nonempty']
MOUNT_OPTIONS_PARALLELS_WWW = ['nonempty']

MOUNT_OPTIONS_VIRTUALBOX_MYSQL = ['dmode=775', 'fmode=664']
MOUNT_OPTIONS_VIRTUALBOX_LOG = ['dmode=777', 'fmode=666']
MOUNT_OPTIONS_VIRTUALBOX_WWW = ['dmode=775', 'fmode=774']

MOUNT_OPTIONS_DOCKER_MYSQL = ['dmode=775', 'fmode=664']
MOUNT_OPTIONS_DOCKER_LOG = ['dmode=777', 'fmode=666']
MOUNT_OPTIONS_DOCKER_WWW = []

MOUNT_OPTIONS_HYPERV_MYSQL = ['dir_mode=0775', 'file_mode=0664']
MOUNT_OPTIONS_HYPERV_LOG = ['dir_mode=0777', 'file_mode=0666']
MOUNT_OPTIONS_HYPERV_WWW = ['dir_mode=0775', 'file_mode=0774']

MOUNT_OPTIONS_VMWARE_MYSQL = ['umask=000']
MOUNT_OPTIONS_VMWARE_LOG = ['umask=000']
MOUNT_OPTIONS_VMWARE_WWW = ['umask=002']

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
