# -*- mode: ruby -*-
# vi: set ft=ruby :

dir = Dir.pwd

Vagrant::Config.run do |config|
  config.vm.box = "10up-quantal-0.1"
  config.vm.box_url = "http://vagrantbox.jeremyfelt.com/10up-quantal-0.1.box"
  config.vm.host_name = "quantal-dev"
  config.vm.customize ["modifyvm", :id, "--memory", 360]
  config.vm.network :hostonly, "192.168.50.4"
  config.vm.share_folder "server-conf", "/srv/server-conf", File.join( dir, "server-conf" )
  config.vm.share_folder "nginx-sites", "/etc/nginx/custom-sites", File.join( dir, "server-conf", "sites" )
  config.vm.share_folder "web-dev", "/srv/www/", File.join( dir, "www" ), :owner => "www-data"
  config.vm.provision :shell, :path => File.join( "provision", "provision.sh" )
end
