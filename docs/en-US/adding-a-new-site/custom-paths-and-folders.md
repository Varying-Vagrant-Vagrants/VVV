---
layout: page
title: Custom Paths and Folders
permalink: /docs/en-US/adding-a-new-site/custom-paths-and-folders/
---

A site in a non-standard folder can still be used via the `vm_dir` and `local_dir` keys. `local_dir` tells VVV where the site is located on the host machine, and `vm_dir` tells VVV where the site is located inside the Virtual machine.

For example, if we put our test sites in a subfolder, we can specify each site like this in the `sites` section:

```YAML
test-site-1:
  vm_dir: /srv/www/test-sites/test-site-1
  local_dir: www/test-sites/test-site-1
  hosts:
    - testsite1.com

test-site-2:
  vm_dir: /srv/www/test-sites/test-site-2
  local_dir: www/test-sites/test-site-2
  hosts:
    - testsite2.com
```

In the above example, the `vm_dir` and `local_dir` point to the same folder (`vm_dir` needs to be an absolute path), however, this doesn’t have to be the case.

In this example, VVV is told to use a site stored outside of the main VVV folder, and mapped to an absolute path in the virtual machine:

```YAML
example-site:
  vm_dir: /srv/www/example-site
  local_dir: /Users/janesmith/Documents/example-site
  hosts:
    - examplesite.com
```
