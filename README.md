# Varying Vagrant Vagrants

Varying Vagrant Vagrants is an evolving [Vagrant](http://vagrantup.com) configuration focused on [WordPress](http://wordpress.org) development.

* **Version**: 1.0-working
* **Latest Stable**: [v0.9](https://github.com/10up/varying-vagrant-vagrants/tree/v0.9)
* **Contributors**: [@jeremyfelt](http://github.com/jeremyfelt), [@carldanley](http://github.com/carldanley), [@ericmann](http://github.com/ericmann), [@lkwdwrd](http://github.com/lkwdwrd), [@TheLastCicada](http://github.com/TheLastCicada), [@tddewey](http://github.com/tddewey), [@johnpbloch](http://github.com/johnpbloch), [@kadamwhite](http://github.com/kadamwhite), [@scribu](http://github.com/scribu), [@danielbachhuber](http://github.com/danielbachhuber), [@tollmanz](http://github.com/tollmanz), [@mbijon](http://github.com/mbijon), [@markjaquith](http://github.com/markjaquith), [@curtismchale](http://github.com/curtismchale), [@Mamaduka](http://github.com/mamaduka), [@lgedeon](http://github.com/lgedeon), [@pmgarman](http://github.com/pmgarman), [@westonruter](http://github.com/westonruter), [@petemall](http://github.com/petemall), [@cmmarslender](http://github.com/cmmarslender), [@mintindeed](http://github.com/mintindeed), [@mboynes](http://github.com/mboynes), [@aaronjorbin](http://github.com/aaronjorbin), [@tobiasbg](http://github.com/tobiasbg), [@simonwheatley](http://github.com/simonwheatley), [@ocean90](http://github.com/ocean90), [@lvnilesh](http://github.com/lvnilesh), [@alexw23](http://github.com/alexw23), [@zamoose](https://github.com/zamoose), [@leewillis77](https://github.com/leewillis77), [@imichaeli](https://github.com/imichaeli), [@andrezrv](https://github.com/andrezrv), [@cadwell](https://github.com/cadwell), [@cfoellmann](https://github.com/cfoellmann), [@westi](https://github.com/westi), [@ryanduff](https://github.com/ryanduff)
* **Contributing**: Contributions are more than welcome. Please submit pull requests against the [master branch](https://github.com/10up/varying-vagrant-vagrants/). Thanks!

## Overview

### The Purpose of Varying Vagrant Vagrants

The primary goal of Varying Vagrant Vagrants (VVV) is to provide an approachable way for developers to work in an environment that matches a project's production environment as closely as possible.

The default server configuration provisioned by VVV is intended to match what [10up](http://10up.com) finds to be common when working with high traffic WordPress sites.

The default WordPress configurations provided by VVV are intended to create an environment ideal for developing themes and plugins as well as for contributing to WordPress core.

### How to Use Varying Vagrant Vagrants

#### VVV as a MAMP/XAMPP Replacement

The best part is that VVV is ready to use as is. Setup VVV and then type `vagrant up` to automatically build a sandboxed Ubuntu server on your computer containing everything needed to contribute to WordPress core or develop a WordPress theme or plugin.

Multiple projects can be developed at once in the same environment provided by VVV.
* Use `wp-content/themes` in either the `wordpress-default` or `wordpress-trunk` directories to develop multiple themes using the same test content.
* Use `wp-content/plugins` in either the `wordpress-default` or `wordpress-trunk` directories to develop a plugin the same way.
* Install additional instances of WordPress in `/srv/www/` with VVV's implementation of [auto site configuration](https://github.com/10up/varying-vagrant-vagrants/wiki/Auto-site-Setup).
* Use the `wordpress-develop` directory to participate in WordPress core development.

#### VVV as a Scaffold

Entirely different server configurations can be created by modifying the files included with this repository or through the use of additional provisioning scripts, `provision-pre.sh` and `provision-post.sh`.

It is not necessary to track the changes made to the main repository. Feel free to check this project out and then change everything to make it your own.

### The Future of Varying Vagrant Vagrants

Immediate goals for VVV include:

* Continue to work towards a stable state of software and configuration included in the default provisioning.
* Provide excellent and clear documentation throughout VVV to aid in both learning and scaffolding.
* Provide a method for easing the process of adding new sites to the VVV environment.

## Getting Started

### What is Vagrant?

[Vagrant](http://vagrantup.com) is a "tool for building and distributing development environments". It works with [virtualization](http://en.wikipedia.org/wiki/X86_virtualization) software such as [VirtualBox](http://virtualbox.org) to provide a virtual machine that is sandboxed away from your local environment.

### The First Vagrant Up

1. Start with any operating system.
1. Install [VirtualBox 4.2.x](https://www.virtualbox.org/wiki/Download_Old_Builds_4_2) or [VirtualBox 4.3.4](https://www.virtualbox.org/wiki/Downloads)
    * Major portions of VirtualBox were rewritten for 4.3, and it's possible that there are still bugs to be shaken out. VVV is completely compatible with earlier versions of VirtualBox, so 4.2.18 or earlier would be just fine. Do note that Vagrant had specific issues with 4.2.16. Going as far back as 4.2.10 will likely be of no issue.
    * VVV itself leans in the 4.3.x direction in the master branch to stay ahead of the curve.
    * VirtualBox 4.3.2 or higher is recommended for OS X Mavericks. Previous versions had an issue related to the cleanup of old VirtualBox files.
1. Install [Vagrant 1.3.5](http://downloads.vagrantup.com/tags/v1.3.5)
    * `vagrant` will now be available as a command in the terminal, try it out.
    * ***Note:*** If Vagrant is already installed, use `vagrant -v` to check the version. You may want to consider upgrading if an older version is in use.
    * ***Note:*** If VirtualBox 4.3.x is installed, Vagrant 1.3.5 or later is required.
1. Install the [vagrant-hostsupdater](https://github.com/cogitatio/vagrant-hostsupdater) plugin with `vagrant plugin install vagrant-hostsupdater`
    * Note: This step is not a requirement, though it does make the process of starting up a virtual machine nicer by automating the entries needed in your local machine's `hosts` file to access the various VVV domains in your browser.
1. Clone or extract the Varying Vagrant Vagrants project into a local directory
    * `git clone git://github.com/10up/varying-vagrant-vagrants.git vagrant-local`
    * OR download and extract the repository master [zip file](https://github.com/10up/varying-vagrant-vagrants/archive/master.zip)
    * OR grab a [stable release](https://github.com/10up/varying-vagrant-vagrants/releases) if you'd like some extra comfort.
1. Change into the new directory with `cd vagrant-local`
1. Start the Vagrant environment with `vagrant up`
    * Be patient, magic happens. This could take a while, especially on the first run.
1. Add a record to your local machine's hosts file
    * ***Note:*** If you installed [vagrant hostsupdater](https://github.com/cogitatio/vagrant-hostsupdater) in step 4, the host entries are made for you automatically. Watch the provisioning script, as an administrator or `su` ***password may be required*** to properly modify the hosts file on your local machine.
    * A manual entry should look like this: `192.168.50.4  vvv.dev local.wordpress.dev local.wordpress-trunk.dev src.wordpress-develop.dev build.wordpress-develop.dev`
1. Visit any of the following default sites in your browser:
    * [http://local.wordpress.dev/](http://local.wordpress.dev/) for WordPress stable
    * [http://local.wordpress-trunk.dev/](http://local.wordpress-trunk.dev/) for WordPress trunk
    * [http://src.wordpress-develop.dev/](http://src.wordpress-develop.dev/) for trunk WordPress development files
    * [http://build.wordpress-develop.dev/](http://build.wordpress-develop.dev/) for version of those development files built with Grunt
    * [http://vvv.dev/](http://vvv.dev/) for a default dashboard containing several useful tools

Fancy, yeah?

### What Did That Do?

The first time you run `vagrant up`, a packaged box containing a virtual machine is downloaded to your local machine and cached for future use. The file used by Varying Vagrant Vagrants contains an Ubuntu 12.04 installation (Precise release) and is about 280MB.

After this box is downloaded, it begins to boot as a sandboxed virtual machine using VirtualBox. Once booted, it runs the provisioning script included with VVV. This initiates the download and installation of around 100MB of packages on the new virtual machine.

The time for all of this to happen depends a lot on the speed of your Internet connection. If you are on a fast cable connection, it will likely only take several minutes.

On future runs of `vagrant up`, the packaged box will be cached on your local machine and Vagrant will only need to apply provisioning.
* If the virtual machine has been destroyed with `vagrant destroy`, it will need to download the full 100MB of package data.
* If the virtual machine has been powered off with `vagrant halt`, the provisioning script will run but will not need to download anything large.

### Now What?

Now that you're up and running with a default configuration, start poking around and modifying things.

1. Access the server via the command line with `vagrant ssh` from your `vagrant-local` directory. You can do pretty much anything you would do with a standard Ubuntu installation on a full server.
    * If you are on a Windows PC, you may need to install additional software for this to work seamlessly. A terminal program such as [Putty](http://www.chiark.greenend.org.uk/~sgtatham/putty/download.html) will provide access immediately.
1. Destroy the box and start from scratch with `vagrant destroy`
    * As explained before, the initial 280MB box file will be cached on your machine. the next `vagrant up` command will initiate the complete provisioning process again.
1. Power off the box with `vagrant halt` or suspend it with `vagrant suspend`. If you suspend it, you can bring it back quickly with `vagrant resume`, if you halt it, you can bring it back with `vagrant up`.
1. Start modifying and adding local files to fit your needs.
    * The network configuration picks an IP of 192.168.50.4. This works if you are *not* on the 192.168.50.x sub domain, it could cause conflicts on your existing network if you *are* on a 192.168.50.x sub domain already. You can configure any IP address in the `Vagrantfile` and it will be used on the next `vagrant up`
    * If you require any custom SQL commands to run when the virtual machine boots, move `database/init-custom.sql.sample` to `database/init-custom.sql` and edit it to add whichever `CREATE DATABASE` and `GRANT ALL PRIVILEGES` statements you want to run on startup to prepare mysql for SQL imports (see next bullet).
    * Have any SQL files that should be imported in the `database/backups/` directory and named as `db_name.sql`. The `import-sql.sh` script will run automatically when the VM is built and import these databases into the new mysql install as long as the proper databases have already been created via the previous step's SQL.
    * Check out the example nginx configurations in `config/nginx-config/sites` and create any other site specific configs you think should be available on server start. The web directory is `/srv/www/` and default configs are provided for basic WordPress stable, trunk, and develop setups.
    * Once a database is imported on the initial `vagrant up`, it will persist on the local machine in a mapped `database/data` directory.

### Credentials and Such

All database usernames and passwords for WordPress installations included by default are `wp` and `wp`.

All WordPress admin usernames and passwords for WordPress installations included by default are `admin` and `password`.

#### WordPress Stable
* URL: `http://local.wordpress.dev`
* DB Name: `wordpress_default`

#### WordPress Trunk
* URL: `http://local.wordpress-trunk.dev`
* DB Name: `wordpress_trunk`

#### WordPress Develop
* /src URL: `http://src.wordpress-develop.dev`
* /build URL: `http://build.wordpress-develop.dev`
* DB Name: `wordpress_develop`
* DB Name: `wordpress_unit_tests`

#### MySQL Root
* User: `root`
* Pass: `root`
* See: [Connecting to MySQL](https://github.com/10up/varying-vagrant-vagrants/wiki/Connecting-to-MySQL) from your local machine

### What do you get?

A bunch of stuff!

1. [Ubuntu](http://ubuntu.com) 12.04 LTS (Precise Pangolin)
1. [WordPress Develop](http://develop.svn.wordpress.org/trunk/)
1. [WordPress Stable](http://wordpress.org)
1. [WordPress Trunk](http://core.svn.wordpress.org/trunk)
1. [WP-CLI](http://wp-cli.org)
1. [nginx](http://nginx.org) 1.4.3
1. [mysql](http://mysql.com) 5.5.32
1. [php-fpm](http://php-fpm.org) 5.4.20
1. [memcached](http://memcached.org/) 1.4.13
1. PHP [memcache extension](http://pecl.php.net/package/memcache/3.0.8) 3.0.8
1. PHP [xdebug extension](http://pecl.php.net/package/xdebug/2.2.3) 2.2.3
1. PHP [imagick extension](http://pecl.php.net/package/imagick/3.1.0RC2) 3.1.0RC2
1. [xdebug](http://xdebug.org/) 2.2.3
1. [PHPUnit](http://pear.phpunit.de/) 3.7.24
1. [ack-grep](http://beyondgrep.com/) 2.04
1. [git](http://git-scm.com) 1.8.4
1. [subversion](http://subversion.apache.org/) 1.7.9
1. [ngrep](http://ngrep.sourceforge.net/usage.html)
1. [dos2unix](http://dos2unix.sourceforge.net/)
1. [Composer](https://github.com/composer/composer)
1. [phpMemcachedAdmin](https://code.google.com/p/phpmemcacheadmin/) 1.2.2 BETA
1. [phpMyAdmin](http://www.phpmyadmin.net) 4.0.9 (multi-language)
1. [Webgrind](https://github.com/jokkedk/webgrind) 1.1
1. [NodeJs](http://nodejs.org/) Current Stable Version
1. [grunt-cli](https://github.com/gruntjs/grunt-cli) Current Stable Version

### Need Help?
* There is a [Mailing list](https://groups.google.com/forum/#!forum/wordpress-and-vagrant) for any topic related to WordPress and Vagrant that is a great place to get started. 
* The [VVV Wiki](https://github.com/10up/varying-vagrant-vagrants/wiki) also contains documentation that might help you out

### Feedback?

Let us have it! If you have tips that we need to know, open a new issue. Some blog posts have been written documenting the process that may provide insight....

* [Hi WordPress, Meet Vagrant](http://jeremyfelt.com/code/2013/04/08/hi-wordpress-meet-vagrant/)
* [Evolving WordPress Development With Vagrant](http://jeremyfelt.com/code/2013/03/17/evolving-wordpress-development-with-vagrant/)
* [Varying Vagrant Vagrants](http://jeremyfelt.com/code/2012/12/11/varying-vagrant-vagrants/)
* [A WordPress Meetup Introduction to Vagrant](http://jeremyfelt.com/code/2013/02/04/an-wordpress-meetup-introduction-to-vagrant-what-youll-need/)
* [Clear nginx Cache in Vagrant](http://jeremyfelt.com/code/2013/01/08/clear-nginx-cache-in-vagrant/)
