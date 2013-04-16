Varying Vagrant Vagrants
========================

Varying Vagrant Vagrants is an evolving [Vagrant](http://vagrantup.com) configuration with a goal of providing a system to pass development projects between team members for easy ramp up on projects.

* **Version**: 0.6-working
* **Contributors**: [@jeremyfelt](http://github.com/jeremyfelt), [@carldanley](http://github.com/carldanley), [@ericmann](http://github.com/ericmann), [@lkwdwrd](http://github.com/lkwdwrd), [@TheLastCicada](http://github.com/TheLastCicada), [@tddewey](http://github.com/tddewey), [@johnpbloch](http://github.com/johnpbloch) and [@kadamwhite](http://github.com/kadamwhite)
* **Contributing**: Contributions are more than welcome. Please submit pull requests against the [develop branch](https://github.com/10up/varying-vagrant-vagrants/tree/develop). Pull requests against master will be met with a friendly comment asking you to redo. Thanks!

## What is Vagrant?

[Vagrant](http://vagrantup.com) is a "tool for building and distributing development environments". It works with virtualization software such as [VirtualBox](http://virtualbox.org) to provide a virtual machine that is sandboxed away from your local environment.

## Getting Started

1. Start with any operating system. Vagrant and VirtualBox have installation packages for Windows, OSX and Linux.
1. Install [VirtualBox 4.2.10](https://www.virtualbox.org/wiki/Downloads).
1. Install [Vagrant 1.1.5](http://downloads.vagrantup.com/tags/v1.1.5)
    * `vagrant` will now be available as a command in the terminal
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
1. Visit `http://local.wordpress.dev/` in your browser for WordPress 3.5.1 or `http://local.wordpress-trunk.dev` for WordPress trunk.

Fancy, yeah?

## What Did That Do?

The first time you run `vagrant up`, a pre-packaged virtual machine box is downloaded to your local machine and cached for future use. The file used by Varying Vagrant Vagrants is about 280MB.

After this box is download, it begins to boot as a sandboxed VirtualBox virtual machine. When ready, it runs the provisioning script also provided with this repository. This initiates the download and installation of around 80MB of packages to be installed on the new virtual machine.

The time for all of this to happen depends a lot on the speed of your Internet connection. If you are on a fast cable connection, it will more than likely only take a few minutes.

On future runs of `vagrant up`, the pre-packaged box will already be cached on your machine and Vagrant will only need to deal with provisioning. If the machine has been destroyed with `vagrant destroy`, it will need to download the full 80MB of packages to install. If the vagrant has been powered off with `vagrant halt`, the provisioning script will run but will not need to download anything.

## Now What?

Now that you're up and running with a default configuration, start poking around and modifying things.

1. Access the server with `vagrant ssh` from your `vagrant-local` directory. You can do pretty much anything you would do with a standard Ubuntu installation on a full server.
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

## What do you get?

A bunch of stuff!

1. Ubuntu 12.04 (Precise Pangolin)
1. nginx 1.1.19
1. mysql 5.5.29
1. php-fpm 5.3.10
1. memcached 1.4.13
1. PECL [memcache extension](http://pecl.php.net/package/memcache) 2.2.7
1. PECL [xdebug extension](http://pecl.php.net/package/xdebug) 2.2.1
1. PEAR [PHPUnit](http://pear.phpunit.de/) 3.7.18
1. [ack-grep](http://betterthangrep.com/) 1.92
1. curl
1. vim
1. git
1. make
1. [ngrep](http://ngrep.sourceforge.net/usage.html)
1. dos2unix
1. [WordPress 3.5.1](http://wordpress.org)
1. [WordPress trunk](http://core.svn.wordpress.org/trunk)

### Startup Time

Startup times for this Vagrant setup can vary widely, especially when booting from scratch, due to the downloads required to install all packages the first time. Here are some real world scenarios.

#### Fast Cable Connection - 58 down / 12 up

* `vagrant up` after `vagrant destroy` (or from scratch) with only the initial ~280M box cached took about **3 minutes**
* `vagrant provision` on running box took about **30 seconds**
* `vagrant up` on powered off box (`vagrant halt`) took about **30 seconds**

#### Slow DSL Connection - 1.1 down / 0.3 up

* `vagrant up` after a `vagrant destroy` (or from scratch) with only the initial ~280M box cached took about **15 minutes**
* `vagrant up` after a `vagrant halt` took about **1 minute**.
* `vagrant resume` after a `vagrant suspend` took about **12 seconds**

## Feedback?

Let us have it! If you have tips that we need to know, open a new issue, send them our way at [@jeremyfelt](http://twitter.com/jeremyfelt), or find us in [other ways](http://10up.com). Some blog posts have been written documenting the process that may provide insight....

* [Hi WordPress, Meet Vagrant](http://jeremyfelt.com/code/2013/04/08/hi-wordpress-meet-vagrant/)
* [Evolving WordPress Development With Vagrant](http://jeremyfelt.com/code/2013/03/17/evolving-wordpress-development-with-vagrant/)
* [Varying Vagrant Vagrants](http://jeremyfelt.com/code/2012/12/11/varying-vagrant-vagrants/)
* [A WordPress Meetup Introduction to Vagrant](http://jeremyfelt.com/code/2013/02/04/an-wordpress-meetup-introduction-to-vagrant-what-youll-need/)
* [Clear nginx Cache in Vagrant](http://jeremyfelt.com/code/2013/01/08/clear-nginx-cache-in-vagrant/)
