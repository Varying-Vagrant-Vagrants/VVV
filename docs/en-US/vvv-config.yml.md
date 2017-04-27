---
layout: page
title: vvv-config.yml
permalink: /docs/en-US/vvv-config/
---

`vvv-config.yml` is the default config file that VVV uses to set itself up. Copy this file to `vvv-custom.yml` to make changes and add your own site.

Here's the full default config file, with every key and option that VVV supports:

```yaml
sites:
  wordpress-default:
    repo: https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-default.git
    vm_dir: /srv/www/wordpress-default
    local_dir: /Users/janesmith/dev/www/vvv/www/wordpress-default
    branch: master
    skip_provisioning: false
    allow_customfile: false
    nginx_upstream: php
    hosts:
      - local.wordpress.dev

  wordpress-develop:
    repo: https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-develop.git
    vm_dir: /srv/www/wordpress-develop
    local_dir: /Users/janesmith/dev/www/vvv/www/wordpress-develop
    branch: master
    skip_provisioning: true
    allow_customfile: false
    nginx_upstream: php
    hosts:
      - develop.wordpress.dev

vm_config:
  memory: 1024
  cores: 1

utilities:
  core:
    - memcached-admin
    - opcache-status
    - phpmyadmin
    - webgrind
utility-sources:
  core: https://github.com/Varying-Vagrant-Vagrants/vvv-utilities.git
```

## Anatomy of a Site config

Lets break apart the `wordpress-default` site:

```yaml
sites:
  wordpress-default:
    repo: https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-default.git
    vm_dir: /srv/www/wordpress-default
    local_dir: /Users/janesmith/dev/www/vvv/www/wordpress-default
    branch: master
    skip_provisioning: false
    allow_customfile: false
    nginx_upstream: php
    hosts:
```

When defining a site, the only required item is the name of the site. This single line would be a perfectly valid site definition:

```yaml
example-site:
```


### repo

This specifies a git repository that contains the site to be provisioned. If set, VVV will grab the git repo, place it in the appropriate place, and provision the site

There's also a shorthand version:

```yaml
example-site: https://github.com/Varying-Vagrant-Vagrants/...
```

### branch

If the `repo` key is being ued, and the `branch` key is set, VVV will checkout that branch instead of `master`.

### vm_dir

This controls the folder inside the virtual machine the sites folder is mapped on to.

### local_dir

This controls which folder on the host machine VVV uses for this site. By default, it uses a folder with the sites name inside the `www` subfolder.

For example, a site named `test` would be inside the `www/test` folder.

### skip_provisioning

If there are a lot of sites in `vvv-custom.yml`, you may want to skip several sites that aren't in use. To do this, set the `skip_provisioning` key, for example:

```yaml
sites:
  wordpress-default:
    repo: https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-default.git
    skip_provisioning: true
```

Now VVV will skip that site when running the provisioner. This means that the hosts, nginx config, and `vvv-init.sh` script will not be copied or ran.

### allow_customfile

It may be necessary to run ruby script during provisioning to do more complex things. This might be installing system wide packages inside the virtual machine etc.

It's recommend that instead the `utilities` section be used when possible. Writing your own Vagrant Ruby code is an in depth topic, and could destabilise VVV if done incorrectly. This should only be used by advanced users with knowledge of the subject.

### nginx_upstream

This option sets where Nginx passes requests to, and is primarily for setting the PHP version used. [You can read more about it here](adding-a-new-site/changing-php-version.md)

### hosts

This defines the domains and hosts for VVV to listen on. If the vagrant host plugin is installed, your hosts file will automatically be updated when the machine is turned on and off

```yaml
hosts:
  - local.wordpress.dev
```

## vm_config

These settings control the Virtual Machine that Vagrant creates. By default this is 1024MB of RAM and 1 core.

This configuration would tell VVV to create a virtual machine with 2GB of RAM and a single CPU core:

```yaml
vm_config:
  memory: 2048
  cores: 1
```

## Utilities

These are repositories and packages VVV pulls in to provide services, such as MySQL, PHPMyAdmin, or Memcached. Additional versions of PHP may be added here.
