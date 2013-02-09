# -*- mode: ruby -*-
# vi: set ft=ruby :

dir = Dir.pwd

Vagrant::Config.run do |config|
  config.vm.box = "quantal32-official"
  config.vm.box_url = "http://cloud-images.ubuntu.com/vagrant/quantal/current/quantal-server-cloudimg-i386-vagrant-disk1.box"
  config.vm.host_name = "mbairdev"
  config.vm.customize ["modifyvm", :id, "--memory", 512]
  config.vm.network :hostonly, "192.168.50.4"
  config.vm.share_folder "server-conf", "/srv/server-conf", File.join( dir, "server-conf" )
  config.vm.share_folder "nginx-sites", "/etc/nginx/custom-sites", File.join( dir, "server-conf", "sites" )
  config.vm.share_folder "web-dev", "/srv/www/", File.join( dir, "www" ), :owner => "www-data"
  config.vm.provision :shell, :path => File.join( "provision", "provision.sh" )
end
