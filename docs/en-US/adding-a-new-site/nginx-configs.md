---
layout: page
title: Nginx Configuration
permalink: /docs/en-US/adding-a-new-site/nginx-configuration/
---

@TODO: Higher level notes and introduction to `vvv-nginx.conf`

## A Standard WordPress Nginx Configuration

For most WordPress sites, this NGINX configuration will suffice:

```Nginx
server {
  listen 80;
  listen 443 ssl;
  server_name {vvv_site_name}.local;
  root {vvv_path_to_site};

  error_log {vvv_path_to_site}/log/error.log;
  access_log {vvv_path_to_site}/log/access.log;

  set $upstream {upstream};

  include /etc/nginx/nginx-wp-common.conf;
}
```

## nginx-wp-common.conf

This is an Nginx config file provided by VVV. Including it pulls in a number of useful rules, such as PHP Fast CGI and rules for using Nginx with permalinks.

While not required, it's strongly recommended that this config file is included.

## Nginx Variable Replacements

Before VVV copies the configs to there final location, it runs a search replace routine. This allows variables containing information about the site to be used inside the Nginx config.

The config at the top of this page contains several examples. E.g. `{vvv_site_name}` is used to set the domain used, and `{vvv_path_to_site}` is used to set the root and log locations.

## Nginx Upstream

You may have noticed this line in the example above:

```Nginx
set $upstream {upstream};
```

The `{upstream}` variable is set from `vvv-custom.yml`, and is used to determine the version of PHP to use. Removing this will disable that functionality.

It may be desirable to force a site to use a particular version of PHP, for details see the [changing PHP versions](changing-php-version.md) documentation.

## PHP Error Logs

```Nginx
error_log {vvv_path_to_site}/log/error.log;
access_log {vvv_path_to_site}/log/access.log;
```

These two lines tell Nginx where to log errors and requests to the site. In this example, the logs for the `example` site are located at `www/example/log/error.log`

Because the logs are being saved in a subfolder, it will be necessary to create the `log` folder and initial log files during provision. To do this, add these lines to `vvv-init.sh`:

```shell
# Nginx Logs
mkdir -p ${VVV_PATH_TO_SITE}/log
touch ${VVV_PATH_TO_SITE}/log/error.log
touch ${VVV_PATH_TO_SITE}/log/access.log
```
