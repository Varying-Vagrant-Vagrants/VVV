# Varying Vagrant Vagrants

Varying Vagrant Vagrants is an open source [Vagrant](http://vagrantup.com) configuration focused on [WordPress](http://wordpress.org) development. VVV is [MIT Licensed](https://github.com/varying-vagrant-vagrants/vvv/blob/master/LICENSE).

VVV is a [10up](http://10up.com) creation and [transitioned](http://10up.com/blog/varying-vagrant-vagrants-future/) to a community organization in 2014.

* **Version**: 1.x.x-working
* **Latest Stable**: [1.2.0](https://github.com/varying-vagrant-vagrants/vvv/tree/1.2.0)
* **Web**: [http://varyingvagrantvagrants.org/](http://varyingvagrantvagrants.org/)
* **Contributors**: [@jeremyfelt](https://github.com/jeremyfelt), [@carldanley](https://github.com/carldanley), [@ericmann](https://github.com/ericmann), [@lkwdwrd](https://github.com/lkwdwrd), [@TheLastCicada](https://github.com/TheLastCicada), [@tddewey](https://github.com/tddewey), [@johnpbloch](https://github.com/johnpbloch), [@kadamwhite](https://github.com/kadamwhite), [@scribu](https://github.com/scribu), [@danielbachhuber](https://github.com/danielbachhuber), [@tollmanz](https://github.com/tollmanz), [@mbijon](https://github.com/mbijon), [@markjaquith](https://github.com/markjaquith), [@curtismchale](https://github.com/curtismchale), [@Mamaduka](https://github.com/mamaduka), [@lgedeon](https://github.com/lgedeon), [@pmgarman](https://github.com/pmgarman), [@westonruter](https://github.com/westonruter), [@petemall](https://github.com/petemall), [@cmmarslender](https://github.com/cmmarslender), [@mintindeed](https://github.com/mintindeed), [@mboynes](https://github.com/mboynes), [@aaronjorbin](https://github.com/aaronjorbin), [@tobiasbg](https://github.com/tobiasbg), [@simonwheatley](https://github.com/simonwheatley), [@ocean90](https://github.com/ocean90), [@lvnilesh](https://github.com/lvnilesh), [@alexw23](https://github.com/alexw23), [@zamoose](https://github.com/zamoose), [@leewillis77](https://github.com/leewillis77), [@imichaeli](https://github.com/imichaeli), [@andrezrv](https://github.com/andrezrv), [@cadwell](https://github.com/cadwell), [@cfoellmann](https://github.com/cfoellmann), [@westi](https://github.com/westi), [@ryanduff](https://github.com/ryanduff), [@selinerdominik](https://github.com/selinerdominik), [@ericandrewlewis](https://github.com/ericandrewlewis), [@vDevices](https://github.com/vDevices), [@sunnyratilal](https://github.com/sunnyratilal), [@enejb](https://github.com/enejb), [@salcode](https://github.com/salcode), [@mattbanks](https://github.com/mattbanks), [@aaroncampbell](https://github.com/aaroncampbell), [@tnorthcutt](https://github.com/tnorthcutt), [@neilpie](https://github.com/neilpie), [@francescolaffi](https://github.com/francescolaffi), [@itsananderson](https://github.com/itsananderson), [@foolswis](https://github.com/foolswis), [@lloydde](https://github.com/lloydde), [@jmbarlow](https://github.com/jmbarlow), [@nacin](https://github.com/nacin), [@thewebists](https://github.com/thewebists), [@iparr](https://github.com/iparr), [@chrishepner](https://github.com/chrishepner), [@miya0001](https://github.com/miya0001), [@iamntz](https://github.com/iamntz), [@mirmillo](https://github.com/mirmillo), [@garyjones](https://github.com/garyjones), [@teraphy](https://github.com/teraphy), [@DrewAPicture](https://github.com/DrewAPicture), [@jjeaton](https://github.com/jjeaton), [@ntwb](https://github.com/ntwb), [@bradp](https://github.com/bradp), [@jb510](https://github.com/jb510)

* **Contributing**: Contributions are more than welcome. Please see our current [contributing guidelines](https://github.com/Varying-Vagrant-Vagrants/VVV/blob/master/CONTRIBUTING.md). Thanks!

## Overview

### The Purpose of Varying Vagrant Vagrants

The primary goal of Varying Vagrant Vagrants (VVV) is to provide an approachable development environment that matches a typical production environment.

The default server configuration provisioned by VVV matches a common configuration for working with high traffic WordPress sites.

The default WordPress configurations provided by VVV create an environment ideal for developing themes and plugins as well as for [contributing to WordPress core](http://make.wordpress.org/core/).

### How to Use Varying Vagrant Vagrants

#### Software Requirements

VVV requires recent versions of both Vagrant and VirtualBox to be installed.

[Vagrant](http://www.vagrantup.com) is a "tool for building and distributing development environments". It works with [virtualization](http://en.wikipedia.org/wiki/X86_virtualization) software such as [VirtualBox](https://www.virtualbox.org/) to provide a virtual machine sandboxed from your local environment.

#### VVV as a MAMP/XAMPP Replacement

Once Vagrant and VirtualBox are installed, download or clone VVV and type `vagrant up` to automatically build a virtualized Ubuntu server on your computer containing everything needed to develop a WordPress theme or plugin. See our section on [The First Vagrant Up](#the-first-vagrant-up) for detailed instructions.

Multiple projects can be developed at once in the same environment.

* Use `wp-content/themes` in either the `www/wordpress-default` or `www/wordpress-trunk` directories to develop themes.
* Use `wp-content/plugins` in either the `www/wordpress-default` or `www/wordpress-trunk` directories to develop plugins.
* Take advantage of VVV's [auto site configuration](https://github.com/varying-vagrant-vagrants/vvv/wiki/Auto-site-Setup) to provision additional instances of WordPress in `www/`.
* Use the `www/wordpress-develop` directory to participate in [WordPress core](http://core.trac.wordpress.org) development.

VVV's `config`, `database`, `log` and `www` directories are shared with the virtualized server.

These shared directories allow you to work, for example, in `vagrant-local/www/wordpress-default` in your local file system and have those changes immediately reflected in the virtualized server's file system and http://local.wordpress.dev/. Likewise, if you `vagrant ssh` and make modifications to the files in `/srv/www/`, you'll immediately see those changes in your local file system.

#### VVV as a Scaffold

Entirely different server configurations can be created by modifying the files included with VVV and through the use of additional [Auto Site Setup](https://github.com/varying-vagrant-vagrants/vvv/wiki/Auto-site-Setup) provisioning scripts. Check this project out and use it as a base to learn about server provisioning or change everything to make it your own.

### The First Vagrant Up

1. Start with any local operating system such as Mac OS X, Linux, or Windows.
1. Install [VirtualBox 4.3.x](https://www.virtualbox.org/wiki/Downloads)
1. Install [Vagrant 1.6.x](http://www.vagrantup.com/downloads.html)
    * `vagrant` will now be available as a command in your terminal, try it out.
    * ***Note:*** If Vagrant is already installed, use `vagrant -v` to check the version. You may want to consider upgrading if a much older version is in use.
1. Install the [vagrant-hostsupdater](https://github.com/cogitatio/vagrant-hostsupdater) plugin with `vagrant plugin install vagrant-hostsupdater`
    * Note: This step is not a requirement, though it does make the process of starting up a virtual machine nicer by automating the entries needed in your local machine's `hosts` file to access the provisioned VVV domains in your browser.
    * If you choose not to install this plugin, a manual entry should be added to your local `hosts` file that looks like this: `192.168.50.4  vvv.dev local.wordpress.dev local.wordpress-trunk.dev src.wordpress-develop.dev build.wordpress-develop.dev`
1. Install the [vagrant-triggers](https://github.com/emyl/vagrant-triggers) plugin with `vagrant plugin install vagrant-triggers`
    * Note: This step is not a requirement. When installed, it allows for various scripts to fire when issuing commands such as `vagrant halt` and `vagrant destroy`.
    * By default, if vagrant-triggers is installed, a `db_backup` script will run on halt, suspend, and destroy that backs up each database to a `dbname.sql` file in the `{vvv}/database/backups/` directory. These will then be imported automatically if starting from scratch. Custom scripts can be added to override this default behavior.
    * If vagrant-triggers is not installed, VVV will not provide automated database backups.
1. Clone or extract the Varying Vagrant Vagrants project into a local directory
    * `git clone git://github.com/Varying-Vagrant-Vagrants/VVV.git vagrant-local`
    * OR download and extract the repository master [zip file](https://github.com/varying-vagrant-vagrants/vvv/archive/master.zip) to a `vagrant-local` directory on your computer.
    * OR download and extract a [stable release](https://github.com/varying-vagrant-vagrants/vvv/releases) zip file if you'd like some extra comfort.
1. In a command prompt, change into the new directory with `cd vagrant-local`
1. Start the Vagrant environment with `vagrant up`
    * Be patient as the magic happens. This could take a while on the first run as your local machine downloads the required files.
    * Watch as the script ends, as an administrator or `su` ***password may be required*** to properly modify the hosts file on your local machine.
1. Visit any of the following default sites in your browser:
    * [http://local.wordpress.dev/](http://local.wordpress.dev/) for WordPress stable
    * [http://local.wordpress-trunk.dev/](http://local.wordpress-trunk.dev/) for WordPress trunk
    * [http://src.wordpress-develop.dev/](http://src.wordpress-develop.dev/) for trunk WordPress development files
    * [http://build.wordpress-develop.dev/](http://build.wordpress-develop.dev/) for the version of those development files built with Grunt
    * [http://vvv.dev/](http://vvv.dev/) for a default dashboard containing several useful tools

Fancy, yeah?

### What Did That Do?

The first time you run `vagrant up`, a packaged box containing a basic virtual machine is downloaded to your local machine and cached for future use. The file used by Varying Vagrant Vagrants contains an installation of Ubuntu 14.04 and is about 332MB.

After this box is downloaded, it begins to boot as a sandboxed virtual machine using VirtualBox. Once booted, it runs the provisioning script included with VVV. This initiates the download and installation of around 100MB of packages on the new virtual machine.

The time for all of this to happen depends a lot on the speed of your Internet connection. If you are on a fast cable connection, it will likely only take several minutes.

On future runs of `vagrant up`, the packaged box will be cached on your local machine and Vagrant will only need to apply the requested provisioning.

* ***Preferred:*** If the virtual machine has been powered off with `vagrant halt`, `vagrant up` will quickly power on the machine without provisioning.
* ***Rare:*** If you would like to reapply the provisioning scripts with `vagrant up --provision` or `vagrant provision`, some time will be taken to check for updates and packages that have not been installed.
* ***Very Rare:*** If the virtual machine has been destroyed with `vagrant destroy`, it will need to download the full 100MB of package data on the next `vagrant up`.

### Now What?

Now that you're up and running, start poking around and modifying things.

1. Access the server via the command line with `vagrant ssh` from your `vagrant-local` directory. You can do almost anything you would do with a standard Ubuntu installation on a full server.
    * **MS Windows users:** An SSH client is generally not distributed with Windows PCs by default. However, a terminal emulator such as [PuTTY](http://www.chiark.greenend.org.uk/~sgtatham/putty/download.html) will provide access immediately. For detailed instructions on connecting with PuTTY, consult the [VVV Wiki](https://github.com/Varying-Vagrant-Vagrants/VVV/wiki/Connect-to-Your-Vagrant-Virtual-Machine-with-PuTTY).
1. Power off the box with `vagrant halt` and turn it back on with `vagrant up`.
1. Suspend the box's state in memory with `vagrant suspend` and bring it right back with `vagrant resume`.
1. Reapply provisioning to a running box with `vagrant provision`.
1. Destroy the box with `vagrant destroy`. Files added in the `www` directory will persist on the next `vagrant up`.
1. Start modifying and adding local files to fit your needs. Take a look at [Auto Site Setup](https://github.com/varying-vagrant-vagrants/vvv/wiki/Auto-site-Setup) for tips on adding new projects.

#### Caveats

The network configuration picks an IP of 192.168.50.4. It could cause conflicts on your existing network if you *are* on a 192.168.50.x subnet already. You can configure any IP address in the `Vagrantfile` and it will be used on the next `vagrant up`

VVV relies on the stability of both Vagrant and Virtualbox. These caveats are common to Vagrant environments and are worth noting:
* If the directory VVV is inside of is moved once provisioned (`vagrant-local`), it may break.
    * If `vagrant destroy` is used before moving, this should be fine.
* If Virtualbox is uninstalled, VVV will break.
* If Vagrant is uninstalled, VVV will break.

The default memory allotment for the VVV virtual machine is 1024MB. If you would like to raise or lower this value to better match your system requirements, a [guide to changing memory size](https://github.com/Varying-Vagrant-Vagrants/VVV/wiki/Customising-your-Vagrant's-attributes-and-parameters) is in the wiki.

### Credentials and Such

All database usernames and passwords for WordPress installations included by default are `wp` and `wp`.

All WordPress admin usernames and passwords for WordPress installations included by default are `admin` and `password`.

#### WordPress Stable
* LOCAL PATH: vagrant-local/www/wordpress-default
* VM PATH: /srv/www/wordpress-default
* URL: `http://local.wordpress.dev`
* DB Name: `wordpress_default`

#### WordPress Trunk
* LOCAL PATH: vagrant-local/www/wordpress-trunk
* VM PATH: /srv/www/wordpress-trunk
* URL: `http://local.wordpress-trunk.dev`
* DB Name: `wordpress_trunk`

#### WordPress Develop
* LOCAL PATH: vagrant-local/www/wordpress-develop
* VM PATH: /srv/www/wordpress-develop
* /src URL: `http://src.wordpress-develop.dev`
* /build URL: `http://build.wordpress-develop.dev`
* DB Name: `wordpress_develop`
* DB Name: `wordpress_unit_tests`

#### MySQL Root
* User: `root`
* Pass: `root`
* See: [Connecting to MySQL](https://github.com/varying-vagrant-vagrants/vvv/wiki/Connecting-to-MySQL) from your local machine

### What do you get?

A bunch of stuff!

1. [Ubuntu](http://www.ubuntu.com/) 14.04 LTS (Trusty Tahr)
1. [WordPress Develop](http://develop.svn.wordpress.org/trunk/)
1. [WordPress Stable](http://wordpress.org/)
1. [WordPress Trunk](http://core.svn.wordpress.org/trunk/)
1. [WP-CLI](http://wp-cli.org/)
1. [nginx](http://nginx.org/) 1.6.x
1. [mysql](http://www.mysql.com/) 5.5.x
1. [php-fpm](http://php-fpm.org/) 5.5.x
1. [memcached](http://memcached.org/) 1.4.13
1. PHP [memcache extension](http://pecl.php.net/package/memcache/3.0.8/) 3.0.8
1. PHP [xdebug extension](http://pecl.php.net/package/xdebug/2.2.5/) 2.2.5
1. PHP [imagick extension](http://pecl.php.net/package/imagick/3.1.2/) 3.1.2
1. [PHPUnit](http://pear.phpunit.de/) 4.0.x
1. [ack-grep](http://beyondgrep.com/) 2.04
1. [git](http://git-scm.com/) 1.9.x
1. [subversion](http://subversion.apache.org/) 1.8.x
1. [ngrep](http://ngrep.sourceforge.net/usage.html)
1. [dos2unix](http://dos2unix.sourceforge.net/)
1. [Composer](https://github.com/composer/composer)
1. [phpMemcachedAdmin](https://code.google.com/p/phpmemcacheadmin/)
1. [phpMyAdmin](http://www.phpmyadmin.net/) 4.1.14 (multi-language)
1. [Opcache Status](https://github.com/rlerdorf/opcache-status)
1. [Webgrind](https://github.com/jokkedk/webgrind)
1. [NodeJs](http://nodejs.org/) Current Stable Version
1. [grunt-cli](https://github.com/gruntjs/grunt-cli) Current Stable Version

### Need Help?

* Let us have it! Don't hesitate to open a new issue on GitHub if you run into trouble or have any tips that we need to know.
* The [WordPress and Vagrant Mailing list](https://groups.google.com/forum/#!forum/wordpress-and-vagrant) is a great place to get started for any related topics.
* The [VVV Wiki](https://github.com/varying-vagrant-vagrants/vvv/wiki) also contains documentation that may help.

### Helfpul Extensions

Supporting init scripts during provisioning allows for some great extensions of VVV core.

* [VVV Site Wizard](https://github.com/aliso/vvv-site-wizard) "automates setting up new WordPress sites in Varying Vagrant Vagrants."
* [Variable VVV](https://github.com/bradp/vv) automates setting up new sites, setting up deployments, and more.
* [HHVVVM](https://github.com/johnjamesjacoby/hhvvvm) is an HHVM configuration for VVV.
* The [WordPress Meta Environment](https://github.com/iandunn/wordpress-meta-environment) is a "collection of scripts that provision the official WordPress.org websites into a Varying Vagrant Vagrants installation."

### The Future of Varying Vagrant Vagrants

Immediate goals for VVV include:

* Continue to work towards a stable state of software and configuration included in the default provisioning.
* Provide excellent and clear documentation throughout VVV to aid in both learning and scaffolding.

## Copyright / License

VVV is copyright (c) 2014, the contributors of the VVV project under the [MIT License](https://github.com/varying-vagrant-vagrants/vvv/blog/master/LICENSE).

## History

VVV has come a long way since it was first [launched as Varying Vagrant Vagrants](http://jeremyfelt.com/code/2012/12/11/varying-vagrant-vagrants/) in December of 2012. Initially introduced as an exploration of workflow for immediate project needs at [10up](http://10up.com), VVV caught speed quickly as more and more of the team was introduced. During an internal [10up developer summit](http://10up.com/blog/10up-2013-developer-summit/) in March of 2013, Vagrant as a tool was a highlight and more developers made the conversion.

In April of 2013, we made a [call to the WordPress community](http://jeremyfelt.com/code/2013/04/08/hi-wordpress-meet-vagrant/) to try to encourage the addition of Vagrant to everyday life. These efforts continued with talks at [WordCamp Chicago](http://wordpress.tv/2013/12/31/jeremy-felt-hi-wordpress-meet-vagrant-2/), [WordCamp Vancouver](http://wordpress.tv/2013/10/19/jeremy-felt-hi-wordpress-meet-vagrant/), and WordCamp Denver.

In January of 2014, [10up](http://10up.com) made the decision to [spin VVV off](http://10up.com/blog/varying-vagrant-vagrants-future/) into its own organization to better align with the community that has grown around the project over time. This transition opens doors for what [Varying Vagrant Vagrants, the organization](http://jeremyfelt.com/code/2014/01/27/varying-vagrant-vagrants-organization/) can accomplish as an ongoing project.
