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
 
end
