# Nginx configs

@TODO: Higher level notes and introduction to `vvv-nginx.conf`

## A Standard WordPress Nginx Configuration

For most WordPress sites, this NGINX configuration will suffice:

```
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

@TODO:

## Nginx Upstream

@TODO:
