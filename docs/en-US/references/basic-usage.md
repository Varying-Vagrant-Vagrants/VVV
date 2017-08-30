---
layout: page
title: Basic Usage
permalink: /docs/en-US/references/basic-usage/
---

* [Basic usage](basic-usage.md) provides the basics of using Vagrant to manage a VM.
* [Default credentials](default-credentials.md) is a list of the default usernames and passwords provsioned in VVV.
* [Default sites](default-sites.md) installed with VVV.
* [Installed packages](installed-packages.md) is a list of packages installed during default provisioning.
* [PHP Extensions](php-extensions.md) is a list of the PHP extensions provisioned by default.


## Using a GUI

This documentation assumes some very basic terminal/command line knowledge to run simple commands. However, some people prefer the convenience of a visual UI. If you fall into this category then consider the [Vagrant Manager](http://vagrantmanager.com/)  project.

Note: Until you provision VVV for the first time, Vagrant Manager will not pick up VVV. Running `vagrant up --provision`  inside the VVV folder and allowing it to successfully finish should be enough.

## Turning VVV On

```shell
vagrant up
```

If Vagrant triggers are installed, and the VVV machine is turned off, this will also run the provisioner.

## Turning VVV Off

```shell
vagrant halt
```

This will shut down the virtual machine. If the machine is frozen for whatever reason, add the ` --force` parameter. If it still refuses to power off, open VirtualBox and manually power the VM off.

## Restarting VVV

```shell
vagrant reload
```

This will do a restart of the Virtual Machine, and is the same as running `vagrant halt; vagrant up`

## Reloading `vvv-custom.yml`

If you make any changes to your config file, they won't take immediate effect. For changes to take hold, restart VVV using `vagrant reload --provision`
