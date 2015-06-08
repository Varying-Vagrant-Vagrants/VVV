# -*- mode: ruby -*-
# vi: set ft=ruby :

vagrant_dir = File.expand_path(File.dirname(__FILE__))

Vagrant.configure("2") do |config|

  # Store the current version of Vagrant for use in conditionals when dealing
  # with possible backward compatible issues.
  vagrant_version = Vagrant::VERSION.sub(/^v/, '')

  # Configuration options for the VirtualBox provider.
  config.vm.provider :virtualbox do |v|
    v.customize ["modifyvm", :id, "--memory", 1024]
    v.customize ["modifyvm", :id, "--cpus", 1]
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
  end

  # Configuration options for the Parallels provider.
  config.vm.provider :parallels do |v|
    v.update_guest_tools = true
    v.optimize_power_consumption = false
    v.memory = 1024
    v.cpus = 1
  end

  # Configuration options for the VMware Fusion provider.
  config.vm.provider :vmware_fusion do |v|
    v.vmx["memsize"] = "1024"
    v.vmx["numvcpus"] = "1"
  end

  # SSH Agent Forwarding
  #
  # Enable agent forwarding on vagrant ssh commands. This allows you to use ssh keys
  # on your host machine inside the guest. See the manual for `ssh-add`.
  config.ssh.forward_agent = true

  # Default Ubuntu Box
  #
  # This box is provided by Ubuntu vagrantcloud.com and is a nicely sized (332MB)
  # box containing the Ubuntu 14.04 Trusty 64 bit release. Once this box is downloaded
  # to your host computer, it is cached for future use under the specified box name.
  config.vm.box = "ubuntu/trusty64"

  # The Parallels Provider uses a different naming scheme.
  config.vm.provider :parallels do |v, override|
    override.vm.box = "parallels/ubuntu-14.04"
  end

  # The VMware Fusion Provider uses a different naming scheme.
  config.vm.provider :vmware_fusion do |v, override|
    override.vm.box = "netsensia/ubuntu-trusty64"
  end
  
  # VMWare Workstation can use the same package as Fusion
  config.vm.provider :vmware_workstation do |v, override|
    override.vm.box = "netsensia/ubuntu-trusty64"
  end

  config.vm.hostname = "vvv"

  # Local Machine Hosts
  #
  # If the Vagrant plugin hostsupdater (https://github.com/cogitatio/vagrant-hostsupdater) is
  # installed, the following will automatically configure your local machine's hosts file to
  # be aware of the domains specified below. Watch the provisioning script as you may need to
  # enter a password for Vagrant to access your hosts file.
  #
  # By default, we'll include the domains set up by VVV through the vvv-hosts file
  # located in the www/ directory.
  #
  # Other domains can be automatically added by including a vvv-hosts file containing
  # individual domains separated by whitespace in subdirectories of www/.
  if defined?(VagrantPlugins::HostsUpdater)
    # Recursively fetch the paths to all vvv-hosts files under the www/ directory.
    paths = Dir[File.join(vagrant_dir, 'www', '**', 'vvv-hosts')]

    # Parse the found vvv-hosts files for host names.
    hosts = paths.map do |path|
      # Read line from file and remove line breaks
      lines = File.readlines(path).map(&:chomp)
      # Filter out comments starting with "#"
      lines.grep(/\A[^#]/)
    end.flatten.uniq # Remove duplicate entries

    # Pass the found host names to the hostsupdater plugin so it can perform magic.
    config.hostsupdater.aliases = hosts
    config.hostsupdater.remove_on_suspend = true
  end

  # Private Network (default)
  #
  # A private network is created by default. This is the IP address through which your
  # host machine will communicate to the guest. In this default configuration, the virtual
  # machine will have an IP address of 192.168.50.4 and a virtual network adapter will be
  # created on your host machine with the IP of 192.168.50.1 as a gateway.
  #
  # Access to the guest machine is only available to your local host. To provide access to
  # other devices, a public network should be configured or port forwarding enabled.
  #
  # Note: If your existing network is using the 192.168.50.x subnet, this default IP address
  # should be changed. If more than one VM is running through VirtualBox, including other
  # Vagrant machines, different subnets should be used for each.
  #
  config.vm.network :private_network, ip: "192.168.50.4"

  # Public Network (disabled)
  #
  # Using a public network rather than the default private network configuration will allow
  # access to the guest machine from other devices on the network. By default, enabling this
  # line will cause the guest machine to use DHCP to determine its IP address. You will also
  # be prompted to choose a network interface to bridge with during `vagrant up`.
  #
  # Please see VVV and Vagrant documentation for additional details.
  #
  # config.vm.network :public_network

  # Port Forwarding (disabled)
  #
  # This network configuration works alongside any other network configuration in Vagrantfile
  # and forwards any requests to port 8080 on the local host machine to port 80 in the guest.
  #
  # Port forwarding is a first step to allowing access to outside networks, though additional
  # configuration will likely be necessary on our host machine or router so that outside
  # requests will be forwarded from 80 -> 8080 -> 80.
  #
  # Please see VVV and Vagrant documentation for additional details.
  #
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Drive mapping
  #
  # The following config.vm.synced_folder settings will map directories in your Vagrant
  # virtual machine to directories on your local machine. Once these are mapped, any
  # changes made to the files in these directories will affect both the local and virtual
  # machine versions. Think of it as two different ways to access the same file. When the
  # virtual machine is destroyed with `vagrant destroy`, your files will remain in your local
  # environment.

  # /srv/database/
  #
  # If a database directory exists in the same directory as your Vagrantfile,
  # a mapped directory inside the VM will be created that contains these files.
  # This directory is used to maintain default database scripts as well as backed
  # up mysql dumps (SQL files) that are to be imported automatically on vagrant up
  config.vm.synced_folder "database/", "/srv/database"

  # If the mysql_upgrade_info file from a previous persistent database mapping is detected,
  # we'll continue to map that directory as /var/lib/mysql inside the virtual machine. Once
  # this file is changed or removed, this mapping will no longer occur. A db_backup command
  # is now available inside the virtual machine to backup all databases for future use. This
  # command is automatically issued on halt, suspend, and destroy if the vagrant-triggers
  # plugin is installed.
  if File.exists?(File.join(vagrant_dir,'database/data/mysql_upgrade_info')) then
    if vagrant_version >= "1.3.0"
      config.vm.synced_folder "database/data/", "/var/lib/mysql", :mount_options => [ "dmode=777", "fmode=777" ]
    else
      config.vm.synced_folder "database/data/", "/var/lib/mysql", :extra => 'dmode=777,fmode=777'
    end

    # The Parallels Provider does not understand "dmode"/"fmode" in the "mount_options" as
    # those are specific to Virtualbox. The folder is therefore overridden with one that
    # uses corresponding Parallels mount options.
    config.vm.provider :parallels do |v, override|
      override.vm.synced_folder "database/data/", "/var/lib/mysql", :mount_options => []
    end
  end

  # /srv/config/
  #
  # If a server-conf directory exists in the same directory as your Vagrantfile,
  # a mapped directory inside the VM will be created that contains these files.
  # This directory is currently used to maintain various config files for php and
  # nginx as well as any pre-existing database files.
  config.vm.synced_folder "config/", "/srv/config"

  # /srv/log/
  #
  # If a log directory exists in the same directory as your Vagrantfile, a mapped
  # directory inside the VM will be created for some generated log files.
  config.vm.synced_folder "log/", "/srv/log", :owner => "www-data"

  # /srv/www/
  #
  # If a www directory exists in the same directory as your Vagrantfile, a mapped directory
  # inside the VM will be created that acts as the default location for nginx sites. Put all
  # of your project files here that you want to access through the web server
  if vagrant_version >= "1.3.0"
    config.vm.synced_folder "www/", "/srv/www/", :owner => "www-data", :mount_options => [ "dmode=775", "fmode=774" ]
  else
    config.vm.synced_folder "www/", "/srv/www/", :owner => "www-data", :extra => 'dmode=775,fmode=774'
  end

  config.vm.provision "fix-no-tty", type: "shell" do |s|
    s.privileged = false
    s.inline = "sudo sed -i '/tty/!s/mesg n/tty -s \\&\\& mesg n/' /root/.profile"
  end

  # The Parallels Provider does not understand "dmode"/"fmode" in the "mount_options" as
  # those are specific to Virtualbox. The folder is therefore overridden with one that
  # uses corresponding Parallels mount options.
  config.vm.provider :parallels do |v, override|
    override.vm.synced_folder "www/", "/srv/www/", :owner => "www-data", :mount_options => []
  end

  # Customfile - POSSIBLY UNSTABLE
  #
  # Use this to insert your own (and possibly rewrite) Vagrant config lines. Helpful
  # for mapping additional drives. If a file 'Customfile' exists in the same directory
  # as this Vagrantfile, it will be evaluated as ruby inline as it loads.
  #
  # Note that if you find yourself using a Customfile for anything crazy or specifying
  # different provisioning, then you may want to consider a new Vagrantfile entirely.
  if File.exists?(File.join(vagrant_dir,'Customfile')) then
    eval(IO.read(File.join(vagrant_dir,'Customfile')), binding)
  end

  # Provisioning
  #
  # Process one or more provisioning scripts depending on the existence of custom files.
  #
  # provison-pre.sh acts as a pre-hook to our default provisioning script. Anything that
  # should run before the shell commands laid out in provision.sh (or your provision-custom.sh
  # file) should go in this script. If it does not exist, no extra provisioning will run.
  if File.exists?(File.join(vagrant_dir,'provision','provision-pre.sh')) then
    config.vm.provision :shell, :path => File.join( "provision", "provision-pre.sh" )
  end

  # provision.sh or provision-custom.sh
  #
  # By default, Vagrantfile is set to use the provision.sh bash script located in the
  # provision directory. If it is detected that a provision-custom.sh script has been
  # created, that is run as a replacement. This is an opportunity to replace the entirety
  # of the provisioning provided by default.
  if File.exists?(File.join(vagrant_dir,'provision','provision-custom.sh')) then
    config.vm.provision :shell, :path => File.join( "provision", "provision-custom.sh" )
  else
    config.vm.provision :shell, :path => File.join( "provision", "provision.sh" )
  end

  # provision-post.sh acts as a post-hook to the default provisioning. Anything that should
  # run after the shell commands laid out in provision.sh or provision-custom.sh should be
  # put into this file. This provides a good opportunity to install additional packages
  # without having to replace the entire default provisioning script.
  if File.exists?(File.join(vagrant_dir,'provision','provision-post.sh')) then
    config.vm.provision :shell, :path => File.join( "provision", "provision-post.sh" )
  end

  # Always start MySQL on boot, even when not running the full provisioner
  # (run: "always" support added in 1.6.0)
  if vagrant_version >= "1.6.0"
    config.vm.provision :shell, inline: "sudo service mysql restart", run: "always"
    config.vm.provision :shell, inline: "sudo service nginx restart", run: "always"
  end

  # Vagrant Triggers
  #
  # If the vagrant-triggers plugin is installed, we can run various scripts on Vagrant
  # state changes like `vagrant up`, `vagrant halt`, `vagrant suspend`, and `vagrant destroy`
  #
  # These scripts are run on the host machine, so we use `vagrant ssh` to tunnel back
  # into the VM and execute things. By default, each of these scripts calls db_backup
  # to create backups of all current databases. This can be overridden with custom
  # scripting. See the individual files in config/homebin/ for details.
  if defined? VagrantPlugins::Triggers
    config.trigger.before :halt, :stdout => true do
      run "vagrant ssh -c 'vagrant_halt'"
    end
    config.trigger.before :suspend, :stdout => true do
      run "vagrant ssh -c 'vagrant_suspend'"
    end
    config.trigger.before :destroy, :stdout => true do
      run "vagrant ssh -c 'vagrant_destroy'"
    end
  end
end
