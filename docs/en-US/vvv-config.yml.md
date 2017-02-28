# vvv-config.yml

`vvv-config.yml` is the default config file that VVV uses to set itself up. Copy this file to `vvv-custom.yml` to make changes and add your own site.

Here's the full default config file, with every key and option that VVV supports:

```
sites:
    wordpress-default:
      repo: https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-default.git
      vm_dir: /srv/www/wordpress-default
      local_dir: /Users/janesmith/dev/www/vvv/www/wordpress-default
      branch: "master
      skip_provisioning: false
      allow_customfile: false
      nginx_upstream: php
    wordpress-develop:
      repo: https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-develop.git
      vm_dir: /srv/www/wordpress-develop
      local_dir: /Users/janesmith/dev/www/vvv/www/wordpress-develop
      branch: master
      skip_provisioning: true
      allow_customfile: false
      nginx_upstream: php

  utilities:
    core:
      - memcached-admin
      - opcache-status
      - phpmyadmin
      - webgrind
  utility-sources:
    core: https://github.com/Varying-Vagrant-Vagrants/vvv-utilities.git
```
