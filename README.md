Varying Vagrant Vagrants
========================

A series of varying Vagrant setups from [@jeremyfelt](http://github.com/jeremyfelt)
* with awesome assists from fellow 10upers [@carldanley](http://github.com/carldanley), [@ericmann](http://github.com/ericmann) and [@lkwdwrd](http://github.com/lkwdwrd)

### So this is Vagrant...
I'm still learning this [Vagrant](http://vagrantup.com) stuff, so take anything I say or put into this repo right now with a huge grain of salt.

But, I think this actually works. Actually, after a couple months, it works really well.

### How?
Start with any operating system, then...

1. First install [VirtualBox 4.2.6](https://www.virtualbox.org/wiki/Downloads). This is the magic that helps the magic behind Vagrant run. I will have you know that I had to uninstall my previous version of VirtualBox and install with the latest before I was able to get Vagrant to work. Results will vary.
1. Download and install [Vagrant 1.0.6](http://downloads.vagrantup.com/tags/v1.0.6), you will now have access to the `vagrant` command via whatever terminal you use.
1. Clone this repo onto your machine in a directory where you want your environment to be stored:
    * `git clone git://github.com/jeremyfelt/varying-vagrant-vagrants.git vagrant-local-dev`
1. Change into this new directory created with the repo:
    * `cd vagrant-local-dev`
1. Start Vagrant:
	* `vagrant up` - *omg magic happens*
	* Once this build process is initiated, the ~600MB Virtual Machine will download and start up as a sandboxed local environment.
1. Add a record to your hosts file so that we can access the default WordPress installation:
	* `192.168.50.4  local.wordpress.dev`
1. Visit `http://local.wordpress.dev/` in your browser and install WordPress

Fancy, yeah?

### Now What?
Now that you're up and running with a default configuration, start poking around and modifying things.

1. Access the server with `vagrant ssh` from your `vagrant-local-dev` directory. You can do pretty much anything you would do with a standard Ubuntu installation on a full server.
1. Destroy the box and start from scratch (don't worry, the initial ~600MB file will be cached on your machine ) with `vagrant destroy`
1. Power off the box with `vagrant halt` or suspend it with `vagrant suspend` - if you suspend it, you can bring it back quickly with `vagrant resume`
1. Start modifying and adding local files to fit your needs.
    * The network configuration picks an IP of 192.168.50.4. This works if you are *not* on the 192.168.50.x sub domain, it could cause conflicts on your existing network if you *are* on a 192.168.50.x sub domain already. You can configure any IP address in the `Vagrantfile` and it will be used on the next `vagrant up`
    * Move `server-conf/create-dbs.sql.sample` to `server-conf/create-dbs.sql` and edit it to add whichever `CREATE DATABASE` and `GRANT ALL PRIVILEGES` statements you want to run on startup to prepare mysql for SQL imports (see next bullet).
    * Have any SQL files that should be imported in the `server-conf/db-dumps/` directory and named as `db_name.sql`. The `import-sql.sh` script will run automatically when the VM is built and import these databases into the new mysql install as long as the proper databases have already been created via the previous step's SQL.
    * Check out the example nginx configurations in `server-conf/sites` and create any other site specific configs you think should be available on server start. The web directory is `/srv/www/` and default configs are provided for a basic WordPress setup.
    * Other stuff. Familiarize and all that.

### What do you get?
A bunch of stuff!

1. Ubuntu 12.04 (Precise Pangolin)
2. nginx 1.2.6
3. mysql 5.5.30
4. php-fpm 5.3.21
5. memcached 1.4.13
6. PECL [memcache extension](http://pecl.php.net/package/memcache)
6. curl
7. vim
8. git
9. make
10. [ngrep](http://ngrep.sourceforge.net/usage.html)

### Feedback?
Let me have it! If you have tips that I need to know, send them my way at [@jeremyfelt](http://twitter.com/jeremyfelt) or find me in [other ways](http://jeremyfelt.com). I have some blog posts written that may provide more insight...
* [Varying Vagrant Vagrants](http://jeremyfelt.com/code/2012/12/11/varying-vagrant-vagrants/)
* [A WordPress Meetup Introduction to Vagrant](http://jeremyfelt.com/code/2013/02/04/an-wordpress-meetup-introduction-to-vagrant-what-youll-need/)
* [Clear nginx Cache in Vagrant](http://jeremyfelt.com/code/2013/01/08/clear-nginx-cache-in-vagrant/)
