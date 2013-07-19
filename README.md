# Varying Vagrant Vagrants

Varying Vagrant Vagrants is an evolving [Vagrant](http://vagrantup.com) configuration focused on [WordPress](http://wordpress.org) development.

* **Version**: 0.9-working
* **Latest Stable**: [v0.8](https://github.com/10up/varying-vagrant-vagrants/tree/v0.8)
* **Contributors**: [@jeremyfelt](http://github.com/jeremyfelt), [@carldanley](http://github.com/carldanley), [@ericmann](http://github.com/ericmann), [@lkwdwrd](http://github.com/lkwdwrd), [@TheLastCicada](http://github.com/TheLastCicada), [@tddewey](http://github.com/tddewey), [@johnpbloch](http://github.com/johnpbloch), [@kadamwhite](http://github.com/kadamwhite), [@scribu](http://github.com/scribu), [@danielbachhuber](http://github.com/danielbachhuber), [@tollmanz](http://github.com/tollmanz), [@mbijon](http://github.com/mbijon), [@markjaquith](http://github.com/markjaquith), [@curtismchale](http://github.com/curtismchale), [@Mamaduka](http://github.com/mamaduka), [@lgedeon](http://github.com/lgedeon), [@pmgarman](http://github.com/pmgarman), [@westonruter](http://github.com/westonruter)
* **Contributing**: Contributions are more than welcome. Please submit pull requests against the [master branch](https://github.com/10up/varying-vagrant-vagrants/). Thanks!

## Overview

### The Purpose of Varying Vagrant Vagrants

The primary goal of Varying Vagrant Vagrants (VVV) is to provide an approachable way for developers to work in an environment that matches a project's production environment as closely as possible.

The default configuration provided by VVV is intended to match what [10up](http://10up.com) finds to be a common server setup when working with high traffic WordPress sites. 

### How to Use Varying Vagrant Vagrants

#### VVV as a MAMP/XAMPP Replacement

The best part is that VVV is ready to use as is. Clone or download the repository and `vagrant up` to get a sandboxed Ubuntu server on your computer with everything needed to develop a WordPress theme or plugin.

Multiple projects can be developed at once in the same environment provided by VVV.
* Use `wp-content/themes` in either the `wordpress-default` or `wordpress-trunk` directories to develop multiple themes using the same test content.
* Use `wp-content/plugins` in either the `wordpress-default` or `wordpress-trunk` directories to develop a plugin the same way.
* Install additional instances of WordPress in `/srv/www/` and configure a few pieces of VVV accordingly to work with the new installation.

#### VVV as a Scaffold

Entirely different server configurations can be created by modifying the files included with this repository.

The existing configuration can also be extended significantly through the use of additional provisioning scripts, `provision-pre.sh` and `provision-post.sh`.

It is not necessary to track the changes made to the main repository. Feel free to check this project out and then change everything to make it your own.

### The Future of Varying Vagrant Vagrants

Immediate goals for VVV include:

* Continue to work towards a stable state of software and configuration included in the default provisioning.
* Provide excellent and clear documentation throughout VVV to aid in both learning and scaffolding.
* Provide a method for describing WordPress environment requirements at a project level so that developers joining a project can ramp up quickly. This includes code, database, and content files.

## Getting Started

### What is Vagrant?

[Vagrant](http://vagrantup.com) is a "tool for building and distributing development environments". It works with virtualization software such as [VirtualBox](http://virtualbox.org) to provide a virtual machine that is sandboxed away from your local environment.

### The First Vagrant Up

1. Start with any operating system.
1. Install [VirtualBox 4.2.16](https://www.virtualbox.org/wiki/Downloads)
    * VVV (and Vagrant) has been tested with this version. If a newer version appears on the downloads page and you don't feel like being a guinea pig, check out the [older downloads](https://www.virtualbox.org/wiki/Download_Old_Builds_4_2) page and download the 4.2.16 release.
1. Install [Vagrant 1.2.4](http://downloads.vagrantup.com/tags/v1.2.4)
    * `vagrant` will now be available as a command in the terminal, try it out.
1. Clone the Varying Vagrant Vagrants repository into a local directory
    * `git clone git://github.com/10up/varying-vagrant-vagrants.git vagrant-local`
    * OR download and extract the repository master [zip file](https://github.com/10up/varying-vagrant-vagrants/archive/master.zip)
1. Change into the new directory
    * `cd vagrant-local`
1. Start the Vagrant environment
    * `vagrant up` - *omg magic happens*
    * Be patient, this could take a while, especially on the first run.
1. Add a record to your local machine's hosts file
    * `192.168.50.4  local.wordpress.dev local.wordpress-trunk.dev`
    * On -nix systems you can use: (note that location of host file after the >> may vary) `sudo sh -c 'echo "192.168.50.4 local.wordpress.dev local.wordpress-trunk.dev" >>/private/etc/hosts'`
1. Visit `http://local.wordpress.dev/` in your browser for WordPress 3.5.1, `http://local.wordpress-trunk.dev` for WordPress trunk, or `http://192.168.50.4` for the default dashboard.

Fancy, yeah?

### What Did That Do?

The first time you run `vagrant up`, a pre-packaged virtual machine box is downloaded to your local machine and cached for future use. The file used by Varying Vagrant Vagrants contains an Ubuntu 12.04 installation (Precise release) and is about 280MB.

After this box is download, it begins to boot as a sandboxed virtual machine using VirtualBox. When ready, it runs the provisioning script also provided with this repository. This initiates the download and installation of around 88MB of packages on the new virtual machine.

The time for all of this to happen depends a lot on the speed of your Internet connection. If you are on a fast cable connection, it will more than likely only take several minutes.

On future runs of `vagrant up`, the pre-packaged box will already be cached on your machine and Vagrant will only need to deal with provisioning. If the machine has been destroyed with `vagrant destroy`, it will need to download the full 88MB of packages to install. If the vagrant has been powered off with `vagrant halt`, the provisioning script will run but will not need to download anything.

### Now What?

Now that you're up and running with a default configuration, start poking around and modifying things.

1. Access the server with `vagrant ssh` from your `vagrant-local` directory. You can do pretty much anything you would do with a standard Ubuntu installation on a full server.
    * If you are on a Windows PC, you may need to install additional software for this to work seamlessly. A terminal program such as [Putty](www.chiark.greenend.org.uk/~sgtatham/putty/download.html) will provide access immediately.
1. Destroy the box and start from scratch with `vagrant destroy`
    * As explained before, the initial 280MB box file will be cached on your machine. the next `vagrant up` command will initiate the complete provisioning process again.
1. Power off the box with `vagrant halt` or suspend it with `vagrant suspend`. If you suspend it, you can bring it back quickly with `vagrant resume`, if you halt it, you can bring it back with `vagrant up`.
1. Start modifying and adding local files to fit your needs.
    * The network configuration picks an IP of 192.168.50.4. This works if you are *not* on the 192.168.50.x sub domain, it could cause conflicts on your existing network if you *are* on a 192.168.50.x sub domain already. You can configure any IP address in the `Vagrantfile` and it will be used on the next `vagrant up`
    * If you require any custom SQL commands to run when the virtual machine boots, move `database/init-custom.sql.sample` to `database/init-custom.sql` and edit it to add whichever `CREATE DATABASE` and `GRANT ALL PRIVILEGES` statements you want to run on startup to prepare mysql for SQL imports (see next bullet).
    * Have any SQL files that should be imported in the `database/backups/` directory and named as `db_name.sql`. The `import-sql.sh` script will run automatically when the VM is built and import these databases into the new mysql install as long as the proper databases have already been created via the previous step's SQL.
    * Check out the example nginx configurations in `config/nginx-config/sites` and create any other site specific configs you think should be available on server start. The web directory is `/srv/www/` and default configs are provided for basic WordPress 3.5.1 and trunk setups.
    * Once a database is imported on the initial `vagrant up`, it will persist on the local machine a mapped mysql data directory.
    * Other stuff. Familiarize and all that.

### Credentials and Such

#### WordPress Default - Stable Release
* URL: `http://local.wordpress.dev`
* DB Name: `wordpress_default`
* DB User: `wp`
* DB Pass: `wp`
* Admin User: `admin`
* Admin Pass: `password`

#### WordPress Trunk
* URL: `http://local.wordpress-trunk.dev`
* DB Name: `wordpress_trunk`
* DB User: `wp`
* DB Pass: `wp`
* Admin User: `admin`
* Admin Pass: `password`

#### MySQL Root
* [Connecting to MySQL](https://github.com/10up/varying-vagrant-vagrants/wiki/Connecting-to-MySQL) from local
* User: `root`
* Pass: `blank`

#### WordPress Unit Tests
* DB Name: `wordpress_unit_tests`
* DB User: `wp`
* DB Pass: `wp`

### What do you get?

A bunch of stuff!

1. [Ubuntu](http://ubuntu.com) 12.04 LTS (Precise Pangolin)
1. [nginx](http://nginx.org) 1.4.1
1. [mysql](http://mysql.com) 5.5.31
1. [php-fpm](http://php-fpm.org) 5.4.15
1. [memcached](http://memcached.org/) 1.4.13
1. PHP [memcache extension](http://pecl.php.net/package/memcache/3.0.6) 3.0.6
1. [xdebug](http://xdebug.org/) 2.2.1
1. [PHPUnit](http://pear.phpunit.de/) 3.7.21
1. [ack-grep](http://beyondgrep.com/) 2.04
1. [git](http://git-scm.com) 1.8.3
1. [subversion](http://subversion.apache.org/) 1.7.9
1. [ngrep](http://ngrep.sourceforge.net/usage.html)
1. [dos2unix](http://dos2unix.sourceforge.net/)
1. [WordPress 3.5.2](http://wordpress.org)
1. [WordPress trunk](http://core.svn.wordpress.org/trunk)
1. [WP-CLI](http://wp-cli.org)
1. [WordPress Unit Tests](http://make.wordpress.org/core/handbook/automated-testing/)
1. [Composer](https://github.com/composer/composer)
1. [phpMemcachedAdmin](https://code.google.com/p/phpmemcacheadmin/) 1.2.2 BETA
1. [phpMyAdmin](http://www.phpmyadmin.net) 4.0.3

### Feedback?

Let us have it! If you have tips that we need to know, open a new issue or find us in [other ways](http://10up.com). Some blog posts have been written documenting the process that may provide insight....

* [Hi WordPress, Meet Vagrant](http://jeremyfelt.com/code/2013/04/08/hi-wordpress-meet-vagrant/)
* [Evolving WordPress Development With Vagrant](http://jeremyfelt.com/code/2013/03/17/evolving-wordpress-development-with-vagrant/)
* [Varying Vagrant Vagrants](http://jeremyfelt.com/code/2012/12/11/varying-vagrant-vagrants/)
* [A WordPress Meetup Introduction to Vagrant](http://jeremyfelt.com/code/2013/02/04/an-wordpress-meetup-introduction-to-vagrant-what-youll-need/)
* [Clear nginx Cache in Vagrant](http://jeremyfelt.com/code/2013/01/08/clear-nginx-cache-in-vagrant/)
