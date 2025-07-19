def vvv_configure_plugins( config, vvv_config, vagrant_dir )
  # Auto Download Vagrant plugins, supported from Vagrant 2.2.0
  unless Vagrant.has_plugin?('vagrant-hostsupdater') && Vagrant.has_plugin?('vagrant-goodhosts') && Vagrant.has_plugin?('vagrant-hostsmanager')
    if File.file?(File.join(vagrant_dir, 'vagrant-goodhosts.gem'))
      system('vagrant plugin install ' + File.join(vagrant_dir, 'vagrant-goodhosts.gem'))
      File.delete(File.join(vagrant_dir, 'vagrant-goodhosts.gem'))
      puts "#{YELLOW}VVV needed to install the vagrant-goodhosts plugin which is now installed. Please run the requested command again.#{CRESET}"
      exit
    else
      config.vagrant.plugins = ['vagrant-goodhosts']
    end
  end

  # The vbguest plugin has issues for some users, so we're going to disable it for now
  config.vbguest.auto_update = false if Vagrant.has_plugin?('vagrant-vbguest')

  # Specify disk size
  #
  # If the Vagrant plugin disksize (https://github.com/sprotheroe/vagrant-disksize) is
  # installed, the following will automatically configure your local machine's disk size
  # to be the specified size. This plugin only works on VirtualBox.
  #
  # Warning: This plugin only resizes up, not down, so don't set this to less than 10GB,
  # and if you need to downsize, be sure to destroy and reprovision.
  #
  if !vvv_config['vagrant-plugins']['disksize'].nil? && defined?(Vagrant::Disksize)
    config.vm.provider :virtualbox do |_v, override|
      override.disksize.size = vvv_config['vagrant-plugins']['disksize']
    end
    if Etc.uname[:version].include? 'ARM64'
      puts "WARNING: Vagrant disksize requires VirtualBox, if you are not using VirtualBox please remove this plugin immediatley"
    end
  end
end

def vvv_configure_hosts( config, vvv_config, vagrant_dir )
    # Local Machine Hosts
  #
  # If the Vagrant plugin goodhosts (https://github.com/goodhosts/vagrant/) is
  # installed, the following will automatically configure your local machine's hosts file to
  # be aware of the domains specified below. Watch the provisioning script as you may need to
  # enter a password for Vagrant to access your hosts file.
  #
  # By default, we'll include the domains set up by VVV through the vvv-hosts file
  # located in the www/ directory and in config/config.yml.
  #
  if config.vagrant.plugins.include? 'vagrant-goodhosts'
    config.goodhosts.aliases = vvv_config['hosts']
    config.goodhosts.remove_on_suspend = true

    # goodhosts already disables clean by default, but lets enforce this at both ends
    config.goodhosts.disable_clean = true
  elsif config.vagrant.plugins.include? 'vagrant-hostsmanager'
    config.hostmanager.aliases = vvv_config['hosts']
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
    config.hostmanager.manage_guest = true
    config.hostmanager.ignore_private_ip = false
    config.hostmanager.include_offline = true
  elsif config.vagrant.plugins.include? 'vagrant-hostsupdater'
    # Pass the found host names to the hostsupdater plugin so it can perform magic.
    config.hostsupdater.aliases = vvv_config['hosts']
    config.hostsupdater.remove_on_suspend = true
  elsif %w[up halt resume suspend status provision reload].include? ARGV[0]
    puts ""
    puts " X ! There is no hosts file vagrant plugin installed!"
    puts " X You need the vagrant-goodhosts plugin (or HostManager/ HostsUpdater ) for domains to work in the browser"
    puts " X Run 'vagrant plugin install --local' to fix this."
    puts ""
  end
end
