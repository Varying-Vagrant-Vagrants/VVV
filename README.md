Varying Vagrant Vagrants
========================

A series of varying Vagrant setups from [@jeremyfelt](http://github.com/jeremyfelt)
* with awesome assists from fellow 10upers [@carldanley](http://github.com/carldanley), [@ericmann](http://github.com/ericmann), [@lkwdwrd](http://github.com/lkwdwrd) and [@TheLastCicada](http://github.com/TheLastCicada)

### So this is Vagrant...
I'm still learning this [Vagrant](http://vagrantup.com) stuff, so take anything I say or put into this repo right now with a huge grain of salt.

But, I think this actually works. Actually, after a couple months, it works really well.

### How?
Start with any operating system, then...

1. First install [VirtualBox 4.2.10](https://www.virtualbox.org/wiki/Downloads). This is the magic that helps the magic behind Vagrant run. I will have you know that I had to uninstall my previous version of VirtualBox and install with the latest before I was able to get Vagrant to work. Results will vary.
1. Download and install [Vagrant 1.1.0](http://downloads.vagrantup.com/tags/v1.1.0), you will now have access to the `vagrant` command via whatever terminal you use.
1. Clone this repo onto your machine in a directory where you want your environment to be stored:
    * `git clone git://github.com/jeremyfelt/varying-vagrant-vagrants.git vagrant-local-dev`
1. Change into this new directory created with the repo:
    * `cd vagrant-local-dev`
1. Start Vagrant:
	* `vagrant up` - *omg magic happens*
	* Once this build process is initiated, a ~280MB virtual machine will download and startup as a sandboxed local environment.
1. Add a record to your hosts file so that we can access the default WordPress installation:
	* `192.168.50.4  local.wordpress.dev`
1. Visit `http://local.wordpress.dev/` in your browser and install WordPress

Fancy, yeah?

### Now What?
Now that you're up and running with a default configuration, start poking around and modifying things.

1. Access the server with `vagrant ssh` from your `vagrant-local-dev` directory. You can do pretty much anything you would do with a standard Ubuntu installation on a full server.
1. Destroy the box and start from scratch with `vagrant destroy`
    * The initial ~280MB file will be cached on your machine, though on the next `vagrant up` it will need to go through the provisioning process, which requires access to the Internet for various apt source packages.
1. Power off the box with `vagrant halt` or suspend it with `vagrant suspend`. If you suspend it, you can bring it back quickly with `vagrant resume`, if you halt it, you can bring it back with `vagrant up`.
1. Start modifying and adding local files to fit your needs.
    * The network configuration picks an IP of 192.168.50.4. This works if you are *not* on the 192.168.50.x sub domain, it could cause conflicts on your existing network if you *are* on a 192.168.50.x sub domain already. You can configure any IP address in the `Vagrantfile` and it will be used on the next `vagrant up`
    * Move `database/init-custom.sql.sample` to `database/init-custom.sql` and edit it to add whichever `CREATE DATABASE` and `GRANT ALL PRIVILEGES` statements you want to run on startup to prepare mysql for SQL imports (see next bullet).
    * Have any SQL files that should be imported in the `database/backups/` directory and named as `db_name.sql`. The `import-sql.sh` script will run automatically when the VM is built and import these databases into the new mysql install as long as the proper databases have already been created via the previous step's SQL.
    * Check out the example nginx configurations in `config/nginx-config/sites` and create any other site specific configs you think should be available on server start. The web directory is `/srv/www/` and default configs are provided for a basic WordPress setup.
    * Other stuff. Familiarize and all that.

### What do you get?
A bunch of stuff!

1. Ubuntu 12.04 (Precise Pangolin)
2. nginx 1.1.19
3. mysql 5.5.29
4. php-fpm 5.3.10
5. memcached 1.4.13
6. PECL [memcache extension](http://pecl.php.net/package/memcache)
6. curl
7. vim
8. git
9. make
10. [ngrep](http://ngrep.sourceforge.net/usage.html)
11. dos2unix

### Startup Time

Startup times for this Vagrant setup can vary widely, especially when booting from scratch, due to the downloads required to install all packages the first time. Here are some real world scenarios.

#### Fast Cable Connection - ?? down / ?? up

#### Fast DSL Connection - ?? down / ?? up

#### Slow DSL Connection - 1.1 down / 0.3 up

Doing a `vagrant up` after a `vagrant destroy` with only the initial ~280M box cached took about **25 minutes** due to the number of apt packages that needed to be download.

Doing a `vagrant up` after a `vagrant halt` took about **1.5 minutes**, mostly because we still run the `apt-get upgrade` routine and try to install various packages.

Doing a `vagrant resume` after a `vagrant suspend` took about **12 seconds**, because no network activity is required.

### Feedback?
Let me have it! If you have tips that I need to know, send them my way at [@jeremyfelt](http://twitter.com/jeremyfelt) or find me in [other ways](http://jeremyfelt.com). I have some blog posts written that may provide more insight...
* [Varying Vagrant Vagrants](http://jeremyfelt.com/code/2012/12/11/varying-vagrant-vagrants/)
* [A WordPress Meetup Introduction to Vagrant](http://jeremyfelt.com/code/2013/02/04/an-wordpress-meetup-introduction-to-vagrant-what-youll-need/)
* [Clear nginx Cache in Vagrant](http://jeremyfelt.com/code/2013/01/08/clear-nginx-cache-in-vagrant/)
