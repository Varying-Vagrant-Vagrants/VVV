# Adding an Existing Site

To do this there are 3 steps:

 - `vvv-custom.yml` and the sites folder
 - Files
 - Provisioner files
 - Restart/reprovision VVV
 - Database

I'm going to walk through setting up a blog named vvvtest.com locally using VVV, but this could be a site currently hosted in MAMP.

## `vvv-custom.yml` and The Main Folder

First we need to tell VVV about the site. I'm going to give the site the name `vvvtest`, and update the sites list in `vvv-custom.yml`:

```YAML
	vvvtest:
		hosts:
			vvvtest.com
```

## Files

Now that VVV knows about our site with the name `vvvtest`, it's going to look inside the `www/vvvtest` folder for our site. We need to create and fill this folder. If I had named the site `testables`, the folder would be `www/testables`.

After creating the folder, download a copy of the site into that folder, and create a `provision` subfolder.

### Bonus: Git

Rather than manually copying files into the folder, copy them into a git repository, and use the `repo` key to tell VVV where to find it.

With this, you can automate a large chunk of the work for new users when working in a team, and encourage healthy version control workflows!

For example:

```YAML
	vvvtest:
		repo: https://github.com/example/site.git
		hosts:
			vvvtest.com
```

We **strongly** recommend this.

## Provisioner Files

 - provision
 	- vvv-init.sh ( optional )
 	- vvv-nginx.conf

### Init script

This file is optional, but when combined with a git repository this becomes very powerful.

`vvv-init.sh` is ran when VVV sets up the site, and gives you an opportunity to execute shell commands, including WP CLI commands.

Your script might:
 - Download and install the latest WordPress
 - Update and install plugins
 - Checkout extra git repos
 - Run `composer install` and other dependency managers and task runners
 - Create an empty database if it doesn't exist and fill it with starter content

#### An Example

Here is an example script that will work for a basic WordPress multisite install:

```shell
#!/usr/bin/env bash

# Add the site name to the hosts file
echo "127.0.0.1 ${VVV_SITE_NAME}.local # vvv-auto" >> "/etc/hosts"

# Make a database, if we don't already have one
echo -e "\nCreating database '${VVV_SITE_NAME}' (if it's not already there)"
mysql -u root --password=root -e "CREATE DATABASE IF NOT EXISTS ${VVV_SITE_NAME}"
mysql -u root --password=root -e "GRANT ALL PRIVILEGES ON ${VVV_SITE_NAME}.* TO wp@localhost IDENTIFIED BY 'wp';"
echo -e "\n DB operations done.\n\n"

# Nginx Logs
mkdir -p ${VVV_PATH_TO_SITE}/log
touch ${VVV_PATH_TO_SITE}/log/error.log
touch ${VVV_PATH_TO_SITE}/log/access.log

# Install and configure the latest stable version of WordPress
cd ${VVV_PATH_TO_SITE}
if ! $(wp core is-installed --allow-root); then
 wp core download --path="${VVV_PATH_TO_SITE}" --allow-root
 wp core config --dbname="${VVV_SITE_NAME}" --dbuser=wp --dbpass=wp --quiet --allow-root
 wp core multisite-install --url="${VVV_SITE_NAME}.local" --quiet --title="${VVV_SITE_NAME}" --admin_name=admin --admin_email="admin@${VVV_SITE_NAME}.local" --admin_password="password" --allow-root
else
 wp core update --allow-root
fi
```

This will:
 - download, configure and install a fresh copy of WordPress, keep it up to date
 - Make sure the PHP error logs are created
 - Check if a database exists, if it isn't, create one and grant the needed priviledges

### Nginx config

VVV uses Nginx as a web server, but Nginx needs to know how to serve a WP site. Luckily VVV provides those rules, requiring only a small config file that works for 99% of WP sites.

For our example, we only need to change the domain/host and copy paste the result into `provision/vvv-nginx.conf`:

```nginx
server {
 listen 80;
 listen 443 ssl;
 server_name vvvtest.com;
 root {vvv_path_to_site};

 error_log {vvv_path_to_site}/log/error.log;
 access_log {vvv_path_to_site}/log/access.log;

 set $upstream {upstream};

 include /etc/nginx/nginx-wp-common.conf;
}
```

For more information about Nginx and VVV, read the [Nginx Configs page](adding-a-new-site/nginx-configs.md) of adding a new site.

## Reprovision

Any time we make changes to `vvv-config.yml` or the provisioner files of a site, we need to restart VVV. This allows VVV to catch up with the latest changes and activates your new site.

To do this, run `vagrant reload`.

## Database

Now our site is active and running in VVV, but there's no content. We need to transfer the database contents into the database running inside VVV.

There are several ways to do this:

 - Do the 5 minute install of WordPress and use the importer plugin
 - Use the PHPMyAdmin install that comes with VVV, by visiting [http://vvv.dev](http://vvv.dev)
 - Connect directly to the MySQL server using the default credentials
 - Restore a backup via a plugin
 - Automatically import an sql file in vvv-init.sh if the database is empty using the `mysql` command
 