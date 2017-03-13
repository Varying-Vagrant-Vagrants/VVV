---
layout: page
title: Default sites configured in VVV
permalink: /docs/en-US/references/default-sites/
---

#### VVV as a MAMP/XAMPP Replacement

Once Vagrant and VirtualBox are installed, download or clone VVV and type `vagrant up --provision` to automatically build a virtualized Ubuntu server on your computer. See our section on [The First Vagrant Up](#installation---the-first-vagrant-up) for detailed instructions.

Multiple projects can be developed at once in the same environment.

* Use `wp-content/themes` in either the `www/wordpress-default` or `www/wordpress-develop/src` directories to develop themes.
* Use `wp-content/plugins` in either the `www/wordpress-default` or `www/wordpress-develop/src` directories to develop plugins.
* Take advantage of VVV's [auto site configuration](https://github.com/varying-vagrant-vagrants/vvv/wiki/Auto-site-Setup) to provision additional instances of WordPress in `www/`. The [Variable VVV](https://github.com/bradp/vv) project helps to automate this process.
* Use the `www/wordpress-develop` directory to participate in [WordPress core](https://make.wordpress.org/core) development.

VVV's `config`, `database`, `log` and `www` directories are shared with the virtualized server.

These shared directories allow you to work, for example, in `vagrant-local/www/wordpress-default` in your local file system and have those changes immediately reflected in the virtualized server's file system and http://local.wordpress.dev/. Likewise, if you `vagrant ssh` and make modifications to the files in `/srv/www/`, you'll immediately see those changes in your local file system.

## Use Git instead of Subversion for WordPress core development

By default, VVV provisions WordPress into `/www/wordpress-develop/` from the [WordPress Subversion repository](https://develop.svn.wordpress.org/).

If you prefer to use Git, there is a [bundled script](https://github.com/Varying-Vagrant-Vagrants/VVV/blob/master/config/homebin/develop_git) that converts to using the [Git mirror](https://develop.git.wordpress.org).

To enable Git for core development, use `vagrant ssh` to access the virtual machine and then run `develop_git`. Alternatively, do this in one line with: `vagrant ssh -c /srv/config/homebin/develop_git`.
