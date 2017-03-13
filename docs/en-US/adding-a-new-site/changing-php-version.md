---
layout: page
title: Changing PHP Version
permalink: /docs/en-US/adding-a-new-site/changing-php-version/
---

You can set the PHP version in `vvv-custom.yml` when defining a site. To do this, use the `nginx_upstream` option to specify the PHP version. VVV also needs to be told to install that version of PHP using the `utilities` section.

Here’s an example that uses PHP v7.1:

```YAML
sites:
  example:
    nginx_upstream: php71

utilities:
  core:
    - php71
```

This will not work if `set $upstream {upstream};` is removed from the nginx config.

In this example, we have changed the `wordpress-default` site to use PHP 7.1, and the `wordpress-develop` site to use PHP 5.6:

```YAML
sites:
  wordpress-default:
    repo: https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-default.git
    nginx_upstream: php71
    hosts:
      - local.wordpress.dev

  wordpress-develop:
    repo: https://github.com/Varying-Vagrant-Vagrants/vvv-wordpress-develop.git
    nginx_upstream: php56
    hosts:
      - src.wordpress-develop.dev
      - build.wordpress-develop.dev

utilities:
  core:
    - memcached-admin
    - opcache-status
    - phpmyadmin
    - webgrind
    - php56
    - php71
```

## Forcing a Version of PHP

It may be desirable to force a site to use a particular version of PHP, even if `vvv-custom.yml` disagrees.

This is done by overriding the nginx upstream value inside `vvv-nginx.conf`. To do this change this:

```nginx
 set $upstream {upstream};
```

To this:

```nginx
 set $upstream php71;
```

That site is now using PHP 7.1, remember to reprovision using `vagrant reload --provision`
