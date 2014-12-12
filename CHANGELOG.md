# Varying Vagrant Vagrants Changelog

## 1.2-working
* VVV is now [MIT Licensed](https://github.com/Varying-Vagrant-Vagrants/VVV/blob/master/LICENSE).
* ***Possible Breaking:*** By default, database files are no longer mapped to a local location.
	* A full `vagrant destroy` and the removal of MySQL data from `{vvv-dir}/database/data/` is recommended.
	* If database files already exist from an earlier vesion of VVV, data will continue to be mapped locally until removed.
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
