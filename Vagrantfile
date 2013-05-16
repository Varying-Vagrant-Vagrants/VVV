# -*- mode: ruby -*-
# vi: set ft=ruby :

dir = Dir.pwd

Vagrant.configure("2") do |config|

  # Configurations from 1.0.x can be placed in Vagrant 1.1.x specs like the following.
  config.vm.provider :virtualbox do |v|
	v.customize ["modifyvm", :id, "--memory", 512]
  end
  
  # Default Ubuntu Box
  #
  # This box is provided by Vagrant at vagrantup.com and is a nicely sized (290MB)
  # box containing the Unbuntu 12.0.4 Precise 32 bit release.
  config.vm.box = "std-precise32"
  config.vm.box_url = "http://files.vagrantup.com/precise32.box"

  config.vm.hostname = "precise32-dev"
  config.vm.network :private_network, ip: "192.168.50.4"

  # Address a bug in an older version of Puppet
  #
  # Once precise32 ships with Puppet 2.7.20+, we can safely remove
  # See http://stackoverflow.com/questions/10894661/augeas-support-on-my-vagrant-machine
  config.vm.provision :shell, :inline => "sudo apt-get update && sudo apt-get install puppet -y"

  # Provision everything we need with Puppet

  config.vm.provision :puppet do |puppet|
    puppet.manifest_file  = "setup.pp"
  end

  config.vm.provision :puppet do |puppet|
    puppet.manifest_file  = "tools.pp"
  end

  # Install and configure a basic database
  config.vm.provision :puppet do |puppet|
    puppet.module_path = "modules"
    puppet.manifest_file  = "database.pp"
  end
 
  # Install and configure a web server
  config.vm.provision :puppet do |puppet|
    puppet.module_path = "modules"
    puppet.manifest_file = "webserver.pp"
  end
  
  # Install and configure our default projects
  config.vm.provision :puppet do |puppet|
    puppet.module_path = "modules"
    puppet.manifest_file = "default-projects.pp"
  end

  # Run provisioning for any user-installed projects
  Dir.glob( "projects/*/vvv.pp" ).each do |vagrant_project|

    # Remove the file from the path name
    vagrant_project.slice! "/vvv.pp"

    config.vm.provision :puppet do |puppet|
      puppet.module_path = "modules"
      # vagrant_project is now the relative path to a single vvv.pp
      puppet.manifests_path = vagrant_project
      puppet.manifest_file = 'vvv.pp'
    end
  end

end
