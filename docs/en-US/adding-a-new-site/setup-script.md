---
layout: page
title: Setup Script
permalink: /docs/en-US/adding-a-new-site/setup-script/
---

`vvv-init.sh` is ran when VVV sets up the site, and gives you an opportunity to execute shell commands, including WP CLI commands. This file is optional, but when combined with a git repository this becomes very powerful.

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
