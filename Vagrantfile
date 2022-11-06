# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:

VAGRANTFILE_API_VERSION = '2'
ENV['LC_ALL']           = 'en_US.UTF-8'

Vagrant.require_version '>= 2.2.4'
require 'yaml'
require 'fileutils'

require './.vvv/lib/info'
require './.vvv/lib/config'
require './.vvv/lib/splash_screens'
require './.vvv/lib/bootstrap'
require './.vvv/lib/migrate'

VVV::SplashScreens.v_logo_with_info if VVV::Bootstrap.show_logo?
VVV.SplashScreens.warning_sudo_bear if VVV::Bootstrap.show_sudo_bear?

VVV::Migrate.migrate_config
VVV::Migrate.migrate_sql_database_backups

vvv_config  = VVV::Config.new.values
vagrant_dir = VVV::Info.vagrant_dir

if VVV::Bootstrap.show_logo?
  VVV::SplashScreens.info_platform vvv_config
  VVV::SplashScreens.info_provider(
    vvv_config['vm_config']['provider'],
    VVV::Info.provider_version(vvv_config['vm_config']['provider'])
  )
  if VVV::Bootstrap.box_overridden?(vvv_config)
    VVV::SplashScreens.info_box_overridden
  end
end

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  # VirtualBox
  config.vm.provider :virtualbox do |v|
    v.customize ['modifyvm', :id, '--uartmode1', 'file', File.join(vagrant_dir, 'log/ubuntu-cloudimg-console.log')]
    v.customize ['modifyvm', :id, '--memory', vvv_config['vm_config']['memory']]
    v.customize ['modifyvm', :id, '--cpus', vvv_config['vm_config']['cores']]
    v.customize ['modifyvm', :id, '--natdnshostresolver1', 'on']
    v.customize ['modifyvm', :id, '--natdnsproxy1', 'on']

    # see https://github.com/hashicorp/vagrant/issues/7648
    v.customize ['modifyvm', :id, '--cableconnected1', 'on']

    v.customize ['modifyvm', :id, '--rtcuseutc', 'on']
    v.customize ['modifyvm', :id, '--audio', 'none']
    v.customize ['modifyvm', :id, '--paravirtprovider', 'kvm']

    # https://github.com/laravel/homestead/pull/63
    v.customize ['modifyvm', :id, '--ostype', 'Ubuntu_64']

    v.customize ['setextradata', :id, 'VBoxInternal2/SharedFoldersEnableSymlinksCreate//srv/www', '1']
    v.customize ['setextradata', :id, 'VBoxInternal2/SharedFoldersEnableSymlinksCreate//srv/config', '1']

    # Set the box name in VirtualBox to match the working directory.
    v.name = File.basename(vagrant_dir) + '_' + (Digest::SHA256.hexdigest vagrant_dir)[0..10]
  end

  # Configuration options for the Parallels provider.
  config.vm.provider :parallels do |v|
    v.customize ['set', :id, '--longer-battery-life', 'off']
    v.memory = vvv_config['vm_config']['memory']
    v.cpus = vvv_config['vm_config']['cores']
  end

  # Configuration options for the VMware Desktop provider.
  config.vm.provider :vmware_desktop do |v|
    v.vmx['memsize'] = vvv_config['vm_config']['memory']
    v.vmx['numvcpus'] = vvv_config['vm_config']['cores']
  end

  # Configuration options for Hyper-V provider.
  config.vm.provider :hyperv do |v|
    v.memory = vvv_config['vm_config']['memory']
    v.cpus = vvv_config['vm_config']['cores']
    v.linked_clone = true
  end

  # Auto Download Vagrant plugins, supported from Vagrant 2.2.0
  unless Vagrant.has_plugin?('vagrant-hostsupdater') && Vagrant.has_plugin?('vagrant-goodhosts') && Vagrant.has_plugin?('vagrant-hostsmanager')
    if File.file?(File.join(vagrant_dir, 'vagrant-goodhosts.gem'))
      system('vagrant plugin install ' + File.join(vagrant_dir, 'vagrant-goodhosts.gem'))
      File.delete(File.join(vagrant_dir, 'vagrant-goodhosts.gem'))
      puts "#{VVV::SplashScreens::C_YELLOW}VVV needed to install the vagrant-goodhosts plugin which is now installed. Please run the requested command again.#{VVV::SplashScreens::C_RESET}"
      exit
    else
      config.vagrant.plugins = ['vagrant-goodhosts']
    end
  end

  # The vbguest plugin has issues for some users, so we're going to disable it for now
  config.vbguest.auto_update = false if Vagrant.has_plugin?('vagrant-vbguest')

  # SSH Agent Forwarding
  #
  # Enable agent forwarding on vagrant ssh commands. This allows you to use ssh keys
  # on your host machine inside the guest. See the manual for `ssh-add`.
  config.ssh.forward_agent = true

  # SSH Key Insertion
  #
  # This is disabled, we had several contributors who ran into issues.
  # See: https://github.com/Varying-Vagrant-Vagrants/VVV/issues/1551
  config.ssh.insert_key = false
  config.vm.box_check_update = false

  # The Parallels Provider uses a different naming scheme.
  config.vm.provider :parallels do |_v, override|
    override.vm.box = 'bento/ubuntu-20.04'

    # Vagrant currently runs under Rosetta on M1 devices. As a result,
    # this seems to be the most reliable way to detect whether or not we're
    # running under ARM64.
    if Etc.uname[:version].include? 'ARM64'
      override.vm.box = 'mpasternak/focal64-arm'
    end
  end

  # The VMware Desktop Provider uses a different naming scheme.
  config.vm.provider :vmware_desktop do |v, override|
    override.vm.box = 'bento/ubuntu-20.04'
    v.gui = false
  end

  # Hyper-V uses a different base box.
  config.vm.provider :hyperv do |_v, override|
    override.vm.box = 'bento/ubuntu-20.04'
  end

  # Virtualbox.
  config.vm.provider :virtualbox do |_v, override|
    # Default Ubuntu Box
    #
    # This box is provided by Bento boxes via vagrantcloud.com and is a nicely sized
    # box containing the Ubuntu 20.04 Focal 64 bit release. Once this box is downloaded
    # to your host computer, it is cached for future use under the specified box name.
    override.vm.box = 'bento/ubuntu-20.04'

    # If we're at a contributor day, switch the base box to the prebuilt one
    if defined? vvv_config['vm_config']['wordcamp_contributor_day_box']
      if vvv_config['vm_config']['wordcamp_contributor_day_box'] == true
        override.vm.box = 'vvv/contribute'
      end
    end
  end

  if defined? vvv_config['vm_config']['box']
    unless vvv_config['vm_config']['box'].nil?
      config.vm.box = vvv_config['vm_config']['box']
    end
  end

  config.vm.hostname = 'vvv'

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
      puts "WARNING: Vagrant disksize requires VirtualBox and is incompatible with Arm devices, uninstall immediatley"
    end
  end

  # Private Network (default)
  #
  # A private network is created by default. This is the IP address through which your
  # host machine will communicate to the guest. In this default configuration, the virtual
  # machine will have an IP address of 192.168.56.4 and a virtual network adapter will be
  # created on your host machine with the IP of 192.168.50.1 as a gateway.
  #
  # Access to the guest machine is only available to your local host. To provide access to
  # other devices, a public network should be configured or port forwarding enabled.
  #
  # Note: If your existing network is using the 192.168.50.x subnet, this default IP address
  # should be changed. If more than one VM is running through VirtualBox, including other
  # Vagrant machines, different subnets should be used for each.
  #
  config.vm.network :private_network, id: 'vvv_primary', ip: vvv_config['vm_config']['private_network_ip']

  config.vm.provider :hyperv do |_v, override|
    override.vm.network :private_network, id: 'vvv_primary', ip: nil
  end

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

  # Disable the default synced folder to avoid overlapping mounts
  config.vm.synced_folder '.', '/vagrant', disabled: true
  config.vm.provision 'file', source: "#{vagrant_dir}/version", destination: '/home/vagrant/version'

  # /srv/database/
  #
  # If a database directory exists in the same directory as your Vagrantfile,
  # a mapped directory inside the VM will be created that contains these files.
  # This directory is used to maintain default database scripts as well as backed
  # up MariaDB/MySQL dumps (SQL files) that are to be imported automatically on vagrant up
  config.vm.synced_folder 'database/sql/', '/srv/database'
  use_db_share = false

  if defined? vvv_config['general']['db_share_type']
    use_db_share = vvv_config['general']['db_share_type'] == true
  end
  if use_db_share == true
    # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
    config.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 9001, group: 9001, mount_options: ['dmode=775', 'fmode=664']

    # The Parallels Provider does not understand "dmode"/"fmode" in the "mount_options" as
    # those are specific to Virtualbox. The folder is therefore overridden with one that
    # uses corresponding Parallels mount options.
    config.vm.provider :parallels do |_v, override|
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 9001, group: 9001, mount_options: [ 'share' ]
    end
    # Neither does the HyperV provider
    config.vm.provider :hyperv do |_v, override|
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 9001, group: 9001, mount_options: ['dir_mode=0775', 'file_mode=0664']
    end
  end

  # /srv/config/
  #
  # If a server-conf directory exists in the same directory as your Vagrantfile,
  # a mapped directory inside the VM will be created that contains these files.
  # This directory is currently used to maintain various config files for php and
  # nginx as well as any pre-existing database files.
  config.vm.synced_folder 'config/', '/srv/config'

  # /srv/config/
  #
  # Map the provision folder so that extensions and provisioners can access helper scripts
  config.vm.synced_folder 'provision/', '/srv/provision'

  # /srv/certificates
  #
  # This is a location for the TLS certificates to be accessible inside the VM
  config.vm.synced_folder 'certificates/', '/srv/certificates', create: true

  # /var/log/
  #
  # If a log directory exists in the same directory as your Vagrantfile, a mapped
  # directory inside the VM will be created for some generated log files.
  config.vm.synced_folder 'log/memcached', '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: ['dmode=777', 'fmode=666']
  config.vm.synced_folder 'log/nginx', '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: ['dmode=777', 'fmode=666']
  config.vm.synced_folder 'log/php', '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: ['dmode=777', 'fmode=666']
  config.vm.synced_folder 'log/provisioners', '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: ['dmode=777', 'fmode=666']

  # /srv/www/
  #
  # If a www directory exists in the same directory as your Vagrantfile, a mapped directory
  # inside the VM will be created that acts as the default location for nginx sites. Put all
  # of your project files here that you want to access through the web server
  config.vm.synced_folder 'www/', '/srv/www', owner: 'vagrant', group: 'www-data', mount_options: ['dmode=775', 'fmode=774']

  vvv_config['sites'].each do |site, args|
    next if args['skip_provisioning']
    if args['local_dir'] != File.join(vagrant_dir, 'www', site)
      config.vm.synced_folder args['local_dir'], args['vm_dir'], owner: 'vagrant', group: 'www-data', mount_options: ['dmode=775', 'fmode=774']
    end
  end

  # The Parallels Provider does not understand "dmode"/"fmode" in the "mount_options" as
  # those are specific to Virtualbox. The folder is therefore overridden with one that
  # uses corresponding Parallels mount options.
  config.vm.provider :parallels do |_v, override|
    override.vm.synced_folder 'www/', '/srv/www', owner: 'vagrant', group: 'www-data', mount_options: [ 'share' ]

    override.vm.synced_folder 'log/memcached', '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: [ 'share' ]
    override.vm.synced_folder 'log/nginx', '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: [ 'share' ]
    override.vm.synced_folder 'log/php', '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: [ 'share' ]
    override.vm.synced_folder 'log/provisioners', '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: [ 'share' ]

    if use_db_share == true
      # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 112, group: 115, mount_options: [ 'share' ]
    end

    vvv_config['sites'].each do |site, args|
      next if args['skip_provisioning']
      if args['local_dir'] != File.join(vagrant_dir, 'www', site)
        override.vm.synced_folder args['local_dir'], args['vm_dir'], owner: 'vagrant', group: 'www-data', mount_options: [ 'share' ]
      end
    end
  end

  # The Hyper-V Provider does not understand "dmode"/"fmode" in the "mount_options" as
  # those are specific to Virtualbox. Furthermore, the normal shared folders need to be
  # replaced with SMB shares. Here we switch all the shared folders to us SMB and then
  # override the www folder with options that make it Hyper-V compatible.
  config.vm.provider :hyperv do |v, override|
    v.vmname = File.basename(vagrant_dir) + '_' + (Digest::SHA256.hexdigest vagrant_dir)[0..10]

    override.vm.synced_folder 'www/', '/srv/www', owner: 'vagrant', group: 'www-data', mount_options: ['dir_mode=0775', 'file_mode=0774']

    if use_db_share == true
      # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 112, group: 115, mount_options: ['dir_mode=0775', 'file_mode=0664']
    end

    override.vm.synced_folder 'log/memcached', '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: ['dir_mode=0777', 'file_mode=0666']
    override.vm.synced_folder 'log/nginx', '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: ['dir_mode=0777', 'file_mode=0666']
    override.vm.synced_folder 'log/php', '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: ['dir_mode=0777', 'file_mode=0666']
    override.vm.synced_folder 'log/provisioners', '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: ['dir_mode=0777', 'file_mode=0666']

    vvv_config['sites'].each do |site, args|
      next if args['skip_provisioning']
      if args['local_dir'] != File.join(vagrant_dir, 'www', site)
        override.vm.synced_folder args['local_dir'], args['vm_dir'], owner: 'vagrant', group: 'www-data', mount_options: ['dir_mode=0775', 'file_mode=0774']
      end
    end
  end

  # The VMware Provider does not understand "dmode"/"fmode" in the "mount_options" as
  # those are specific to Virtualbox. The folder is therefore overridden with one that
  # uses corresponding VMware mount options.
  config.vm.provider :vmware_desktop do |_v, override|
    override.vm.synced_folder 'www/', '/srv/www', owner: 'vagrant', group: 'www-data', mount_options: ['umask=002']

    override.vm.synced_folder 'log/memcached', '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: ['umask=000']
    override.vm.synced_folder 'log/nginx', '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: ['umask=000']
    override.vm.synced_folder 'log/php', '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: ['umask=000']
    override.vm.synced_folder 'log/provisioners', '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: ['umask=000']

    if use_db_share == true
      # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 112, group: 115, mount_options: ['umask=000']
    end

    vvv_config['sites'].each do |site, args|
      next if args['skip_provisioning']
      if args['local_dir'] != File.join(vagrant_dir, 'www', site)
        override.vm.synced_folder args['local_dir'], args['vm_dir'], owner: 'vagrant', group: 'www-data', mount_options: ['umask=002']
      end
    end
  end

  # Customfile - POSSIBLY UNSTABLE
  #
  # Use this to insert your own additional Vagrant config lines. Helpful
  # for mapping additional drives. If a file 'Customfile' exists in the same directory
  # as this Vagrantfile, it will be evaluated as ruby inline as it loads.
  #
  # Note that if you find yourself using a Customfile for anything crazy or specifying
  # different provisioning, then you may want to consider a new Vagrantfile entirely.
  if File.exist?(File.join(vagrant_dir, 'Customfile'))
    puts " ⚠ ! Running additional Vagrant code in Customfile located at #{File.join(vagrant_dir, 'Customfile')}\n"
    puts " ⚠ ! Official support is not provided for this feature, it is assumed you are proficient with vagrant\n\n"
    eval(IO.read(File.join(vagrant_dir, 'Customfile')), binding)
    puts " ⚠ ! Finished running Customfile, resuming normal vagrantfile execution\n\n"
  end

  vvv_config['sites'].each do |site, args|
    next unless args['allow_customfile']

    paths = Dir[File.join(args['local_dir'], '**', 'Customfile')]
    paths.each do |file|
      puts " ⚠ ! Running additional site customfile at #{file}\n"
      puts " ⚠ ! Official support is not provided for this feature.\n\n"
      eval(IO.read(file), binding)
      puts " ⚠ ! Finished running Customfile, resuming normal vagrantfile execution\n\n"
    end
  end

  # Provisioning
  #
  # Process one or more provisioning scripts depending on the existence of custom files.

  unless Vagrant::Util::Platform.windows?
    if Process.uid == 0
      # the VM should know if vagrant was ran by a root user or using sudo
      config.vm.provision "flag-root-vagrant-command", type: 'shell', keep_color: true, inline: "mkdir -p /vagrant && touch /vagrant/provisioned_as_root"
    end
  end

  long_provision_bear = <<~HTML
  #{VVV::SplashScreens::C_BLUE}#{VVV::SplashScreens::C_RESET}
  #{VVV::SplashScreens::C_BLUE}    ▄▀▀▀▄▄▄▄▄▄▄▀▀▀▄    ▄   ▄    #{VVV::SplashScreens::C_GREEN}A full provision will take a bit.#{VVV::SplashScreens::C_RESET}
  #{VVV::SplashScreens::C_BLUE}    █▒▒░░░░░░░░░▒▒█   █   █     #{VVV::SplashScreens::C_GREEN}Sit back, relax, and have some tea.#{VVV::SplashScreens::C_RESET}
  #{VVV::SplashScreens::C_BLUE}     █░░█░░░░░█░░█   ▀   ▀      #{VVV::SplashScreens::C_RESET}
  #{VVV::SplashScreens::C_BLUE}  ▄▄  █░░░▀█▀░░░█   █▀▀▀▀▀▀█    #{VVV::SplashScreens::C_GREEN}If you didn't want to provision you can#{VVV::SplashScreens::C_RESET}
  #{VVV::SplashScreens::C_BLUE} █░░█ ▀▄░░░░░░░▄▀▄▀▀█      █    #{VVV::SplashScreens::C_GREEN}turn VVV on with 'vagrant up'.#{VVV::SplashScreens::C_RESET}
  #{VVV::SplashScreens::C_BLUE}───────────────────────────────────────────────────────────────────────#{VVV::SplashScreens::C_RESET}
  HTML

  # Changed the message here because it's going to show the first time you do vagrant up, which might be confusing
  config.vm.provision "pre-provision-script", type: 'shell', keep_color: true, inline: "echo \"#{long_provision_bear}\""

  # provison-pre.sh acts as a pre-hook to our default provisioning script. Anything that
  # should run before the shell commands laid out in provision.sh (or your provision-custom.sh
  # file) should go in this script. If it does not exist, no extra provisioning will run.
  if File.exist?(File.join(vagrant_dir, 'provision', 'provision-pre.sh'))
    config.vm.provision 'pre', type: 'shell', keep_color: true, path: File.join('provision', 'provision-pre.sh'), env: { "VVV_LOG" => "pre" }
  end

  # provision.sh or provision-custom.sh
  #
  # By default, Vagrantfile is set to use the provision.sh bash script located in the
  # provision directory. If it is detected that a provision-custom.sh script has been
  # created, that is run as a replacement. This is an opportunity to replace the entirety
  # of the provisioning provided by default.
  if File.exist?(File.join(vagrant_dir, 'provision', 'provision-custom.sh'))
    config.vm.provision 'custom', type: 'shell', keep_color: true, path: File.join('provision', 'provision-custom.sh'), env: { "VVV_LOG" => "main-custom" }
  else
    config.vm.provision 'default', type: 'shell', keep_color: true, path: File.join('provision', 'provision.sh'), env: { "VVV_LOG" => "main" }
  end

  config.vm.provision 'tools', type: 'shell', keep_color: true, path: File.join('provision', 'provision-tools.sh'), env: { "VVV_LOG" => "tools" }

  # Provision the dashboard that appears when you visit vvv.test
  config.vm.provision 'dashboard',
                      type: 'shell',
                      keep_color: true,
                      path: File.join('provision', 'provision-dashboard.sh'),
                      args: [
                        vvv_config['dashboard']['repo'],
                        vvv_config['dashboard']['branch']
                      ],
                      env: { "VVV_LOG" => "dashboard" }

  vvv_config['utility-sources'].each do |name, args|
    config.vm.provision "extension-source-#{name}",
                        type: 'shell',
                        keep_color: true,
                        path: File.join('provision', 'provision-extension-source.sh'),
                        args: [
                          name,
                          args['repo'].to_s,
                          args['branch']
                        ],
                        env: { "VVV_LOG" => "extension-source-#{name}" }
  end
  vvv_config['extension-sources'].each do |name, args|
    config.vm.provision "extension-source-#{name}",
                        type: 'shell',
                        keep_color: true,
                        path: File.join('provision', 'provision-extension-source.sh'),
                        args: [
                          name,
                          args['repo'].to_s,
                          args['branch']
                        ],
                        env: { "VVV_LOG" => "extension-source-#{name}" }
  end

  vvv_config['utilities'].each do |name, extensions|
    extensions = {} unless extensions.is_a? Array
    extensions.each do |extension|
      if extension == 'tideways'
        vvv_config['hosts'] += ['tideways.vvv.test']
        vvv_config['hosts'] += ['xhgui.vvv.test']
      end
      config.vm.provision "extension-#{name}-#{extension}",
                          type: 'shell',
                          keep_color: true,
                          path: File.join('provision', 'provision-extension.sh'),
                          args: [
                            name,
                            extension
                          ],
                          env: { "VVV_LOG" => "extension-#{name}-#{extension}" }
    end
  end
  vvv_config['extensions'].each do |name, extensions|
    extensions = {} unless extensions.is_a? Array
    extensions.each do |extension|
      if extension == 'tideways'
        vvv_config['hosts'] += ['tideways.vvv.test']
        vvv_config['hosts'] += ['xhgui.vvv.test']
      end
      config.vm.provision "extension-#{name}-#{extension}",
                          type: 'shell',
                          keep_color: true,
                          path: File.join('provision', 'provision-extension.sh'),
                          args: [
                            name,
                            extension
                          ],
                          env: { "VVV_LOG" => "extension-#{name}-#{extension}" }
    end
  end

  vvv_config['sites'].each do |site, args|
    next if args['skip_provisioning']

    config.vm.provision "site-#{site}",
                        type: 'shell',
                        keep_color: true,
                        path: File.join('provision', 'provision-site.sh'),
                        args: [
                          site,
                          args['repo'].to_s,
                          args['branch'],
                          args['vm_dir'],
                          args['skip_provisioning'].to_s,
                          args['nginx_upstream']
                        ],
                        env: { "VVV_LOG" => "site-#{site}" }
  end

  # provision-post.sh acts as a post-hook to the default provisioning. Anything that should
  # run after the shell commands laid out in provision.sh or provision-custom.sh should be
  # put into this file. This provides a good opportunity to install additional packages
  # without having to replace the entire default provisioning script.
  if File.exist?(File.join(vagrant_dir, 'provision', 'provision-post.sh'))
    config.vm.provision 'post', type: 'shell', keep_color: true, path: File.join('provision', 'provision-post.sh'), env: { "VVV_LOG" => "post" }
  end

  config.vm.provision "post-provision-script", type: 'shell', keep_color: true, path: File.join( 'config/homebin', 'vagrant_provision' ), env: { "VVV_LOG" => "post-provision-script" }

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

  # Vagrant Triggers
  #
  # We run various scripts on Vagrant state changes like `vagrant up`, `vagrant halt`,
  # `vagrant suspend`, and `vagrant destroy`
  #
  # These scripts are run on the host machine, so we use `vagrant ssh` to tunnel back
  # into the VM and execute things. By default, each of these scripts calls db_backup
  # to create backups of all current databases. This can be overridden with custom
  # scripting. See the individual files in config/homebin/ for details.
  unless Vagrant::Util::Platform.windows?
    if Process.uid == 0
      config.trigger.after :all do |trigger|
        trigger.name = 'Do not use sudo'
        trigger.ruby do |env,machine|
          sudo_warnings
        end
      end
    end
  end

  config.trigger.after :up do |trigger|
    trigger.name = 'VVV Post-Up'
    trigger.run_remote = { inline: '/srv/config/homebin/vagrant_up' }
    trigger.on_error = :continue
  end
  config.trigger.before :reload do |trigger|
    trigger.name = 'VVV Pre-Reload'
    trigger.run_remote = { inline: '/srv/config/homebin/vagrant_halt' }
    trigger.on_error = :continue
  end
  config.trigger.after :reload do |trigger|
    trigger.name = 'VVV Post-Reload'
    trigger.run_remote = { inline: '/srv/config/homebin/vagrant_up' }
    trigger.on_error = :continue
  end
  config.trigger.before :halt do |trigger|
    trigger.name = 'VVV Pre-Halt'
    trigger.run_remote = { inline: '/srv/config/homebin/vagrant_halt' }
    trigger.on_error = :continue
  end
  config.trigger.before :suspend do |trigger|
    trigger.name = 'VVV Pre-Suspend'
    trigger.run_remote = { inline: '/srv/config/homebin/vagrant_suspend' }
    trigger.on_error = :continue
  end
  config.trigger.before :destroy do |trigger|
    trigger.name = 'VVV Pre-Destroy'
    trigger.run_remote = { inline: '/srv/config/homebin/vagrant_destroy' }
    trigger.on_error = :continue
  end
end
