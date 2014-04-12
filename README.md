# Varying Vagrant Vagrants

Varying Vagrant Vagrants is an evolving [Vagrant](http://vagrantup.com) configuration focused on [WordPress](http://wordpress.org) development.

VVV is a [10up](http://10up.com) creation and [transitioned](http://10up.com/blog/varying-vagrant-vagrants-future/) to a community organization in 2014.

* **Version**: 1.2-working
* **Latest Stable**: [v1.1](https://github.com/varying-vagrant-vagrants/vvv/tree/v1.1)
* **Contributors**: [@jeremyfelt](https://github.com/jeremyfelt), [@carldanley](https://github.com/carldanley), [@ericmann](https://github.com/ericmann), [@lkwdwrd](https://github.com/lkwdwrd), [@TheLastCicada](https://github.com/TheLastCicada), [@tddewey](https://github.com/tddewey), [@johnpbloch](https://github.com/johnpbloch), [@kadamwhite](https://github.com/kadamwhite), [@scribu](https://github.com/scribu), [@danielbachhuber](https://github.com/danielbachhuber), [@tollmanz](https://github.com/tollmanz), [@mbijon](https://github.com/mbijon), [@markjaquith](https://github.com/markjaquith), [@curtismchale](https://github.com/curtismchale), [@Mamaduka](https://github.com/mamaduka), [@lgedeon](https://github.com/lgedeon), [@pmgarman](https://github.com/pmgarman), [@westonruter](https://github.com/westonruter), [@petemall](https://github.com/petemall), [@cmmarslender](https://github.com/cmmarslender), [@mintindeed](https://github.com/mintindeed), [@mboynes](https://github.com/mboynes), [@aaronjorbin](https://github.com/aaronjorbin), [@tobiasbg](https://github.com/tobiasbg), [@simonwheatley](https://github.com/simonwheatley), [@ocean90](https://github.com/ocean90), [@lvnilesh](https://github.com/lvnilesh), [@alexw23](https://github.com/alexw23), [@zamoose](https://github.com/zamoose), [@leewillis77](https://github.com/leewillis77), [@imichaeli](https://github.com/imichaeli), [@andrezrv](https://github.com/andrezrv), [@cadwell](https://github.com/cadwell), [@cfoellmann](https://github.com/cfoellmann), [@westi](https://github.com/westi), [@ryanduff](https://github.com/ryanduff), [@selinerdominik](https://github.com/selinerdominik), [@ericandrewlewis](https://github.com/ericandrewlewis), [@vDevices](https://github.com/vDevices), [@sunnyratilal](https://github.com/sunnyratilal), [@enejb](https://github.com/enejb), [@salcode](https://github.com/salcode), [@mattbanks](https://github.com/mattbanks)
* **Contributing**: Contributions are more than welcome. Please see our current [contributing guidelines](https://github.com/Varying-Vagrant-Vagrants/VVV/blob/master/CONTRIBUTING.md). Thanks!

## Overview

### The Purpose of Varying Vagrant Vagrants

The primary goal of Varying Vagrant Vagrants (VVV) is to provide an approachable way for developers to begin working in a development environment that matches a production environment as closely as possible.

The default server configuration provisioned by VVV is intended to match a common configuration for working with high traffic WordPress sites.

The default WordPress configurations provided by VVV are intended to create an environment ideal for developing themes and plugins as well as for contributing to WordPress core.

### How to Use Varying Vagrant Vagrants

#### VVV as a MAMP/XAMPP Replacement

VVV is ready to use as is. Download or clone VVV and then type `vagrant up` to automatically build a sandboxed Ubuntu server on your computer containing everything needed to contribute to WordPress core or develop a WordPress theme or plugin.

Multiple projects can be developed at once in the same environment.

* Use the `wordpress-develop` directory to participate in [WordPress core](http://core.trac.wordpress.org) development.
* Use `wp-content/themes` in either the `wordpress-default` or `wordpress-trunk` directories to develop multiple themes.
* Use `wp-content/plugins` in either the `wordpress-default` or `wordpress-trunk` directories to develop plugins.
* Take advantage of VVV's [auto site configuration](https://github.com/varying-vagrant-vagrants/vvv/wiki/Auto-site-Setup) to provision additional instances of WordPress in `/srv/www/`.

#### VVV as a Scaffold

Entirely different server configurations can be created by modifying the files included with VVV and through the use of additional [Auto Site Setup](https://github.com/varying-vagrant-vagrants/vvv/wiki/Auto-site-Setup) provisioning scripts.

It is not necessary to track the changes made to the main repository. Feel free to check this project out and then change everything to make it your own.

### The Future of Varying Vagrant Vagrants

Immediate goals for VVV include:

* Continue to work towards a stable state of software and configuration included in the default provisioning.
* Provide excellent and clear documentation throughout VVV to aid in both learning and scaffolding.

## Getting Started

### What is Vagrant?

[Vagrant](http://www.vagrantup.com) is a "tool for building and distributing development environments". It works with [virtualization](http://en.wikipedia.org/wiki/X86_virtualization) software such as [VirtualBox](https://www.virtualbox.org/) to provide a virtual machine that is sandboxed away from your local environment.

### The First Vagrant Up

1. Start with any operating system.
1. Install [VirtualBox 4.2.x](https://www.virtualbox.org/wiki/Download_Old_Builds_4_2) or [VirtualBox 4.3.6](https://www.virtualbox.org/wiki/Downloads)
    * Major portions of VirtualBox were rewritten for 4.3, and it's possible that there are still bugs to be shaken out. VVV is completely compatible with earlier versions of VirtualBox, so 4.2.22 or earlier would be just fine. Do note that Vagrant had specific issues with 4.2.16. Going as far back as 4.2.10 will likely be of no issue.
    * VVV itself leans in the 4.3.x direction in the master branch to stay ahead of the curve.
1. Install [Vagrant 1.4.3](http://www.vagrantup.com/downloads.html)
    * `vagrant` will now be available as a command in your terminal, try it out.
    * ***Note:*** If Vagrant is already installed, use `vagrant -v` to check the version. You may want to consider upgrading if a much older version is in use.
    * ***Note:*** If VirtualBox 4.3.x is installed, Vagrant 1.3.5 or later is required.
1. Install the [vagrant-hostsupdater](https://github.com/cogitatio/vagrant-hostsupdater) plugin with `vagrant plugin install vagrant-hostsupdater`
    * Note: This step is not a requirement, though it does make the process of starting up a virtual machine nicer by automating the entries needed in your local machine's `hosts` file to access the provisioned VVV domains in your browser.
    * If you choose not to install this plugin, a manual entry should be added to your local `hosts` file that looks like this: `192.168.50.4  vvv.dev local.wordpress.dev local.wordpress-trunk.dev src.wordpress-develop.dev build.wordpress-develop.dev`
1. Clone or extract the Varying Vagrant Vagrants project into a local directory
    * `git clone git://github.com/Varying-Vagrant-Vagrants/VVV.git vagrant-local`
    * OR download and extract the repository master [zip file](https://github.com/varying-vagrant-vagrants/vvv/archive/master.zip)
    * OR grab a [stable release](https://github.com/varying-vagrant-vagrants/vvv/releases) if you'd like some extra comfort.
1. Change into the new directory with `cd vagrant-local`
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

The first time you run `vagrant up`, a packaged box containing a basic virtual machine is downloaded to your local machine and cached for future use. The file used by Varying Vagrant Vagrants contains an installation of Ubuntu 12.04 and is about 280MB.

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

The network configuration picks an IP of 192.168.50.4. This works if you are *not* on the 192.168.50.x sub domain, it could cause conflicts on your existing network if you *are* on a 192.168.50.x sub domain already. You can configure any IP address in the `Vagrantfile` and it will be used on the next `vagrant up`

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
* See: [Connecting to MySQL](https://github.com/varying-vagrant-vagrants/vvv/wiki/Connecting-to-MySQL) from your local machine

### What do you get?

A bunch of stuff!

1. [Ubuntu](http://www.ubuntu.com/) 12.04 LTS (Precise Pangolin)
1. [WordPress Develop](http://develop.svn.wordpress.org/trunk/)
1. [WordPress Stable](http://wordpress.org/)
1. [WordPress Trunk](http://core.svn.wordpress.org/trunk/)
1. [WP-CLI](http://wp-cli.org/)
1. [nginx](http://nginx.org/) 1.4.x
1. [mysql](http://www.mysql.com/) 5.5.x
1. [php-fpm](http://php-fpm.org/) 5.5.x
1. [memcached](http://memcached.org/) 1.4.13
1. PHP [memcache extension](http://pecl.php.net/package/memcache/3.0.8/) 3.0.8
1. PHP [xdebug extension](http://pecl.php.net/package/xdebug/2.2.3/) 2.2.3
1. PHP [imagick extension](http://pecl.php.net/package/imagick/3.1.2/) 3.1.2
1. [PHPUnit](http://pear.phpunit.de/) 4.0.x
1. [ack-grep](http://beyondgrep.com/) 2.04
1. [git](http://git-scm.com/) 1.9.x
1. [subversion](http://subversion.apache.org/) 1.7.9
1. [ngrep](http://ngrep.sourceforge.net/usage.html)
1. [dos2unix](http://dos2unix.sourceforge.net/)
1. [Composer](https://github.com/composer/composer)
1. [phpMemcachedAdmin](https://code.google.com/p/phpmemcacheadmin/)
1. [phpMyAdmin](http://www.phpmyadmin.net/) 4.1.12 (multi-language)
1. [Opcache Status](https://github.com/rlerdorf/opcache-status)
1. [Webgrind](https://github.com/jokkedk/webgrind)
1. [NodeJs](http://nodejs.org/) Current Stable Version
1. [grunt-cli](https://github.com/gruntjs/grunt-cli) Current Stable Version

### Need Help?

* Let us have it! Don't hesitate to open a new issue on GitHub if you run into trouble or have any tips that we need to know.
* The [WordPress and Vagrant Mailing list](https://groups.google.com/forum/#!forum/wordpress-and-vagrant) is a great place to get started for any related topics.
* The [VVV Wiki](https://github.com/varying-vagrant-vagrants/vvv/wiki) also contains documentation that may help.

### Common Caveats

These are common to Vagrant environments and worth noting:
* If the directory VVV is inside of is moved once provisioned, it may break.
    * If `vagrant destroy` is used before moving, this should be fine.
* If Virtualbox is uninstalled, VVV will break.
* If Vagrant is uninstalled, VVV will break.

### History

VVV has come a long way since it was first [launched as Varying Vagrant Vagrants](http://jeremyfelt.com/code/2012/12/11/varying-vagrant-vagrants/) in December of 2012. Initially introduced as an exploration of workflow for immediate project needs at [10up](http://10up.com), VVV caught speed quickly as more and more of the team was introduced. During an internal [10up developer summit](http://10up.com/blog/10up-2013-developer-summit/) in March of 2013, Vagrant as a tool was a highlight and more developers made the conversion.

In April of 2013, we made a [call to the WordPress community](http://jeremyfelt.com/code/2013/04/08/hi-wordpress-meet-vagrant/) to try to encourage the addition of Vagrant to everyday life. These efforts continued with talks at [WordCamp Chicago](http://wordpress.tv/2013/12/31/jeremy-felt-hi-wordpress-meet-vagrant-2/), [WordCamp Vancouver](http://wordpress.tv/2013/10/19/jeremy-felt-hi-wordpress-meet-vagrant/), and WordCamp Denver.

In January of 2014, [10up](http://10up.com) made the decision to [spin VVV off](http://10up.com/blog/varying-vagrant-vagrants-future/) into its own organization to better align with the community that has grown around the project over time. This transition opens doors for what [Varying Vagrant Vagrants, the organization](http://jeremyfelt.com/code/2014/01/27/varying-vagrant-vagrants-organization/) can accomplish as an ongoing project.
