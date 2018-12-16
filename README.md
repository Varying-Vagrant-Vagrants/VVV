# Varying Vagrant Vagrants

Varying Vagrant Vagrants is an open source [Vagrant](https://www.vagrantup.com) configuration focused on [WordPress](https://wordpress.org) development.

VVV is a [10up](https://10up.com) creation and [transitioned](http://10up.com/blog/varying-vagrant-vagrants-future/) to a community organization in 2014.

* **Latest Stable**: [2.4.0 master branch](https://github.com/Varying-Vagrant-Vagrants/VVV/tree/master)
* **Web**: [https://varyingvagrantvagrants.org/](https://varyingvagrantvagrants.org/)
* **Contributing**: Contributions are more than welcome. Please see our current [contributing guidelines](https://varyingvagrantvagrants.org/docs/en-US/contributing/). Thanks!

VVV is under the [MIT License](LICENSE).

## Objectives

* Approachable development environment with a modern server configuration.
* Stable state of software and configuration in default provisioning.
* Excellent and clear documentation to aid in learning and scaffolding.

VVV is ideal for developing themes and plugins, as well as for [contributing to WordPress core](https://make.wordpress.org/core/).

## Minimum System requirements

- [Vagrant](https://www.vagrantup.com) 2.1.4+
- [Virtualbox](https://www.virtualbox.org) 5.2+

## Software included

VVV is built on a Ubuntu 14.04 LTS (Trusty) base VM and provisions the server with current versions of several software packages, including:

1. [Nginx](http://nginx.org/) (mainline)
1. [MariaDB](https://mariadb.org/) 10.1.x (drop-in replacement for MySQL)
1. [PHP FPM](http://php-fpm.org/) 7.2.x
1. [WP-CLI](http://wp-cli.org/)
1. [Memcached](http://memcached.org/)
1. [PHPUnit](https://phpunit.de/)
1. [Composer](https://github.com/composer/composer)
1. [NodeJs](https://nodejs.org/) v10
1. [Mailhog](https://github.com/mailhog/MailHog)

For a more comprehensive list, please see the [list of installed packages](https://varyingvagrantvagrants.org/docs/en-US/installed-packages/).

## How to Use VVV

VVV requires recent versions of both Vagrant and VirtualBox.

[Vagrant](https://www.vagrantup.com) is a "tool for building and distributing development environments". It works with [virtualization](https://en.wikipedia.org/wiki/X86_virtualization) software such as [VirtualBox](https://www.virtualbox.org/) to provide a virtual machine sandboxed from your local environment.

Besides VirtualBox, provider support is also included for Parallels, Hyper-V, VMWare Fusion, and VMWare Workstation.

The online documentation contains detailed [installation instructions](https://varyingvagrantvagrants.org/docs/en-US/installation/).

Full documentation can be found on the [varyingvagrantvagrants.org](https://varyingvagrantvagrants.org) website.

## Contributors

A full list of contributors can be found [here](https://github.com/Varying-Vagrant-Vagrants/VVV/graphs/contributors).
