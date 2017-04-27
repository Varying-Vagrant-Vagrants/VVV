---
layout: page
title: Adding a New Site
permalink: /docs/en-US/adding-a-new-site/
---

Adding a new site is as simple as adding it under the sites section of `vvv-custom.yml`. If `vvv-custom.yml` does not exist, you can create it by copying `vvv-config.yml` to `vvv-custom.yml`.

To do this there are 3 steps:

 - `vvv-custom.yml` and the sites folder
 - Files
 - Provisioner files
 - Restart/reprovision VVV
 - Database

I'm going to walk through setting up a blog named vvvtest.com locally using VVV, but this could be a site currently hosted in MAMP.

If you're migrating a site from VVV 1, read this page, then visit the [migration page](../migrating-vvv1.md) for further details.

## `vvv-custom.yml` and The Main Folder

First we need to tell VVV about the site. I'm going to give the site the name `vvvtest`, and update the sites list in `vvv-custom.yml`:

```YAML
vvvtest:
```

We also want to specify the host as vvvtest.com:

```YAML
vvvtest:
  hosts:
    - vvvtest.com
```

Read here for [more information about Domains and hosts](custom-domains-hosts.md)

## Files

Now that VVV knows about our site with the name `vvvtest`, it's going to look inside the `www/vvvtest` folder for our site. We need to create and fill this folder. If I had named the site `testables`, the folder would be `www/testables`.

After creating the folder, download a copy of the site into that folder, and create a `provision` subfolder.

If you'd like to change the folders VVV uses, [read here for more information](custom-paths-and-folders.md)

### Bonus: Git

Rather than manually copying files into the folder, copy them into a git repository, and use the `repo` key to tell VVV where to find it.

With this, you can automate a large chunk of the work for new users when working in a team, and encourage healthy version control workflows!

For example:

```YAML
vvvtest:
  repo: https://github.com/example/site.git
  hosts:
    - vvvtest.com
```

We **strongly** recommend this.

## Provisioner Files

 - provision
 - vvv-init.sh ( optional )
 - vvv-nginx.conf

The site will require a `vvv-init.sh` to download, install, and setup WordPress. [Read about `vvv-init.sh` and an example here](setup-script.md)

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

For more information about Nginx and VVV, read the [Nginx Configs page](nginx-configs.md) of adding a new site.

## Reprovision

Any time we make changes to `vvv-custom.yml` or the provisioner files of a site, we need to restart VVV. This allows VVV to catch up with the latest changes and activates your new site.

To do this, run `vagrant reload --provision`.

## Database

Now our site is active and running in VVV, but there's no content. We need to transfer the database contents into the database running inside VVV.

There are several ways to do this:

 - Do the 5 minute install of WordPress and use the importer plugin
 - Use the PHPMyAdmin install that comes with VVV, by visiting [http://vvv.dev](http://vvv.dev)
 - Connect directly to the MySQL server using the default credentials
 - Restore a backup via a plugin
 - Automatically import an sql file in vvv-init.sh if the database is empty using the `mysql` command
