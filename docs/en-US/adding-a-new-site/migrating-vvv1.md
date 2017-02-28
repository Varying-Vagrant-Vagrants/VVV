# Migrating from VVV 1

VVV 1 sites still work, but they require an additional step.

## Telling VVV 2 About Your Site

VVV 2 uses a config file to discover sites. Adding your site to this file will allow VVV 2 to provision and host it.

If, for example, you have a site at `www/my-test-site`, you can migrate it to VVV2 by adding this to the `sites` section of `vvv-custom.yml`:

```
    my-test-site:
```

Turn your VVV instance off and on to reload the config, and VVV will now look inside `www/my-test-site` for provisioning files the same way VVV 1 does.

### Git Repositories

It's possible to specify a git repository rather than a folder, and VVV2 will clone it and provision the contents automatically, for example:

```
    my-test-site: https://github.com/etc.....
```

This will clone the git repository into `www/my-test-site` and provision the contents.

This can also be done via the `repo` key, allowing extra options, such as hosts to be defined:

```
    my-test-site:
    	repo: https://github.com/etc.....
    	hosts:
    		mytestsite.com
```

### VVV 1 Sites in Non-Standard Folders

Some VVV 1 sites are in nested or non-standard folder structures. These are still supported. See the [custom paths and folders](custom-paths-and-folders.md) documentation for how to configure these sites.


## Why is This Needed?

VVV sites work the same way in VVV 1 and VVV 2, but with one major difference. VVV 2 uses a config file, and VVV 1 scans for sites automatically.

### How VVV 1 Detects Sites

When the v1 provisioner runs, it searches the VVV folder for `vvv-init.sh` and `vvv-nginx.conf` files. As a result VVV picked up sites regardless of location, even catching nested sites.

But this caused performance problems. Folder scans could be very slow with some file systems, and there was no way to control which sites were provisioned. If you wanted to provision a site quickly, larger sites had to be moved out of the `www` folder.

### How VVV 2 Detects Sites

VVV 2 did away with site auto-detection. Instead VVV uses a config file named `vvv-custom.yml` that lists all the sites. This way a user can set the folder used via the `vm_dir` option, or skip provisioning via the `skip_provisioning` option.

This also makes provisioning significantly faster, and allows for additional options.
