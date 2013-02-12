# -*- mode: ruby -*-
# vi: set ft=ruby :

dir = Dir.pwd

Vagrant::Config.run do |config|
  config.vm.box = "10up-precise32-0.3"
  config.vm.box_url = "http://vagrantbox.jeremyfelt.com/10up-precise32-0.3.box"
  config.vm.host_name = "precise32-dev"
  config.vm.customize ["modifyvm", :id, "--memory", 512]
  config.vm.network :hostonly, "192.168.50.4", :auto_config => true, :adapter => 2

  # Drive mapping
  #
  # The following config.vm.share_folder settings will map directories in your Vagrant
  # virtual machine to directories on your local machine. Once these are mapped, any
  # changes made to the files in these directories will affect both the local and virtual
  # machine versions. Think of it as two different ways to access the same file.

  # server-conf/
  #
  # If a server-conf directory exists in the same directory as your Vagrantfile,
  # a mapped directory inside the VM will be created that contains these files.
  # This directory is currently used to maintain various config files for php and 
  # nginx as well as any pre-existing database files.
  config.vm.share_folder "server-conf", "/srv/server-conf", File.join( dir, "server-conf" )
  
  # server-conf/sites/
  #
  # If a sites directory exists inside the above server-conf directory, it will be
  # added as a mapped directory inside the VM as well. This is used to maintain specific
  # site configuration files for nginx
  config.vm.share_folder "nginx-sites", "/etc/nginx/custom-sites", File.join( dir, "server-conf", "sites" )
  
  # www/
  #
  # If a www directory exists in the same directory as your Vagrantfile, a mapped directory
  # inside the VM will be created that acts as the default location for nginx sites. Put all
  # of your project files here that you want to access through the web server
  config.vm.share_folder "web-dev", "/srv/www/", File.join( dir, "www" ), :owner => "www-data"

  config.vm.provision :shell, :path => File.join( "provision", "provision.sh" )
end
