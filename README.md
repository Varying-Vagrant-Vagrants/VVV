# VVV ( Varying Vagrant Vagrants )

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/206b06167aaf48aab24422cd417e8afa)](https://www.codacy.com/gh/Varying-Vagrant-Vagrants/VVV?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Varying-Vagrant-Vagrants/VVV&amp;utm_campaign=Badge_Grade) [![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/varying-vagrant-vagrants/vvv.svg)](http://isitmaintained.com/project/varying-vagrant-vagrants/vvv "Average time to resolve an issue") [![Percentage of issues still open](http://isitmaintained.com/badge/open/varying-vagrant-vagrants/vvv.svg)](http://isitmaintained.com/project/varying-vagrant-vagrants/vvv "Percentage of issues still open")

VVV is a local developer environment, mainly aimed at [WordPress](https://wordpress.org) developers. It uses [Vagrant](https://www.vagrantup.com) and VirtualBox, and can be used to build sites, and contribute to WordPress.

## How To Use

To use it, download and install [Vagrant](https://www.vagrantup.com) and [VirtualBox](https://www.virtualbox.org/). Then, clone this repository and run:

```shell
vagrant plugin install vagrant-hostsupdater --local
vagrant up --provision
```

When it's done, visit [http://vvv.test](http://vvv.test).

The online documentation contains more detailed [installation instructions](https://varyingvagrantvagrants.org/docs/en-US/installation/).

* **Web**: [https://varyingvagrantvagrants.org/](https://varyingvagrantvagrants.org/)
* **Contributing**: Contributions are more than welcome. Please see our current [contributing guidelines](https://varyingvagrantvagrants.org/docs/en-US/contributing/). Thanks!

## Minimum System requirements

* [Vagrant](https://www.vagrantup.com) 2.2.7 or below.
* [Virtualbox](https://www.virtualbox.org) 6.1.x or below.
* 8GB+ of RAM
* Virtualisation ( VT-X ) enabled in the BIOS ( Windows/Linux )
* Hyper-V turned off ( Windows )

## Software included

For a comprehensive list, please see the [list of installed packages](https://varyingvagrantvagrants.org/docs/en-US/installed-packages/).
