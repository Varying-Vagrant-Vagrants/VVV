Varying Vagrant Vagrants
========================

My (@jeremyfelt) Assorted (?) Vagrant Configs

### So...
I'm still learning this [Vagrant](http://vagrantup.com) stuff, so take anything I say or put into this repo right now with a huge grain of salt.

But, I think this actually works.

### How?
Start with OS X, then...

1. First install [VirtualBox](https://www.virtualbox.org/wiki/Downloads). This is the magic that helps the magic behind Vagrant run. I will have you know that I had to uninstall my previous version of VirtualBox and install with the latest before I was able to get Vagrant to work. Results will vary.
1. Download and install [Vagrant 1.0.5](http://downloads.vagrantup.com/tags/v1.0.5), you will now have access to the `vagrant` command via whatever terminal you use.
1. Clone this repo onto your machine.
    * `git clone git://github.com/jeremyfelt/varying-vagrant-vagrants.git`
1. Poke around and modify the files to fit your needs.
    * Vagrant file picks up its location dynamically, so you may get by without path changes.
    * The network configuration picks an IP of 192.168.50.4. This works if you are *not* on the 192.168.50.x sub domain, it could cause conflicts on your existing network if you *are* on a 192.168.50.x sub domain already.
    * Move `server-conf/create-dbs.sql.sample` to `server-conf/create-dbs.sql` and edit it to add whichever `CREATE DATABASE` and `GRANT ALL PRIVILEGES` statements you want to run on startup to prepare mysql for SQL imports (see next bullet).
    * Have any SQL files that should be imported in the `server-conf/db-dumps/` directory and named as `db_name.sql`. The `import-sql.sh` script will run automatically when the VM is built and import these databases into the new mysql install as long as the proper databases have already been created via the previous step's SQL.
    * Check out the example nginx in `server-conf/sites` and create any other site specific configs you think should be available on server start. The web directory is `/srv/www/` and default configs are provided for a basic WordPress setup.
    * Other stuff. Familiarize and all that.
1. Go to the root directory of the project and type `vagrant up`, magic should happen.
1. After a minute or few, all packages will be installed and you'll be greeted with an `ifconfig` result. Take the IP address - 192.168.50.4 by default - and use it in your local `/etc/hosts` file to setup any domains that you expect to be served with nginx.
1. Start using your sites!
1. Or... the more fun part... dig around in your new server by typing `vagrant ssh`

### What do you get?
A bunch of stuff!

1. Ubuntu 12.04 (Precise Pangolin)
2. nginx 1.19
3. mysql 5.5
4. php 5.3.10 with php-fpm
5. memcached 1.4.13
6. PECL [memcache extension](http://pecl.php.net/package/memcache)
6. curl
7. vim
8. git
9. make
10. [ngrep](http://ngrep.sourceforge.net/usage.html)
11. xdebug ( 'off' by default, to toggle `vagrant ssh` and perform `xdebug_on` or `xdebug_off` )

### Feedback?
Let me have it! If you have tips that I need to know, send them my way at [@jeremyfelt](http://twitter.com/jeremyfelt) or find me in [other ways](http://jeremyfelt.com).
