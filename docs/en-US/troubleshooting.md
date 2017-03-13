---
layout: page
title: Troubleshooting
permalink: /docs/en-US/troubleshooting/
---

Need help?

* Let us have it! Don't hesitate to open a new issue on GitHub if you run into trouble or have any tips that we need to know.
* The [VVV Wiki](https://github.com/varying-vagrant-vagrants/vvv/wiki) also contains documentation that may help.

## Starting from Fresh

Sometimes, a clean fresh start fixes things, to do this, run the following commands:

```shell
# make sure this is the latest VVV
git pull
# Turn off the machine
vagrant halt
# Destroy the machine
vagrant destroy
# Make sure we use the latest version of the base box§
vagrant box update
# Make sure the recommended vagrant plugins are installed
vagrant plugin install vagrant-triggers vagrant-vbguest vagrant-hostsupdater
# And that they're all up to date
vagrant plugin update
# Start VVV and create the VM from scratch
vagrant up --provision
```

## Common Problems

### SSH Timeout During Provision

This is a generic error that can indicate multiple things, including:

 - An unexpected error in the provisioner
 - Failure to setup the connection between Vagrant and the running VM ( a handful of versions of Vagrant failed to install the necessary keys inside the VM, updating Vagrant, destroying the box, and doing a clean provision should resolve this )
 - Local network IP clashes
 - Firewalls
 - Unusual network configurations
 - Many other possible problems

If this happens, do the following, and provide the results when asking for help.

 - Run `vagrant ssh`, if this works and you're able to get inside the VVV machine and run commands that is useful information, and may allow you to manually run the commands to bring up nginx and PHP
 - Halt the machine with `vagrant halt` and turn it back on in verbose logging mode using `vagrant up --provision --verbose | vvv.log`. The log file may then reveal errors that might not show in the terminal. Send this file when reporting problems.

## Corrupt VM

It's possible that the Virtual Machine file system may become corrupted. This might happen if your VM didn't shut down correctly, perhaps there was a power cut or your laptop ran out of power unexpectedly.

In this scenario, your files should be safe on the host filesystem. If the Vagrant triggers plugin is installed, a database backup will be available. Using these, the site can be recovered from a fresh VVV box.

Run `vagrant halt; vagrant destroy` to delete the Virtual Machine, followed by `vagrant up --provision` to recreate the machine. When the process is finished, restore the database from backups.

For more information on backups, see the [backups](#backups) section below.


## Common Causes of Problems

### Typos in `vvv-custom.yml`

If there's a typo or syntax error in `vvv-custom.yml` the provisioner will fail. Make sure the file is valid YAML when making changes to this file.

### Out of Date VVV

VVV is an active project, but if it isn't up to date you might suffer from bugs that have already been fixed. Do a `git pull` and restart/reprovision VVV.

### Out of Date Software

Mismatched Virtualbox and Guest additions can cause problems, as can older versions of Vagrant. When troubleshooting a problem, update to the latest versions of software, then verify the problem still exists after a `vagrant halt;vagrant up --provision`

### Local Network IP Clashes

The network configuration picks an IP of 192.168.50.4. It could cause conflicts on your existing network if you *are* on a 192.168.50.x subnet already. You can configure any IP address in the `Vagrantfile` and it will be used on the next `vagrant up --provision`

### Vagrant and VirtualBox

VVV relies on the stability of both Vagrant and VirtualBox. These caveats are common to Vagrant environments and are worth noting:
* If the directory VVV is inside of is moved once provisioned (`vagrant-local`), it may break.
    * If `vagrant destroy` is used before moving, this should be fine.
* If VirtualBox is uninstalled, VVV will break.
* If Vagrant is uninstalled, VVV will break.

### Memory Allotment

The default memory allotment for the VVV virtual machine is 1024MB. If you would like to raise or lower this value to better match your system requirements, a [you can do so with the vm_config section of `vvv-custom.yml`](vm_config.md) is in the wiki.

### 64bit Ubuntu and Older CPUs

Since version 1.2.0, VVV has used a 64bit version of Ubuntu. Some older CPUs (such as the popular *Intel Core 2 Duo* series) do not support this. Changing the line `config.vm.box = "ubuntu/trusty64"` to `"ubuntu/trusty32"` in the `Vagrantfile` before `vagrant up` will provision a 32bit version of Ubuntu that will work on older hardware.

## Backups

In the event that you're stuck or at a loss, VVV tries to generate database backups at `VVV/database/backups/*.sql`, with a file for each database.

This coupled with the uploads in the file system should allow the VVV environment to be recreated from a clean slate.
