---
layout: page
title: Changelog
permalink: /docs/en-US/changelog/
---

# Changelog

## 3.10 ( 2022 TBD )

### Enhancements

* Improved provisioning output
* VVV will now attempt to test Nginx configs on installation and recover ( #2604 )
* Switched to new launchpad PPA domains with HTTPS ( #2586 )
* Improved the verboseness of the DB import scripts ( #2621 )

### Bug Fixes

* WP CLI package update failures now fail gracefully instead of stopping a provision ( #2601 )
* Fixed an edge case updating NVM via git ( #2604 )
* Disable hardware support for gcrypt to avoid bad VirtualBox implementations ( #2609 )
* Fix unbound variable in `db_backup` ( #2617 )
* Ensured npm and nvm are always available in site provisioners
* Explicitly allow a composer plugin when installing PHPCS to avoid issues in July 2022 ( #2620 )

## 3.9.1 ( 2022 April 13th )

### Enhancements

* VVV now switches to Parallels by default on Arm machines ( #2560 )
* Adds default Nginx pages for 40x and 50x errors to help on troubleshooting ( #2345 )
* NVM is now used to manage NodeJS, VVV will auto-switch the node version to that used by `.nvmrc` when inside the guest VM ( #2581 )
* The PHP Redis extension is now installed with the default PHP version ( #2582 )

### Bug Fixes

* Refactored the certificate check to check for the certificate file, not the TLS-CA utility ( #2563 )
* Fixed an issue with `composer create-project` not running when specified in `config.yml` ( #2565 )
* Switched obsolete mirror check for MariaDB to the one already used (#2575)
* Fixed a broken warning in the network checks
* Fixed an issue with `/root/.local/share/composer` when provisioning (#2589)
* Fixed an issue with the new Git release that was crashing the provisioner beucase of wrong user permissions ( #2593 )

## 3.8.1 ( 2021 November 15th )

### Enhancements

* Split tools out into their own provisioner ( #2270 )
* Parallelised the tools provisioner ( #2520 )
* Added the `unattended-upgrades` package to auto-upgrade packages with security updates ( #2513 )
* Add `jq` for CLI based JSON parsing ( #2518 )
* Improved Debian/Raspbian compatibility in apt package provisioners ( #2522 )
* Added basic Avahi support for vvv.local ( #2523 )
* Utiities have been renamed to extensions
* VVV now warns when `vagrant-disksize` is installed on Arm/Apple Silicon devices
* Changed Parallels Arm64 box
* Added db.restore.exclude/include/restore_by_default parameters

### Bug Fixes

* Fixed backwards compatibility for enabling backups in `config.yml` via `backup: true`
* Fixed the import of databases with spaces in there names
* Improved root certificate trust chain handling
* Service restarts now have dedicated functions
* Several evals removed from the hook functions
* Disabled nested virtualisation under Hyper-V
* Clock synchronisation now fails gracefully
* The private IP requested has been changed to fit the restrictions in VirtualBox 6.1.28
* If unset, VVV will now set the global git branch merge strategy to avoid provisioner failure

## 3.7.2 ( 2021 August 3rd )

### Enhancements

* Added a new bear to full provision message, and updated message to be more clear

### Bug Fixes

* Switched Ubuntu 20 boxes to Bento on VirtualBox to avoid folder mounting issues
* Fixed a broken heredoc

## 3.7.1 ( 2021 July 20th )

### Enhancements

* Improved site provisioning messages
* MariaDB upgraded to v10.5
* Improved Apt source file handling in core provisioners
* Upgraded to Composer 2
* Upgraded to Python 3 setuptools and pip3
* PHPCS installation improvements
* Added ARM64 support for Mailhog
* Improved the splash screen provider version fetching
* Added improved apt package upgrade routines
* Provisioners now ask to install only packages that aren't installed
* General package handling performance improvements
* New config to exclude databases from backup in `config.yml` ( #2346 )
* New config to gzip compress database backups in `config.yml` ( #2346 )
* Experimental Apple silicon support using vagrant + parallels
* Disable backup and restore of databases by default
* Updated Mailhog to 1.0.1 for new installs
* Improved MailHog downloading with retries and error output
* Improved Composer installation
* webp support in Imagemagick
* Switch from Ubuntu 18.04 to 20.04 (current LTS release)
* Simplified config folder
* Increased the default PHP memory limit from 128MB to 256MB

### Bug Fixes

* Fixed `vvv_error` not always printing messages
* When a sites repo has the wrong URL for the origin remote, the user is now told. This avoids certain mistakes being made.
* Remote changes are now fetched before resetting, not afterwards.
* Increased the priority of Nodesource and Ondrej packages to avoid issues
* Fixed Parallels mount permissions
* Fixes for site names containing spaces causing Nginx and TLS issues
* Warnings that you're missing vagrant plugins no longer show when running vagrant plugin commands
* Force the Virtual Machine to 64bit on VirtualBox to avoid infinite loops on 32bit architectures
* Force the installation and update of grunt and grunt-cli so that old grunt is always overwritten when updated
* Sync clocks before provisioning if ntpdate is available to avoid Apt mirror time issues
* Fixed cloning the dashboard git repository with unknown remote branches
* Skip mounting custom folders for skipped sites
* Improved WP CLI ownership and permission settings
* Removed WP CLI doctor subcommand package that was causing issues for some users
* Fixed dashboard updating

## 3.6.2 ( 2021 March 17th )

### Bug Fixes

* Replaced PHPCS symlinking to avoid issues with Windows

## 3.6.1 ( 2021 March 16th )

### Important Note

Lots of provisioners now run in strict mode. Your custom site and utility provisioners may fail if they do not handle bad return codes from commands correctly. For example if you ran `composer create-project` on a folder that was not empty, it will fail. In v3.5 this failure was ignored and the script continued despite the critical error, in v3.6 VVV will halt provisioning so that the error can be seen.

Make sure that commands are only ran at their appropriate times, e.g. only install things if they aren't installed, and if you're checking the return value of a command, do it in an if check, not as a temporary variable. If you're feeling adventurous you can unset the strict flags ( danger! ).

Finally, check that your custom modifications haven't been added in the official site templates.

### Enhancements

* Improve the way that PHPCS gets provisioned to avoid conflicts with composer v2 (#2357)
* PHP v7.4 is now the default PHP ( other versions are available on CLI if installed via `php73`, `php72`, etc )
* Beautify the PHP debug switcher script
* Support for basic formatting tags in `vvv_warn` `vvv_error` `vvv_info` and `vvv_success`
* A new `vvv_output` and `vvv_format_output` bash functions
* Minor refactors and colours added to the main provisioner
* Improved output of backup and import scripts
* SHDocs added to core provisioners
* Improved PHP configuration file installation
* Sites can now define composer create-project/install/update commands to run in their folders section in addition to the git options added in v3.5.1
* Adds a `vagrant` command inside the virtual machine to tell users they are still inside the VM and need to exit
* `switch_php_debugmod` now checks if a module is installed and enabled, with improved output to make it clearer which versions of PHP support the module
* Print provision log if there are errors
* Adds an Xdebug Info button to the dashboard when Xdebug is enabled

### Bug Fixes

* Fixed the use of `vvv_warn` `vvv_success` `vvv_error` and `vvv_info` outside of provisioners
* Don't try to install shyaml if it's already installed
* Global composer packages were only updated when composer itself was updated
* Skip the WordPress unit tests database when running backups
* Don't back up databases that have no tables
* Xdebug deprecated configuration option warnings fixed
* Use HTTPS instead of SSH for WP CLI Doctor subcommand installation
* Install missing library for Xdebug support

## 3.5.1 ( 2020 December 11th )

### Enhancements

* Cleaned up leftover `nvm` removal code from main provisioner ( #2185 )
* Added support for `vagrant-goodhosts`, we recommend using this in the future instead of `vagrant-hostsupdater`
* Added `box-cleanup.sh` and `box-minimize.sh` scripts. Run these before creating a vagrant box to reduce disk size. These are only intended for box file creation.
* Prevent use of sudo vagrant up ( #2215 )
* Major refactor of the main provisioner, and introduction of a hook system to be used while provisioning ( #2230, #2238 )
* Support for cloning git repositories into site folders via `config/config.yml` ( #2247 )
* Install WP-CLI doctor package ( #2051 )
* Enhanced database backup terminal output ( #2256 )
* Sites with no `hosts` defined will now default to `{sitename}.test` ( #2267 )
* Enhanced pre/post vagrant up and provision messages ( #2306 )
* More warnings for people who use `sudo vagrant` commands ( do not use sudo ) ( #2306 )
* If the VM was created by a root user, `/vagrant/provisioned_as_root` is now created ( #2306 )
* The default version of PHP was upgraded to v7.3 ( #2307 )
* Only install the specific version of PHP Pcov we need, rather than all versions ( #2310 )

### Deprecations

* SVN repository upgrade searches have been moved to a utility. Previous versions of VVV would search 5 folders deep for svn repositories that needed upgrading. If  you still need this, add the `svn-folder-upgrade` core utility. This change can speed up provisioning by 5-10+ seconds on large installations.
* In the future the dashboard options will be deprecated. Custom dashboards should instead use site provisioners, allowing them to run provisioners, make custom Nginx configs, and have multiple dashboards if desired.
* Some people use `sudo` when running vagrant commands with VVV, ***they should stop, do not use `sudo`***, and immediatley back up their data. Future VVV versions will assume that when running with `sudo` that the user is trying to recover databases in preparation for a `vagrant destroy`. This includes skipping the provisioning of sites, limiting the available features, and very annoying and prominent warnings. Users who intend to continue using `sudo` for everyday development should expect a painful experience if they do not try to recover. Feel free to ask for help in github or the VVV slack.

### Bug Fixes

* Fix mysql root password reset ( #2182 )
* Fix empty string yml value reading on site provisioner ( #2201 )
* Fixed an issue preventing backups of databases whose names contained reserved words ( #2213 )
* Remove APT list files and switch compression type defaults for repositories to avoid hash mismatch ( #2208 )
* In case the previous provisioning had some issues with dpkg on a new provision `dpkg --configure -a` is executed as default ( #2211 )
* Fixed provision-site.sh syntax errors on fail situations ( #2231 )
* Dashboard cloning is now more reliable ( #2243 )

## 3.4.1 ( 2020 June 4th )

### Enhancements

* Improved the log folder names from `20200225-182126` to `2020.02.25-18-21-26` ( #2078 )
* Added a `switch_php_debugmod` to replace the `xdebug_on` `tideways_off` style scripts ( #2084 )
* Checks the default password for MySQL root user during provision ( #2077, #2085 )
* Remove NVM support entirely ( #2088 )
* Improved the provider examples in `default-config.yml` ( #2091 )
* Run rubocop on Vagrantfile in a move towards more idiomatic ruby ( #2093 )
* Improved network checks to test more domains ( #2099 )
* ack-grep is now installed via `apt` rather than `beyondgrep.com` ( #2100 )
* Refactor site provisioners ( #2102 )
* Added new bears to the various vagrant trigger scripts ( #2105, #2108 )
* Removed Ubuntu news MOTD ( #2105 )
* Improve network checks wording ( #2106 )
* Support for vagrant-hostmanager ( #2112 )
* Bumped MariaDB sources from 10.3 to 10.4 ( #2140 )
* Improve compatibility with globally installed gems on the guest ( #2138 )
* Add LFTP tool ( #2137 )
* List relevant log files when provisioners fail ( #2161 )

### Bug Fixes

* Fix check for utility installed that prevented SSL certificates to be generated ( #2073 )
* Fix SSL issue on the base Ubuntu image ( #2074 )
* Don't spider and recurse domains when checking for network connections ( #2103 )
* Always set the database root user password to avoid having the default invalid password on fresh installs ( #2104 )
* Swap the MariaDB apt mirror used for a more reliable source ( partially #2140 and in a217369 )
* Fixed an issue with the dpkg lock file not being cleaned up sometimes ( #2151 )
* Fix issues with the sad bear showing at the end of provisioning despite provisioners being succesful ( #2161 )
* Fix provisioners printing all output to console (not just errors) ( #2174 )

## 3.3.0 ( 2020 Feb 26th )

### Enhancements

* Improvements to the ruby code in the vagrant file

### Bug Fixes

* Installs the ntp date packages and starts the ntp service to fix time drift on sleep
* Fixes an issue with the ntpsec package by removing it
* Fixed the use of dots in site names breaking provisioning

## 3.2.0 ( 2019 Nov 5th )

### Enhancements

* Improved output of `xdebug_on` and `xdebug_off`
* Updated the default config to reference PHP 7.4 support
* webgrind is now provisioned using composer
* Added support for the vagrant-disksize plugin if available
* Site provisioner output is now piped to the log file to simplify the terminal output. Errors should still be sent to the terminal
* Utility provisioner is now piped to the log file to simplify terminal output

### Bug Fixes

* Fixed cloning site provisioners into empty directories
* Enabled MailHog for all PHP versions
* Removed trailing spaces from all provisioner files and configs
* `my.cnf` is now readable by the vagrant user
* Fixes to newline substitution in the splash screen and some rearrangement
* MySQL binary logging is now disabled
* Synced folder permission fixes for VMWare
* Shared DB mounts are now mounted on Parallels and VMWare
* Fixed `/var/log` being mounted on Parallels, HyperV, and VMWare

## 3.1.1 ( 2019 August 6th )

This is a quick update that changes a default parameter when undefined. In VVV 2 the database was stored inside the VM, and in VVV 3 we put it in a shared folder. This didn't work for some people, so we added a config option to disable this. If this option wasn't set, VVV would use the shared folder.

In v3.1.1 if the option isn't set, it will instead store the database inside the VM. This makes it work out of the box for everybody. If you have a working VVV with the shared folder, you can restore this behaviour by setting `db_share_type: true` in `vvv-custom.yml` and reprovisioning, see `vvv-config.yml` for an example of where this setting goes

### Enhancements

* Improved the default `vvv-config.yml` to show new site template parameters
* VVV installs less out of the box with a simplified `vvv-config.yml`, uncomment utilities to add software back in

### Bug Fixes

* Fixes the grunt installation
* Fixes to file permissions in the `/vagrant` folder
* Removed typos in the readme
* Switched to a new box for VMWare
* Shared folder warning fixes for VMWare
* Trailing whitespace removed from the editorconfig file
* Visiting the dashboard before it's provisioned now no longer gives a PHP error

## 3.1.0 ( 2019 July 4th )

This is primarily a reliability update. Note that updating to v3.1 requires a `vagrant destroy` and a `vagrant up --provision`. If you've turned off shared database folders, backup beforehand.

### Enhancements

* The vagrant box can now be overridden using the `box` parameter in `vvv-custom.yml` under the `vm_config` section. This requires a `vagrant destroy` followed by a `vagrant up --provision` to recreate the VM using the new box
* The main provisioner now only fetches the apt keys once rather than on every key check
* The TTY fix shell provisioner and the `/vagrant` setup shell provisioner were merged for a minor reduction in provisioning time.
* Allow `db_backup` script to be run manually regardless if automatic DB backups are disabled
* `vvv`, `vvv.dev`, and `vvv.local` now redirect to `vvv.test`
* Added a premade Sequel Pro config file under the `database` folder
* Set GitHub token from `vvv-custom.yml` for Composer

### Bug Fixes

* Changed to the `ubuntu/bionic64` box to avoid issues with kernel page cache corruption until they can be identified, these were causing issues when updating a WP installation
* Fixes to mysql user and group creation to improve shared folder reliability
* Fixed an issue with permissions in files copied to the home folder
* Fixed shared folder and permissions for Microsoft Hyper-V
* Fixed all mount_options to the correct permissions for Microsoft Hyper-V
* Set VM Name to exactly the same as VirtualBox, using v.vmname for Hyper-V
* Fixes to log file paths for XDebug and PHP
* Fixes files and folders in the home folder being owned by root instead of vagrant
* Fixes support for database names containing hyphens in the import/restore scripts
* Fixes the site provisioner attempting to clone site templates into existing sites when a site template is added to a site that didn't have one before, but has already provisioned ( it will note that this happened but won't clone the template )
* Removed some references to Go
* Fixed symlink issues with apt source files by copying instead
* Specify `keep_colors` on vagrant provisioners to prevent composer from outputting valid messages in the red error colours, unnecessarily alarming users
* `xdebug_on` and `xdebug_off` now toggle Tideways so that XDebug and Tideways are never running at the same time
* Switched to Node v10 by default to fix compatibility issues with the WP Core build scripts
* Runs the npm commands in the main provisioner under the vagrant user
* Node v11 is now auto-downgraded to Node v10
* Fixed Database SSH access from the host by enabling password authentication in `/etc/ssh/sshd_config`
* Added code to remove NVM
* Change Permission folder `/vagrant` from root to vagrant

## 3.0.0 ( 17 May 2019 )

This version moves to an Ubuntu 18.04 box. It also moves the database data directory to a mounted folder. This means you can destroy and rebuild the VM without loss, but it also means **a `vagrant destroy` is necessary to update**. **Be sure to back up database tables you need beforehand**.

If you have issues provisioning with the new shared database folder, you can disable it by adding `db_share_type: false` to the `general:` section of `vvv-custom.yml` then reprovisioning. This will return you to the VVV 2 behaviour.

In the near future, we expect to use a box with PHP/etc preinstalled, this will be VVV 4.0.

### Enhancements

* The box was changed to use Ubuntu 18.04 LTS
* If cloning a git repo to create a new site fails, VVV will halt provisioning and warn the user
* Added tbe `git-svn` package, `git-svn` is used for bi-directional operation between subversion and git
* MongoDB was updated to v4.0
* New `/srv/provision` and `/srv/certificates` shared folders
* Provisioners now log their output to a `logs/provisioners` folder, with each provision having its own subfolder

### Bug Fixes

* Added a VVV package mirror PPA
* Updated apt-get keys for several sources
* Prevented provisioning from occurring inside Ubuntu 14 VMs
* Fixed issues with Nginx restarting too fast and too often by reloading instead
* Fixed the permissions on the `db_restore` script
* The `/var/log` folder is no longer directly mounted, instead the `/var/log/php`, `/var/log/nginx`, `/var/log/provisioners` and `/var/log/memcached` subfolders are mounted. This improves compatibility
* The SQL import script for backups will now create the databases if they don't exist before importing

### Removals

* The deprecated domains `vvv.dev`, `vvv.local`, and `vvv.localhost`, were removed, the dashboard lives at `vvv.test`.
* Removed the `/vagrant` default shared folder

## 2.6.0 ( 2nd April 2019 )

### Enhancements

* Auto download plugin for vagrant, supported vagrant 2.2.0+
* Autoset the locale inside the virtual machine to avoid errors in the console
* Added a `vagrant_provision` and `vagrant_provision_custom` script to the homebin folder that run post-provision
* Improved the messaging to tell the user at the end of a `vagrant up` or `vagrant provision` that it was successful
* Added friendly splashes at the end of vagrant up and provision to make it obvious to end users when they've finished
* The VVV install path is now in the splash screen, making it easier to debug GH issues
* Added a `wordcamp_contributor_day_box` flag to the `vm_config` section of `vvv-config.yml` so that contributor day setup scripts are simpler

### Bug Fixes

* Improved detection of VirtualBox path to avoid `???` version numbers in the VVV splash

## 2.5.1 ( 14th January 2019 )

2.5 Brings a major bug fix, and some performance improvements to provisioning

### Enhancements

* Updated PHPMemcachedadmin from v1.2.2.1 to v1.2.3
* A new `db_backup` option was added to `vvv-custom.yml`
* A new `db_restore` option was added to skip the initial import
* MailHog is now installed from a prebuilt binary instead of being built from source, speeding up initial provision
* VVV will now explicitly check for vvv-hosts in the .vvv and provision subfolders and skip searching 3 folders down if they're found
* Additional warnings and messages were added to aid with debugging site provisioners
* VVV will warn the user if no hosts are defined for a site, or if no folder exists for a site
* Skipping provisioning on a site will now make the site provisioner abort earlier
* Site provisioners no longer need to use nginx template config files to add TLS keys, they can use `{vvv_tls_cert}` and `{vvv_tls_key}` in `vvv-nginx.conf`
* `tideways.vvv.test` is now registered if the experimental tideways xhgui utility is present

### Deprecations

* Loading vvv-hosts is now skipped if hosts are defined in the VVV configuration file
* GoLang was removed from the provisioner

### Bug Fixes

* Updated the GPG key for packagecloud.io
* Updated the site provisioning script to fix WordPress Meta Environment failure (WordPress/meta-environment#122)
* Continue if the vagrant up and reload triggers failed
* Nginx and MySQL restarting is no longer done via a provisioner, this fixes contributor day issues when using `--no-provision` leading to nginx and mysql being unavailable. This is done via the `config/homebin/vagrant_up` script

## 2.4.0 ( 2018 October 2th )

### Enhancements

* Updated Node v6 to Node v10
* The default site config has been improved to clear up confusion over the difference between the site template and the develop site template
* Utilities can now place nginx config files in `/etc/nginx/custom-utilities/` during provisioning
* The default Nginx config can now be extended with files in `/etc/nginx/dashboard-extensions/` during provisioning
* The message VVV showed when copying `vvv-config.yml` to `vvv-custom.yml` was a tad confusing, it's been improved

### Bug Fixes

* Sites that set `skip_provision` to true no longer have their hosts added
* PHP error logging was switched from `/srv/log` to `/var/log`, fixing an issue with PHP logs appearing inside Nginx logs

## 2.3.0 ( 2018 September )

### Enhancements

* Support for git-lfs
* Replaced MailCatcher with MailHog
* Network tests now use Launchpad instead of Google.com
* Improved Splash screen and warning messages
* Improved the default vvv config to prevent confusion
* Improved the default prompt when using `vagrant ssh`
* Improved the welcome message when you SSH in
* If provisioning fails, VVV now aborts instead of continuing and failing
* Apt-get keys are now bundled with the VM

### Bug Fixes

* VVV will now warn you when you add a site without a site template
* Fixed issues wrapping bash prompt colours on some environments
* Fixed an issue with dpkg failures
* The logs folder is now owned by the vagrant user not the ubuntu user

### Deprecations

* VVV will now search 3 folders down for vvv-init.sh vvv-hosts and vvv-nginx.conf not 4 folders
* Ruby was replaced with GoLang, and MailCatcher removed for new users

## 2.2.1 (May, 2018)

Note that to update to 2.2.1, you must remove the Vagrant triggers plugin and install Vagrant 2.1

### Enhancements

* Support for Vagrant 2.1, note that older versions of Vagrant and Vagrant Triggers are now deprecated
* PHP 7.2 is now the default PHP version
* Added the TLS CA authority, making HTTPS TLS/SSL connections to VVV sites easier, see [our docs on how to set this up](https://varyingvagrantvagrants.org/docs/en-US/references/https/)
* The VVV terminal splash is now smaller, with better support for lighter colour schemes.
* The dashboard is now a separate git repo cloned on provision, that can be overridden in `vvv-custom.yml`
* PHPCompatibility PHPCS standards are now installed
* VVV now has a `version` file
* Private network IP can now be changed via `vvv-custom.yml`, see [#1407](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1407)
* Default VM RAM bumped up to `2048` from `1024`, [see #1370](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1370)
* The `src` subdomain of the WP develop site was disabled in line with changes to WP core
* `php70` added to the core utility

### Bug Fixes

* Fixed the unexpected `-f` error on Windows
* Fixed the splash not reporting git vs zip and branch on Windows
* Fixes to PHPCS installation
* Updated the box used for VMWare [see #1406](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1406)
* When cloning utilities git ran as the root user [see #1491](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1491)
* Composer ran under the root users [see #1489](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1489)
* When cloning sites, git ran as the root user [see #1490](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1490)

### Deprecations

* `vvv-wordpress-develop` has been replaced by `custom-site-template-develop`
* `vvv-wordpress` has been replaced by `custom-site-template`
* Legacy TLS certificate generation for vvv.test was removed, it was broken, use the TLS-CA utility instead
* PHP 7.0 is no longer the default PHP version used, and has been replaced with PHP 7.2, `php70` is available in the core utility [see #1484](https://github.com/Varying-Vagrant-Vagrants/VVV/pull/1484)
* Older versions of Vagrant are no longer supported, Vagrant 2.1+ is now required

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

Please see the [migration documentation](https://varyingvagrantvagrants.org/docs/en-US/adding-a-new-site/migrating-from-vvv-1-4-x/) for tips on how to manage this process.

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
* Vagrant: Allow for a custom trigger, `vagrant_up` to fire during `vagrant up` and `vagrant reload`. See g[#778](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/778).
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
* With a new operating system comes a new RSA key. If you are connecting via SSH through an application that relies on your machines `known_hosts` file, you will need to clear the old key for 192.168.56.4. [See #365](https://github.com/Varying-Vagrant-Vagrants/VVV/issues/365)
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
* Provide a phpinfo URL at `http://vvv.test/phpinfo/`
* Set `WP_DEBUG` to true by default for included installations of WordPress

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
* Use composer to install phpunit, mockery and xdebug * faster than PEAR
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
