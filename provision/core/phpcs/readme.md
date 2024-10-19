## VVV PHPCS setup

This folder houses a composer file that installs PHPCS. If PHPCS fails, you can destroy/nuke the vendor and bin folders, as well as the lock file, and re-provision, or even run composer yourself on the host.

The goal for us in terms of packages is to enable both WordPress and VIP coding standards, with PHP compat thrown in for good measure.

Additionally, this is in a shared folder so that you can use the same PHPCS install in your VVV VM as well as your IDE/editor.

