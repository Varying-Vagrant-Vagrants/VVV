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

@TODO:

## Nginx Variable Replacements

@TODO:

## Nginx Upstream

@TODO:
