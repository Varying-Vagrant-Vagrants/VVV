---
layout: page
title: VVV 2.0.0 Documentation
permalink: /docs/en-US/
---

## Getting Started

These guides are intended to help with the initial installation of VVV as well as to provide ongoing support as you get familiar with its configuration.

* [Install VVV](installation.md)
* [Add New Sites](adding-a-new-site/index.md)
* [Guide to vvv-custom.yml](vvv-config.yml.md)

## Reference documents

* [Default credentials](references/default-credentials.md) is a list of the default usernames and passwords provsioned in VVV.
* [PHP Extensions](references/php-extensions.md) is a list of the PHP extensions provisioned by default.
* [Installed packages](references/installed-packages.md) is a list of packages installed during default provisioning.
* [Default sites](references/default-sites.md) installed with VVV.
* [Basic usage](references/basic-usage.md) provides the basics of using Vagrant to manage a VM.

## Help

* [Troubleshooting](troubleshooting.md)
* [Migrating from 1.4.1 to 2.0.0](migrating-vvv1.md)

## Helpful Extensions

Support for custom init scripts and site configurations allows for some great extensions of VVV core.

* [Variable VVV](https://github.com/bradp/vv) automates setting up new sites, setting up deployments, and more.
* [WordPress Meta Environment](https://github.com/WordPress/meta-environment) is a "collection of scripts that provision the official WordPress.org websites into a Varying Vagrant Vagrants installation."

## Custom Dashboards

The dashboard provided by VVV allows for easy replacement by looking for a `www/default/dashboard-custom.php` file. The community has built several great dashboards that may be more useful than the bare info provided by default:

* @topdown's [VVV Dashboard](https://github.com/topdown/VVV-Dashboard)
* @leogopal's [VVV Dashboard](https://github.com/leogopal/VVV-Dashboard)
* @stevenkword's [VVV Dashboard Custom](https://github.com/stevenkword/vvv-dashboard-custom)
* @goblindegook's [VVV Material Dashboard](https://github.com/goblindegook/vvv-material-dashboard)

## Copyright / License

VVV is copyright (c), the contributors of the VVV project under the [MIT License](LICENSE).
