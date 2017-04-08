---
layout: page
title: Migrate VVV 1.4.x to 2.0.0
permalink: /docs/en-US/migrate-vvv-1/
---

## Overview

Sites configured in VVV 1.4.x and earlier require additional setup after upgrading to VVV 2.0.0. For best results, a complete `vagrant destroy` is recommended, but a migration without data loss is still possible. Please be sure to backup critical files and databases.

A full provision should be run at least once during this process.

If you'd like, run `vagrant provision` before making the changes to configuration described in this document. This will ensure that core packages are updated and allow you to use `vagrant provision --with-provision` for individual sites. You can also wait until custom sites have been configured before running a full `vagrant provision` to handle everything at once.

## Preparation

The `vvv-config.yml` (default) or `vvv-custom.yml` (custom) configuration files are used by VVV to discover sites. Adding existing sites to one of these files will allow VVV to see and provision them.

First, copy the default `vvv-config.yml` file to `vvv-custom.yml`. This creates a custom configuration and keeps the VVV repository clear for future changes.

## Migrate a custom site

The sites configured in `vvv-custom.yml` map directly to project directories in `vvv/www/`. If, for example, a custom site's files are in `vvv/www/my-test-site`, migrate it from 1.4.x to 2.0.0 by adding `my-test-site:` to the `sites` section of `vvv-custom.yml`:

```YAML
sites:
  # Full default configuration clipped for length.

  my-test-site:

utilities:
  core:
    - memcached-admin
```

Now `vagrant provision` or `vagrant provision --provision-with site-my-test-site` will process the `vvv-init.sh`, `vvv-nginx.conf`, and `vvv-hosts` files in the custom site's directory, `vvv/www/my-test-site/`.

### Using a git repository

If the `my-test-site` project also exists as a git repository with `vvv-init.sh`, `vvv-nginx.conf`, and `vvv-hosts` files, this can also be configured.

```YAML
sites:
  # Full default configuration clipped for length.

  my-test-site: https://github.com/username/my-test-site.git

utilities:
  core:
    - memcached-admin
```

This will cause any changes to be pulled from the repository when `vagrant provision` or `vagrant provision --with-provision site-my-test-site` is run.

See the [full YAML configuration documentation](vvv-config.yml.md) for details on other available options.

## Migrate a default site

VVV 1.4.x provides a handful of sites by default. These sites were provisioned directly by VVV and do not have the necessary structure for the migration used with custom sites.

The easiest route will be to delete the `vvv/www/wordpress-develop` and `vvv/www/wordpress-default` directories. Be sure to back up any crucial files in these directories beforehand.

Once these directories are deleted, run `vagrant provision` or `vagrant provision --provision-with site-wordpress-develop` and `vagrant provision --provsion-with site-wordpress-default`. The configuration in the `vvv-config.yml` or `vvv-custom.yml` files will provide the provisioner with the information it needs to reconfigure these sites using the same databases as before.

## Custom sites in non-standard folders

Some sites are in nested or non-standard folder structures. See the [custom paths and folders](adding-a-new-site/custom-paths-and-folders.md) documentation for how to configure these sites.

## Why is this necessary?

VVV sites work the same way in 1.4.x and 2.0.0, but with one major difference. VVV 2.0.0 uses a YAML configuration file, and VVV 1.4.x scanned for sites automatically.

### How 1.4.x detected sites

When the 1.4.x provisioner ran, it scanned the contents of the entire VVV folder for `vvv-init.sh` and `vvv-nginx.conf` files. As a result, VVV picked up sites regardless of location, even catching nested sites.

Among other things, this caused performance problems. Folder scans could be very slow with some file systems, and there was no way to control which sites were provisioned. If you wanted to provision a site quickly, larger sites had to be moved out of the `www` folder.

### How 2.0.0 detects sites

VVV 2.0.0 does away with site auto-detection. Instead VVV uses a YAML configuration file named `vvv-config.yml` (default) or `vvv-custom.yml` (custom) that lists all sites. A series of options are available to customize the environment and location of each site. This makes provisioning significantly faster, allowing for the provisioning of individual sites or the entire VM.
