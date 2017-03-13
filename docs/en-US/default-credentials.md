---
layout: page
title: Default Credentials
permalink: /docs/en-US/default-credentials/
---

All database usernames and passwords for WordPress installations included by default are:

__User:__ `wp`
__Password:__ `wp`

All WordPress admin usernames and passwords for WordPress installations included by default are:

__User:__ `admin`
__Password:__ `password`

MySQL Root:

__User:__ `root`
__Password:__ `root`

See: [Connecting to MySQL](https://github.com/varying-vagrant-vagrants/vvv/wiki/Connecting-to-MySQL) from your local machine

Vagrant Box Ubuntu Root:

__User:__ `root`
__Password:__ `vagrant`

#### WordPress Stable
* LOCAL PATH: vagrant-local/www/wordpress-default
* VM PATH: /srv/www/wordpress-default
* URL: `http://local.wordpress.dev`
* DB Name: `wordpress_default`

#### WordPress Develop
* LOCAL PATH: vagrant-local/www/wordpress-develop
* VM PATH: /srv/www/wordpress-develop
* /src URL: `http://src.wordpress-develop.dev`
* /build URL: `http://build.wordpress-develop.dev`
* DB Name: `wordpress_develop`
* DB Name: `wordpress_unit_tests`
