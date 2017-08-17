---
layout: page
title: Utilities
permalink: /docs/en-US/utilities/
---

Utilities are packages for VVV that install system level functionality. For example, a core utilities package is provided by default. This default utility can install phpmyadmin, webgrind, and other versions of PHP.

Here are the default utilities as they would be defined in `vvv-custom.yml` in full:

```
utilities:
  core:
    - memcached-admin
    - opcache-status
    - phpmyadmin
    - webgrind
utility-sources:
  core: https://github.com/Varying-Vagrant-Vagrants/vvv-utilities.git
```

Utilities are defined at the end of the file, outside of the sites section. The `utility-sources` section defines the name of a utility and where it can be found.

## Adding Utilities

Lets say that I want to run Java 7 inside a VVV installation. In order to install java, I'll need a utility. Lets name it `java` and include it:

```
utilities:
  core:
    - php56
  java:
    - java7
utility-sources:
  java: https://github.com/example/java-utilities.git
```

My hypothetical utility defines how to install different versions of Java, and is located in a git repository. I might have defined how to install java 8, or java 6, but here I used java 7.

## How Utility Repositories Are Structured

A utility repo contains folders, and each folder has a provisioner script inside.

With this in mind, I would expect the java repository mentioned earlier to have this folder structure:

 - java6/
   - provision.sh
 - java7/
   - provision.sh
 - java8/
   - provision.sh
 - readme.md

The name of the subfolder maps directly on to what is put in `vvv-custom.yml`. VVV will run the `provision.sh` file, at which point it can do as it pleases. This could be installing a package via `apt-get` or something else. Other files can be included in these folders for `provision.sh` to make use of.