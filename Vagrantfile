# -*- mode: ruby -*-
# vi: set ft=ruby :

dir = Dir.pwd

Vagrant.configure("1") do |config|

  # Configurations from the 1.0.x Vagrantfile spec can go here and are backward
  # compatible with Vagrant 1.1.x. These should be transferred over time to the
  # 1.1.x format for better future support.
  
  # The customize command is specific to Virtualbox and was used in Vagrant 1.0.x
  # A warning will appear when using it in Vagrant 1.1.x as other providers do not
  # support this option. This warning is safe to ignore if you are running Virtualbox
  config.vm.customize ["modifyvm", :id, "--memory", 512]

end

Vagrant.configure("2") do |config|
  
  # Default Ubuntu Box
  #
  # This box is provided by Vagrant at vagrantup.com and is a nicely sized (290MB)
  # box containing the Unbuntu 12.0.4 Precise 32 bit release.
  config.vm.box = "std-precise32"
  config.vm.box_url = "http://files.vagrantup.com/precise32.box"

  # Preconfigured box
  #
  # If you use the box configured on the next two lines, a large 700 MB file
  # will be cached to your machine with preinstalled versions of the software
  # normally included in the provisioning script.
  #config.vm.box = "10up-precise32-0.3"
  #config.vm.box_url = "http://vagrantbox.jeremyfelt.com/10up-precise32-0.3.box"
  
  config.vm.hostname = "precise32-dev"
  config.vm.network :private_network, ip: "192.168.50.4"
 
  # Drive mapping
  #
  # The following config.vm.share_folder settings will map directories in your Vagrant
  # virtual machine to directories on your local machine. Once these are mapped, any
  # changes made to the files in these directories will affect both the local and virtual
  # machine versions. Think of it as two different ways to access the same file.

  # /srv/database/
  #
  # If a database directory exists in the same directory as your Vagrantfile,
  # a mapped directory inside the VM will be created that contains these files.
  # This directory is used to maintain default database scripts as well as backed
  # up mysql dumps (SQL files) that are to be imported automatically on vagrant up
  config.vm.synced_folder "database/", "/srv/database"
  config.vm.synced_folder "database/data/", "/var/lib/mysql", :extra => 'dmode=777,fmode=777'

  # /srv/config/
  #
  # If a server-conf directory exists in the same directory as your Vagrantfile,
  # a mapped directory inside the VM will be created that contains these files.
  # This directory is currently used to maintain various config files for php and 
  # nginx as well as any pre-existing database files.
  config.vm.synced_folder "config/", "/srv/config"
  
  # /srv/config/nginx-config/sites/
  #
  # If a sites directory exists inside the above server-conf directory, it will be
  # added as a mapped directory inside the VM as well. This is used to maintain specific
  # site configuration files for nginx
  config.vm.synced_folder "config/nginx-config/sites/", "/etc/nginx/custom-sites"
  
  # /srv/www/
  #
  # If a www directory exists in the same directory as your Vagrantfile, a mapped directory
  # inside the VM will be created that acts as the default location for nginx sites. Put all
  # of your project files here that you want to access through the web server
  config.vm.synced_folder "www/", "/srv/www/", :owner => "www-data"

  config.vm.provision :shell, :path => File.join( "provision", "provision.sh" )
end
