---
layout: page
title: Changelog
permalink: /docs/en-US/changelog/
---

## 2.1.0 (November 8, 2017)

### Enhancements

* Add cosmetic improvements to provisioning. This cleans up quite a bit of the junk that displayed on many lines when it should have displayed on one. See [#1247](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1247).
* Update ack-grep to 2.16. See [#1148](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1148).
* Dashboard (`http://vvv.test`) links now open in new tabs. See [#1168](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1168).
* Speed up checking for `vvv-hosts` files in `Vagrantfile`. See [#1182](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1182).
* Pass more configuration data to the VirtualBox VM on boot. See [#1197](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1197).
* Link to `varyingvagrantvagrants.org`'s Add New Site in the `http://vvv.test` dashboard. See [#1220](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1220).
* Build the VirtualBox VM name from the Vagrant directory and path hash. See [#1236](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1236).
* PHPCS is now installed via Composer. See [#922](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/922).
* VVV now uses .test instead of .dev for new installs and the dashboard. See [#583](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/583).
* Added the VIP minimum coding standards to PHPCS. See [#1279](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1279).

### Bugs

* Fix a wrong path for phpcs and phpcbf executables. See [#1200](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1200).
* Force Composer to use the `scripts` directory instead of `bin`. See [#1202](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1202).
* Fix bug installing `rvm` (which broke MailCatcher). See [#1235](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1235).
* Add `phpcodesniffer-standard` to PHPCS's `composer.json`. See [#1239](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1239).
* Ignore provision generated PHPCS files in Git. See [#1276](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1276).

### Documentation

Many updates to VVV's documentation were made between the release of 2.0.0 and now. As of 2.1.0, the process to contributing to documentation has changed to use the [varyingvagrantvagrants.org](https://github.com/Varying-Vagrant-Vagrants/varyingvagrantvagrants.org) repository. This allows the workflow for shipping documentation changes to proceed separately from shipping VVV releases.

## 2.0.0 (March 13, 2017)

VVV 2.0.0 introduces **breaking changes** in how files are organized and introduces an entirely new method of configuration.

A full `vagrant destroy` and `vagrant up` are recommended for best results. Running `vagrant destroy` will remove your virtual machine entirely and all data stored on the VM will be lost. Please be sure to backup your databases and any files stored in the VM. Files on your local file system will remain, but will still benefit (as always) from a backup.

It is possible to make the from VVV 1.4.x to 2.0.0 without a `vagrant destroy`, but the process will involve restructuring several things. Primarily, default project directories are now expected to contain a `public_html/` directory. This requires not only file changes, but new Nginx configurations. If you need help troubleshooting, don't hesitate to open a new issue.

Please see the [migration documentation](https://varyingvagrantvagrants.org/docs/en-US/migrate-vvv-1/) for tips on how to manage this process.

The decision to include breaking changes in a release is not made lightly. The new ability to configure your installation of VVV with a `vvv-custom.yml` file will make VVV entirely more flexible and maintainable than it has ever been. Please see the [release blog post](https://varyingvagrantvagrants.org/blog/2017/03/13/varying-vagrant-vagrants-2-0-0.html) and [documentation](https://varyingvagrantvagrants.org/docs/en-US/) for more details.

### Features & Enhancements

* Introduce a YAML configuration for VVV. It is now possible to customize your configuration of VVV with a `vvv-custom.yml` file that defines which projects, hosts, and utilities are provisioned. See [#980](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/980).
* Introduce a new [VVV Utilities repository](https://github.com/Varying-Vagrant-Vagrants/vvv-utilities). This works with the new YAML configuration to provide the ability to customize what utilities are provisioned with VVV. See [#1021](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1021).
* Introduce a new [VVV Custom Site Template repository](https://github.com/Varying-Vagrant-Vagrants/custom-site-template). This can be used in `vvv-custom.yml` to quickly add new sites to VVV.
* Introduce a new [VVV WordPress Develop repository](https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-develop). This is used in the default `vvv-config.yml` and can be used in (or excluded from) custom configurations.
* Introduce a new [VVV WordPress Default repository](https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-default). This is used in the default `vvv-config.yml` and can be used in (or excluded from) custom configurations.
* Introduce a new [VVV WordPress Trunk repository](https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-trunk). This can be used in custom configurations if you'd like a checkout of WordPress trunk.
* Add support for custom keys in the YAML configuration. These are available to individual site provisioning scripts. See [#1071](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1071).
* Add support for PHP 5.6, 7.0, and 7.1 via the VVV YAML configuration. See [#1055](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1055).
* Introduce a new documentation structure and an entire set of new documentation. See [#1073](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1073) and, more importantly, [#1112](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1112).
* Introduce documentation explaining the governance of VVV. See [#1118](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1118).
* Install MariaDB 10.1 instead of MySQL 5.5 as part of default provisioning. See [#1005](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1005) and [#1115](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1115).
* Install and update WP-CLI with its PHAR file rather than with a git clone. See [#1057](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1057).
* Add the `php-memcached` package to default provisioning as an alternative to `php-memcache` that works with PHP 7.0. See [#1076](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1076).
* Set `colordiff` as the default `svn diff` command tool. See [#1077](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1077).
* Add a VVV logo to provisioning. See [#1110](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1110).
* Add some style to the default VVV dashboard. See [#1122](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1122).

### Bugs

* Remove old, unused `mu-plugins` directory. See [#1027](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1027).
* Follow redirects when detecting a network connection. See [#1048](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1048).
* Include `$is_args` with `try_files` in Nginx configuration. See [#1075](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1075).
* Remove an attempt to enforce `ipv4` in Postfix as it was not working. See [#1116](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1116).

## 1.4.1 (January 16, 2017)

* Introduce a documentation structure for future releases.

## 1.4.0 (November 2, 2016)

### Bug fixes and Enhancements

* PHP 7.0.x has now replaced PHP 5.5.x. See [#844](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/844).c
* Update PHPUnit to the latest stable 5.6.x version. See [#1004](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1004)
* Xdebug 2.4.0 is now built from source to provide PHP 7.0.x support. See [#869](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/869).
* Disable Xdebug during provisioning so that Composer can operate at normal speed. See [#971](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/971)
* Improve the package installation check to avoid false positives. See [#840](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/840).
* Allow `vvv-nginx.conf` to be located in a project's subdirectory. See [#852](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/852).
* Install the latest version of git via its PPA. See [#872](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/872)
* Assign names to pre, custom, default, and post provisioners to make `--provision-with` possible. See [#806](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/806)
* Install `nvm` to provide access to multiple NodeJS versions. See [#863](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/863)
* Provide the NodeJS 6.x LTS release by default. See [#1007](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1007)
* Switch to NodeSource for NodeJS packages. See [#779](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/779)
* Move `query_cache_*` config for MySQL to its proper place in the `[mysqld]` section. See [#925](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/925)
* Allow WordPress core unit test database configuration to be supplied with environment variables. See [#846](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/846).
* Use the correct command `wp core update` when updating WordPress with WP-CLI. See [#958](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/958)
* Remove the `core.svn.wordpress.org` WordPress trunk checkout from default provisioning. `develop.svn.wordpress.org` remains. See [#921](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/921)
* Checkout and initialize the `develop.svn.wordpress.org` repository in a temporary (non-shared) directory. This addresses issues with a possible race condition during NPM package installation on a shared drive. See [#969](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/969)

## 1.3.0 (February 21, 2016)

### Features
* Add support for Parallels as a provider. See [#479](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/479).
* Add support for Hyper-V as a provider. See [#742](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/742).
* Add support for VMWare Fusion as a provider. See [#587](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/587).
* Add support for VMWare Workstation as a provider. See [656](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/656).
* Add MailCatcher to default provisioning. See [#632](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/632).

### Bug fixes and enhancements
* Composer: Set a custom GitHub token for Composer if it exists. See [#575](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/575).
* Docs: Update inline `Vagrantfile` documentation to better explain various network configurations.
* MySQL: Enable `innodb_file_per_table`. See [#537](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/537).
* npm: Add `npm-check-updates` during provisioning to help manage `package.json` inside the VM. See [#484](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/484).
* PHP: Bump max upload size in PHP and Nginx to 1024MB to support developing on the Internet in the year 2016. See [#599](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/599).
* PHPCS: Set default code standards for PHPCS to WordPress-Core. See [#574](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/574).
* phpMyAdmin: Allow for a custom phpMyAdmin configuration file. See [#688](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/688).
* PHPUnit: Update PHPUnit to 4.3.8. A future update of VVV should include PHP 5.6.x or PHP 7 and allow us to update PHPUnit to 5.x. See [#808](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/808).
* PHPUnit: Allow PHPUnit to run tests from the local machine while using the WordPress unit tests database in the virtual machine. See [#785](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/785).
* PHPUnit: Set the `WP_CORE_DIR` path so PHPUnit tests are run against WordPress trunk. See [#783](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/783).
* Provision: Rewrite `provision.sh` to be more modular. This improves readability and may one day aid in testability. See [#659](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/659).
* Vagrant: Set the default box name to that of the working directory to allow for multiple instances of VVV without conflict. See [#706](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/706).
* Vagrant: Allow for a custom trigger, `vagrant_up` to fire during `vagrant up` and `vagrant reload`. See [#778](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/778).
* Vagrant: Make it easier and more forward-compatible to modify the virtual machine's IP address. See [#781](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/781).
* Vagrant: Clarify default setting of 1 CPU when creating the virtual machine. See [#625](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/625/).
* WordPress: Provide better support for testing with xip.io by accounting for these requests in `wp-config.php`. See [#559](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/559).
* WordPress: SVN repositories configured in VVV provisioning are now set to HTTPS. Existing repositories configured to HTTP will not automatically update during provisioning unless they are first relocated to HTTPS. The `svn relocate` command can be used for this. See [#561](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/561).
* WP-CLI: Add an external configuration for WP-CLI so that a locally installed copy can be used outside of the VM. See [#564](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/564).

## 1.2.0 (December 14, 2014)
* VVV is now [MIT Licensed](https://github.com/Varying-Vagrant-Vagrants/VVV/blob/master/LICENSE).
* ***Possible Breaking:*** By default, database files are no longer mapped to a local location.
	* A full `vagrant destroy` and the removal of MySQL data from `{vvv-dir}/database/data/` is recommended.
	* If database files already exist from an earlier version of VVV, data will continue to be mapped locally until removed.
	* Database data will continue to exist on the virtual machine through `vagrant halt` and `vagrant suspend`.
	* Database data will no longer exist on the virtual machine after `vagrant destroy`.
	* A `db_backup` script is provided by default that creates local backups of each database on halt, suspend, and destroy if the vagrant-triggers plugin is installed.
* ***Possible Breaking:*** Ubuntu has been upgraded from 12.04 LTS to 14.04 LTS. We have also moved from 32bit to 64bit.
	* A full `vagrant destroy` is recommended for best results.
	* A new box will be downloaded for the base virtual machine. If you'd like to free space, remove the old box with `vagrant box remove precise32`. Running `vagrant box list` will show you all base VMs on your local machine.
	* With a new operating system comes a new RSA key. If you are connecting via SSH through an application that relies on your machines `known_hosts` file, you will need to clear the old key for 192.168.50.4. [See #365](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/365)
* Init scripts are now fired with `source` rather than `bash`. Due to this change, something like `cd "$(dirname $0)"` no longer works as expected. See [#373](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/373) and [#370](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/370) for reasoning and discussion.
* WordPress: Add `develop_git` to convert the default SVN checkout to Git.
* PHP: Update to PHP 5.5.x
* PHP: Remove php-apc and apc.ini. Enable built in opcache.
* PHP: Start tracking custom php5-fpm.conf file.
* PHP: Start tracking custom opcache.ini file.
* PHP: Update to PHPUnit 4.0.x
* PHP: Install XDebug PECL extension directly, rather than via apt.
* phpMyAdmin: Update to 4.2.13.1
* WP-Cli: Add support for autocomplete.
* VVV Dashboard: Add [Opcache Status](https://github.com/rlerdorf/opcache-status) for opcache monitoring.
* Bash: Allow for a custom `bash_prompt` file in `config/`
* NodeJS: Use recommended PPAs to install
* NodeJS: Self update NPM during provisioning
* Logs: Map a shared directory for logs, start storing `php_errors.log`
* Nginx: Install using the mainline repository, currently 1.7.x.

## 1.1
* Transition to [Varying Vagrant Vagrants organization](https://github.com/Varying-Vagrant-Vagrants).
* Add a CONTRIBUTING document.
* Add `--allow-root` to all `wp-cli` calls in VVV core.
* Use a new global composer configuration.
* Add `zip` as a package during provisioning.
* Introduce a helpful caveats section.
* Remove `tcp_nodelay` config in Nginx. Reasoning in 0cce79501.

## 1.0
* **Introduce** [Auto Site Setup](https://github.com/varying-vagrant-vagrants/VVV/wiki/Auto-site-Setup) during provisioning to allow for easy new project configuration.
* **Happy Fix** `vagrant up` after halt meets expectations and no longer requires provisioning to be reapplied.
* Begin implementing best practices from Google's [shell style guide](http://google-styleguide.googlecode.com/svn/trunk/shell.xml) in our provisioning scripts.
* Databases can now be dropped in phpMyAdmin. Pro-tip, `drop database wordpress_develop` in phpMyAdmin followed by `vagrant provision` clears your src.wordpress-develop.dev for reinstall.
* Copy config files instead of linking them. This allows for a nicer `vagrant up` after a `vagrant halt` and treats provisioning more like it should be treated. See [1fbf329](https://github.com/varying-vagrant-vagrants/VVV/commit/1fbf32926e69b852d912047da1bfa7c302693b82) for a more detailed commit message.
* Allow for `dashboard-custom.php` to override the default dashboard provided by VVV
* Reduce size of the included `my.cnf` file to exclude unrequired changes. Increase `max_allowed_packet` setting.

## 0.9
* **Possible Annoying:** Use `precise32` for the Vagrant box name for better cross project box caching.
    * **Note:** This will probably cause a new Vagrant box to download. Use `vagrant box remove std-precise32` after a `vagrant destroy` to remove the old one and start with this.
* **Possible Breaking:** Change VM hostname to `vvv.dev`
    * **Note:** If you had anything setup to rely on the hostname of precise32-dev, this may break.
* **Possible Breaking:** Change MySQL root password to `root`
	* **Note:** If anything is setup to rely on the previous password of `blank`, this  may break.
	* You can also now access `mysql -u root` without a password.
* **Introduce** support for the WordPress develop.svn
	* This was added pretty much the day it was available. Such a pleasure to work with!
	* Allowed us to remove the old `wordpress-unit-tests` in favor of the new `wordpress-develop/tests`
* **Introduce** support for the Vagrant hostsupdater plugin
	* Use `vagrant plugin install vagrant-hostsupdater` to install.
	* Very, very much recommended for an easier and happier life.
* **Introduce** Postfix with a default config. Mail works! (But check your spam)
* **Introduce** the WordPress i18n Tools, including `config/homebin/makepot`
* **Introduce** PHP_CodeSniffer, WordPress-Coding-Standards, and Webgrind
* **Remove** entire well intended but not so useful flags system
* Rather than include PHPMemcachedadmin in the VVV repository, download it on initial provision
* Verify support for Vagrant 1.3.5 (as well as 1.2.x) and VirtualBox 4.3 (as well as 4.2.x)
* Move `xdebug_on` and `xdebug_off` controls to executable files in `config/homebin`
* Generate `vagrant_dir` in `Vagrantfile` for accessing relative file locations
* Add a basic network connectivity check by pinging Google DNS servers
* Update stable version of WordPress automatically on provision
* General cleanup to screen output during provisioning
* Many updates to the default nginx configuration
* Remove poor, unused implementation of Watchr
* Provide default certs for SSL in Nginx

## 0.8
* Enable SSH agent forwarding
* Wrap update/installation procedures with a network status check
* Enable WP_DEBUG by default
* Update wp-cli during provisioning
* Better handling of package status checks
* Better handling of custom apt sources
* Add PHPMemcachedAdmin 1.2.2 to repository for memcached stats viewing.
* Add phpMyAdmin 4.0.3 to repository for database management

## 0.7

**BREAKING CHANGES**: Breaking changes are made in this release due to the reorganization of config files for PHP that will require a full `vagrant destroy` and `vagrant up` to resolve.

* Refactor of package provisioning allows for better (and incremental) `vagrant provision` uses by checking individual package installs before attempting to install them again.
* Remove several flags used to disable portions of provisioning. This favors the scaffold approach provided by VVV.
* Improved nginx configuration and documentation
* Use --asume-yes vs --force-yes with apt
* Update Composer based on a specific revision rather than always checking for an update.
* Update Mockery based on a specific version rather than using the dev channel.
* Update [ack-grep](http://beyondgrep.com) to 2.04
* Add php5-imap package
* Update to Nginx 1.4 sources
* Update to PHP 5.4 sources
* Update to Git 1.8 sources
* Updated xdebug configuration parameters, fixes 60s timeout issue
* Better method to enable/disable xdebug configuration
* Refactor handling of custom PHP, APC, and xdebug configurations
* Bump default memcached memory allocation to 128M
* Introduce custom `apc.ini` file, bump `apc.shm_size` to 128M
* Provide a phpinfo URL at `http://192.168.50.4/phpinfo/`
* Set WP_DEBUG to true by default for included installations of WordPress

## 0.6
* Add [WordPress Unit Tests](http://unit-tests.svn.wordpress.org/trunk/)
* Option for custom shell provisioning file
* Pre/Post provisioning hooks via additional shell scripts
* Flags system to disable portions of default provisioning
* Grab stable WordPress from latest.tar.gz vs SVN
* Append custom apt sources list to default
* Update to SVN 1.7.9, addresses specific Windows permissions issue
* Move [wp-cli](https://github.com/wp-cli/wp-cli) to /srv/www/ for easier contributions

## 0.5
* Repository moved under [10up organization](http://github.com/10up/varying-vagrant-vagrants)
* Wrap provisioning in an initial run flag, speed up subsequent boots
* Add support for a Customfile to pull in desired local modifications

## 0.4
* Add default .vimrc file with some helpful tricks
* Clarify sample SQL commands
* Add WordPress trunk installation to default setup
* Use composer to install phpunit, mockery and xdebug - faster than PEAR
* Filename modifications for config files
* General documentation improvements

## 0.3
* Add Mockery
* Vagrant version requirement changes
* Add wp-cli
* Use wp-cli to setup default WordPress installation
* Add subversion

## 0.2.1
* Bug fix on importing SQL files

## 0.2
* Add ack-grep
* Move to Vagrant 1.1 style Vagrantfile
* Better DB handling all around
* Link mysql data directories for persistence
* Add PHPUnit
* Add XDebug

## 0.1
* Initial version, lots of junk from untracked versions. :)
