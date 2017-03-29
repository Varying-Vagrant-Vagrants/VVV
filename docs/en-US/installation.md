---
layout: page
title: Installation
permalink: /docs/en-US/installation/
---

## The first "vagrant up"

1. Install [VirtualBox 5.x](https://www.virtualbox.org/wiki/Downloads)
1. Install [Vagrant 1.x](https://www.vagrantup.com/downloads.html)
    * `vagrant` will now be available as a command in your terminal, try it out.
    * ***Note:*** If Vagrant is already installed, use `vagrant -v` to check the version. You may want to consider upgrading if a much older version is in use.
1. Install some these Vagrant plugins:
    1. Install the [vagrant-hostsupdater](https://github.com/cogitatio/vagrant-hostsupdater) plugin with `vagrant plugin install vagrant-hostsupdater`
    1. Install the [vagrant-triggers](https://github.com/emyl/vagrant-triggers) plugin with `vagrant plugin install vagrant-triggers`
        * Note: This step is not a requirement. When installed, it allows for various scripts to fire when issuing commands such as `vagrant halt` and `vagrant destroy`.
        * By default, if vagrant-triggers is installed, a `db_backup` script will run on halt, suspend, and destroy that backs up each database to a `dbname.sql` file in the `{vvv}/database/backups/` directory. These will then be imported automatically if starting from scratch. Custom scripts can be added to override this default behavior.
        * If vagrant-triggers is not installed, VVV will not provide automated database backups.
1. Clone or extract the Varying Vagrant Vagrants project into a local directory
    * `git clone -b master git://github.com/Varying-Vagrant-Vagrants/VVV.git vagrant-local`
    * OR download and extract a [stable release](https://github.com/varying-vagrant-vagrants/vvv/releases) zip or tar.
1. In a terminal, change into the new directory with `cd vagrant-local` and type `vagrant up` to start VVV.
	* For Windows 8 or higher it is recommended that you run the cmd window as Administrator.
    * Be patient as the magic happens. This could take a while on the first run as your local machine downloads the required files.
    * Watch as the virtual machine starts. Your machine ***password may be required*** to properly modify the hosts file on your local machine.
1. Visit any of the [built in WordPress sites](built-in-wp-installs.md) or the VVV Dashboard at [http://vvv.dev](http://vvv.dev)

## What did that do?

The first time you run `vagrant up`, a packaged box containing a basic virtual machine is downloaded to your local machine and cached for future use. The file used by Varying Vagrant Vagrants contains an installation of Ubuntu 14.04 and is about 332MB.

After this box is downloaded, it begins to boot as a sandboxed virtual machine using VirtualBox. Once booted, it runs the provisioning script included with VVV. This initiates the download and installation of around 100MB of packages on the new virtual machine.

The time for all of this to happen depends a lot on the speed of your Internet connection. If you are on a fast cable connection, it will likely only take several minutes.

On future runs of `vagrant up`, the packaged box will be cached on your local machine and Vagrant will only need to apply the requested provisioning.

## And now what?

Now that you're up and running, start poking around and modifying things.

* Access the server via the command line with `vagrant ssh` from your `vagrant-local` directory. You can do almost anything you would do with a standard Ubuntu installation on a full server.
    * **MS Windows users:** An SSH client is generally not distributed with Windows PCs by default. However, a terminal emulator such as [PuTTY](http://www.chiark.greenend.org.uk/~sgtatham/putty/download.html) will provide access immediately. For detailed instructions on connecting with PuTTY, consult the [VVV Wiki](https://github.com/Varying-Vagrant-Vagrants/VVV/wiki/Connect-to-Your-Vagrant-Virtual-Machine-with-PuTTY).
* Power off the box with `vagrant halt` and turn it back on with `vagrant up`.
* Reapply provisioning to a running box with `vagrant provision`.
* Destroy the box with `vagrant destroy`. Any data stored in the virtual machine, including databases, will be deleted. Files added in the `www` directory will persist on the next `vagrant up`.
* Start modifying and adding local files to fit your needs. Take a look at [Adding a Site](adding-a-new-site/index.md) for tips on adding new projects.
