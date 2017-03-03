# Custom Domains and Hosts

There are 3 ways to define hosts

 - The main `vvv-custom.yml` config file
 - A `vvv-hosts` file
 - The `vvv-init.sh` file

The recommended way is to use the `vvv-custom.yml` file. `vvv-hosts` is supported for backwards compatibility, and `vvv-init.sh` can be used for edge circumstances

When changing hosts, the Nginx config will need updating so that Nginx knows to listen for requests on those domains. If this isn't done, the VVV dashboard will appear instead of the desired site.

**Remember**, you need to reprovision for a change to take effect, run `vagrant reload` after making changes.

## vvv-custom.yml

When adding a site in `vvv-custom.yml`, add a hosts section listing the domains of that site. For example:

```
  example:
    hosts:
      - example.com
```

This will map example.com to the example site, and update the HOST file on your machine.

## vvv-hosts

VVV 1 added hosts using a file named `vvv-hosts`, and VVV 2 continues support for this for backwards compatibility reasons. Place this as a text file with no file extension in a `provision` subfolder, or in the root of the site.

Here's an example that adds 2 domains:

```
example.com
example.net
```

## vvv-init.sh

Sometimes, it's useful to generate domains automatically. We can do this by piping to /etc/hosts inside the Virtual Machine via `vvv-init.sh`.

In this example, we dynamically add a domain generated via the name given to the site:

```
# Add the site name to the hosts file
echo "127.0.0.1 ${VVV_SITE_NAME}.local # vvv-auto" >> "/etc/hosts"
```

For a site named example, this would generate the domain http://example.local
