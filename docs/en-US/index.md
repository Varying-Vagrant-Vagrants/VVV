# Varying Vagrant Vagrants


[How to Contribute](contributing.md)

## Table of Content ##
* [Overview](#overview)
* [Installation](installation.md)
* [Credentials](default-credentials.md)
* [Extensions](#helpful-extensions)
* [Copyright](#copyright--license)


#### Software Requirements

[Requirements](installation/software-requirements.md)


#### VVV as a Scaffold

Entirely different server configurations can be created by modifying the files included with VVV and through the use of additional [Auto Site Setup](https://github.com/varying-vagrant-vagrants/vvv/wiki/Auto-site-Setup) provisioning scripts. Check this project out and use it as a base to learn about server provisioning or change everything to make it your own.

### [Installation - The First Vagrant Up](#installation)

[Read how to install VVV here](installation.md)

#### Caveats

See [Troubleshooting](troubleshooting.md)

### [Credentials](#credentials)

[Need usernames and passwords? Find out the default credentials for the built in sites here](default-credentials.md)


### [Helpful Extensions](#extensions)

Supporting init scripts during provisioning allows for some great extensions of VVV core.

* [Variable VVV](https://github.com/bradp/vv) automates setting up new sites, setting up deployments, and more.
* [HHVVVM](https://github.com/johnjamesjacoby/hhvvvm) is an HHVM configuration for VVV.
* The [WordPress Meta Environment](https://github.com/iandunn/wordpress-meta-environment) is a "collection of scripts that provision the official WordPress.org websites into a Varying Vagrant Vagrants installation."
* [VVV Provision Flipper] (https://github.com/bradp/vvv-provision-flipper) allows for easy toggling between VVV provisioning scripts.

#### Custom Dashboards

The dashboard provided by VVV allows for easy replacement by looking for a `www/default/dashboard-custom.php` file. The community has built several great dashboards that may be more useful than the bare info provided by default:

* @topdown's [VVV Dashboard](https://github.com/topdown/VVV-Dashboard)
* @leogopal's [VVV Dashboard](https://github.com/leogopal/VVV-Dashboard)
* @stevenkword's [VVV Dashboard Custom](https://github.com/stevenkword/vvv-dashboard-custom)
* @goblindegook's [VVV Material Dashboard](https://github.com/goblindegook/vvv-material-dashboard)

### Varying Vagrant Vagrants Objectives

* Provide an approachable development environment with a modern server configuration.
* Continue to work towards a stable state of software and configuration included in the default provisioning.
* Provide excellent and clear documentation throughout VVV to aid in both learning and scaffolding.


## History

[Read about the history of the VVV project here](history.md)

## [Copyright / License](#license)

VVV is copyright (c) 2014-2016, the contributors of the VVV project under the [MIT License](LICENSE).

