# Troubleshooting

Need help?

* Let us have it! Don't hesitate to open a new issue on GitHub if you run into trouble or have any tips that we need to know.
* The [VVV Wiki](https://github.com/varying-vagrant-vagrants/vvv/wiki) also contains documentation that may help.

## Caveats

The network configuration picks an IP of 192.168.50.4. It could cause conflicts on your existing network if you *are* on a 192.168.50.x subnet already. You can configure any IP address in the `Vagrantfile` and it will be used on the next `vagrant up`

VVV relies on the stability of both Vagrant and VirtualBox. These caveats are common to Vagrant environments and are worth noting:
* If the directory VVV is inside of is moved once provisioned (`vagrant-local`), it may break.
    * If `vagrant destroy` is used before moving, this should be fine.
* If VirtualBox is uninstalled, VVV will break.
* If Vagrant is uninstalled, VVV will break.

The default memory allotment for the VVV virtual machine is 1024MB. If you would like to raise or lower this value to better match your system requirements, a [guide to changing memory size](https://github.com/Varying-Vagrant-Vagrants/VVV/wiki/Customising-your-Vagrant's-attributes-and-parameters) is in the wiki.

Since version 1.2.0, VVV has used a 64bit version of Ubuntu. Some older CPUs (such as the popular *Intel Core 2 Duo* series) do not support this. Changing the line `config.vm.box = "ubuntu/trusty64"` to `"ubuntu/trusty32"` in the `Vagrantfile` before `vagrant up` will provision a 32bit version of Ubuntu that will work on older hardware.

## Backups

In the event that you're stuck or at a loss, VVV tries to generate database backups at `VVV/database/backups/*.sql`, with a file for each database.

This coupled with the uploads in the file system should allow the VVV environment to be recreated from a clean slate.