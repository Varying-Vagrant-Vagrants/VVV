# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:

VAGRANTFILE_API_VERSION = "2"

Vagrant.require_version '>= 2.2.4'
require 'yaml'
require 'fileutils'
require 'pathname'
require 'socket'

require_relative 'provision/vagrant/constants'
require_relative 'provision/vagrant/misc'
require_relative 'provision/vagrant/splash'
require_relative 'provision/vagrant/plugins'
require_relative 'provision/vagrant/networking'
require_relative 'provision/vagrant/provisioners'
require_relative 'provision/vagrant/synced_folders'

vagrant_dir = __dir__
version = vvv_version(vagrant_dir)

unless Vagrant::Util::Platform.windows?
  if Process.uid == 0
    sudo_warnings
  end
end

unless Vagrant::Util::Platform.windows?
  if Process.uid == 0
    puts " "
    puts "#{RED} ⚠ DANGER VAGRANT IS RUNNING AS ROOT/SUDO, DO NOT USE SUDO ⚠#{CRESET}"
    puts " "
  end
end

show_logo = false

# whitelist when we show the logo, else it'll show on global Vagrant commands
show_logo = true if %w[up resume status provision reload].include? ARGV[0]
show_logo = false if ENV['VVV_SKIP_LOGO']

# Show the initial splash screen
if show_logo
  vvv_show_logo_splash(vagrant_dir)
end

# Load the config file before the second section of the splash screen

# Perform file migrations from older versions
vvv_config_file = File.join(vagrant_dir, 'config/config.yml')
unless File.file?(vvv_config_file)
  old_vvv_config = File.join(vagrant_dir, 'vvv-custom.yml')
  if File.file?(old_vvv_config)
    puts "#{YELLOW}Migrating #{RED}vvv-custom.yml#{YELLOW} to #{GREEN}config/config.yml#{YELLOW}\nIMPORTANT NOTE: Make all modifications to #{GREEN}config/config.yml#{YELLOW}.#{CRESET}\n\n"
    FileUtils.mv(old_vvv_config, vvv_config_file)
  else
    puts "#{YELLOW}Copying #{RED}config/default-config.yml#{YELLOW} to #{GREEN}config/config.yml#{YELLOW}\nIMPORTANT NOTE: Make all modifications to #{GREEN}config/config.yml#{YELLOW} in future so that they are not lost when VVV updates.#{CRESET}\n\n"
    FileUtils.cp(File.join(vagrant_dir, 'config/default-config.yml'), vvv_config_file)
  end
end

old_db_backup_dir = File.join(vagrant_dir, 'database/backups/')
new_db_backup_dir = File.join(vagrant_dir, 'database/sql/backups/')
if (File.directory?(old_db_backup_dir) == true) && (File.directory?(new_db_backup_dir) == false)
  puts 'Moving db backup directory into database/sql/backups'
  FileUtils.mv(old_db_backup_dir, new_db_backup_dir)
end

begin
  vvv_config = YAML.load_file(vvv_config_file)
  unless vvv_config['sites'].is_a? Hash
    vvv_config['sites'] = {}

    puts "#{RED}config/config.yml is missing a sites section.#{CRESET}\n\n"
  end
rescue StandardError => e
  puts "#{RED}config/config.yml isn't a valid YAML file.#{CRESET}\n\n"
  puts "#{RED}VVV cannot be executed!#{CRESET}\n\n"

  warn e.message
  exit
end

vvv_config['hosts'] = [] unless vvv_config['hosts'].is_a? Hash

vvv_config['hosts'] += ['vvv.test']

vvv_config['sites'].each do |site, args|
  if args.is_a? String
    repo = args
    args = {
      'repo' => repo
    }
  end

  args = {} unless args.is_a? Hash

  defaults = {
    'repo' => false,
    'vm_dir' => "/srv/www/#{site}",
    'local_dir' => File.join(vagrant_dir, 'www', site),
    'branch' => 'master',
    'skip_provisioning' => false,
    'allow_customfile' => false,
    'nginx_upstream' => 'php',
    'hosts' => []
  }

  vvv_config['sites'][site] = defaults.merge(args)

  unless vvv_config['sites'][site]['skip_provisioning']
    site_host_paths = Dir.glob(Array.new(4) { |i| vvv_config['sites'][site]['local_dir'] + '/*' * (i + 1) + '/vvv-hosts' })
    vvv_config['sites'][site]['hosts'] += site_host_paths.map do |path|
      lines = File.readlines(path).map(&:chomp)
      lines.grep(/\A[^#]/)
    end.flatten
    if vvv_config['sites'][site]['hosts'].is_a? Array
      vvv_config['hosts'] += vvv_config['sites'][site]['hosts']
    else
      vvv_config['hosts'] += ["#{site}.test"]
    end
  end
  vvv_config['sites'][site].delete('hosts')
end

if vvv_config['extension-sources'].is_a? Hash
  vvv_config['extension-sources'].each do |name, args|
    next unless args.is_a? String

    repo = args
    args = {
      'repo' => repo,
      'branch' => 'master'
    }

    vvv_config['extension-sources'][name] = args
  end
else
  vvv_config['extension-sources'] = {}
end

vvv_config['dashboard'] = {} unless vvv_config['dashboard']
dashboard_defaults = {
  'repo' => 'https://github.com/Varying-Vagrant-Vagrants/dashboard.git',
  'branch' => 'master'
}
vvv_config['dashboard'] = dashboard_defaults.merge(vvv_config['dashboard'])

unless vvv_config['extension-sources'].key?('core')
  vvv_config['extension-sources']['core'] = {
    'repo' => 'https://github.com/Varying-Vagrant-Vagrants/vvv-utilities.git',
    'branch' => 'master'
  }
end

vvv_config['utilities'] = {} unless vvv_config['utilities'].is_a? Hash
vvv_config['utility-sources'] = {} unless vvv_config['utility-sources'].is_a? Hash
vvv_config['extension-sources'] = {} unless vvv_config['extension-sources'].is_a? Hash
vvv_config['extensions'] = {} unless vvv_config['extensions'].is_a? Hash

vvv_config['vm_config'] = {} unless vvv_config['vm_config'].is_a? Hash

vvv_config['general'] = {} unless vvv_config['general'].is_a? Hash

vm_defaults = {
  'memory' => 2048,
  'cores' => 2,
  'provider' => 'virtualbox',
  'private_network_ip' => '192.168.56.4'
}

# if Arm default to docker then parallels
if Etc.uname[:version].include? 'ARM64'
  vm_defaults['provider'] = 'docker'
  if vvv_is_parallels_present()
    vm_defaults['provider'] = 'parallels'
  end
end

vvv_config['vm_config'] = vm_defaults.merge(vvv_config['vm_config'])
vvv_config['hosts'] = vvv_config['hosts'].uniq

vvv_config['vagrant-plugins'] = {} unless vvv_config['vagrant-plugins']

# Early mapping of the hosts to be added.
vvv_config['utilities'].each do |name, extensions|
  extensions = {} unless extensions.is_a? Array
  extensions.each do |extension|
    if extension == 'tideways'
      vvv_config['hosts'] += ['tideways.vvv.test']
      vvv_config['hosts'] += ['xhgui.vvv.test']
    end
  end
end

vvv_config['extensions'].each do |name, extensions|
  extensions = {} unless extensions.is_a? Array
  extensions.each do |extension|
    if extension == 'tideways'
      vvv_config['hosts'] += ['tideways.vvv.test']
      vvv_config['hosts'] += ['xhgui.vvv.test']
    end
  end
end

# Create a global variable to use in functions and classes
$vvv_config = vvv_config

# Show the second splash screen section

if show_logo
  platform = [ Vagrant::Util::Platform.platform]
  if Vagrant::Util::Platform.windows?
    platform << 'windows '
    platform << 'wsl ' if Vagrant::Util::Platform.wsl?
    platform << 'msys ' if Vagrant::Util::Platform.msys?
    platform << 'cygwin ' if Vagrant::Util::Platform.cygwin?
    if Vagrant::Util::Platform.windows_hyperv_enabled?
      platform << 'HyperV-Enabled '
    end
    platform << 'HyperV-Admin ' if Vagrant::Util::Platform.windows_hyperv_admin?
    if Vagrant::Util::Platform.windows_admin?
      platform << 'HasWinAdminPriv '
    else
      platform << 'missingWinAdminPriv ' unless Vagrant::Util::Platform.windows_admin?
    end
  else
    platform << 'shell:' + ENV['SHELL'] if ENV['SHELL']
    platform << 'systemd ' if Vagrant::Util::Platform.systemd?
  end

  platform << 'vagrant-hostmanager' if Vagrant.has_plugin?('vagrant-hostmanager')
  platform << 'vagrant-hostsupdater' if Vagrant.has_plugin?('vagrant-hostsupdater')
  platform << 'vagrant-goodhosts' if Vagrant.has_plugin?('vagrant-goodhosts')
  platform << 'vagrant-vbguest' if Vagrant.has_plugin?('vagrant-vbguest')
  platform << 'vagrant-disksize' if Vagrant.has_plugin?('vagrant-disksize')

  platform << 'CaseSensitiveFS' if Vagrant::Util::Platform.fs_case_sensitive?
  unless Vagrant::Util::Platform.terminal_supports_colors?
    platform << 'monochrome-terminal'
  end

  if defined? vvv_config['vm_config']['wordcamp_contributor_day_box']
    if vvv_config['vm_config']['wordcamp_contributor_day_box'] == true
      platform << 'contributor_day_box'
    end
  end

  if defined? vvv_config['vm_config']['box']
    unless vvv_config['vm_config']['box'].nil?
      puts "Custom Box: Box overridden via config/config.yml , this won't take effect until a destroy + reprovision happens"
      platform << 'box_override:' + vvv_config['vm_config']['box']
    end
  end

  if defined? vvv_config['general']['db_share_type']
    if vvv_config['general']['db_share_type'] != true
      platform << 'shared_db_folder_disabled'
    else
      platform << 'shared_db_folder_enabled'
    end
  else
    platform << 'shared_db_folder_default'
  end

  provider_version = '??'

  provider_meta = nil

  case vvv_config['vm_config']['provider']
  when 'virtualbox'
    provider_meta = VagrantPlugins::ProviderVirtualBox::Driver::Meta.new()
    provider_version = provider_meta.version
  when 'parallels'
    provider_version = '?'
    if defined? VagrantPlugins::Parallels
      provider_meta = VagrantPlugins::Parallels::Driver::Meta.new()
      provider_version = provider_meta.version
    end
  when 'vmware'
    provider_version = '??'
  when 'hyperv'
    provider_version = 'n/a'
  when 'docker'
    provider_version = `docker -v`.gsub("Docker version ", "")
  else
    provider_version = '??'
  end

  splashsecond = <<~HEREDOC
    #{YELLOW}Platform: #{YELLOW}#{platform.join(' ')}
    #{GREEN}Vagrant: #{GREEN}v#{Vagrant::VERSION}, #{BLUE}#{vvv_config['vm_config']['provider']}: #{BLUE}v#{provider_version}

    #{DOCS}Docs:       #{URL}https://varyingvagrantvagrants.org/
    #{DOCS}Contribute: #{URL}https://github.com/varying-vagrant-vagrants/vvv
    #{DOCS}Dashboard:  #{URL}http://vvv.test#{CRESET}

  HEREDOC
  puts splashsecond
end

if defined? vvv_config['vm_config']['provider']
  # Override or set the vagrant provider.
  ENV['VAGRANT_DEFAULT_PROVIDER'] = vvv_config['vm_config']['provider']
end

ENV['LC_ALL'] = 'en_US.UTF-8'

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  # VirtualBox
  config.vm.provider :virtualbox do |v|
    unless Vagrant::Util::Platform.windows?
      if Process.uid == 0
        machine_id_file=Pathname.new(".vagrant/machines/default/virtualbox/id")
        unless machine_id_file.exist?()
          puts "#{RED} ⚠ DANGER VAGRANT IS RUNNING AS ROOT/SUDO, DO NOT USE SUDO ⚠#{CRESET}"
          puts " ! VVV has detected that the VM has not been created yet, and is running as root/sudo."
          puts " ! Do not use sudo with VVV, do not run VVV as a root user. Aborting."
          abort( "Aborting Vagrant command to prevent a critical mistake, do not use sudo/root with VVV." )
        end
      end
    end

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

    # Set the VM name and include a hash of the working directory, this prevents multiple
    # VVV's interfering with eachother or using the same VM.
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

  vvv_configure_plugins(config,vvv_config,vagrant_dir)

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
  config.vm.box_version = '>= 0'

  # The Parallels Provider uses a different naming scheme.
  config.vm.provider :parallels do |_v, override|
    override.vm.box = 'bento/ubuntu-24.04'

    # Pin the arm64 version of the box to a specific version we know has an arm build.
    if Etc.uname[:version].include? 'ARM64'
      override.vm.box_version = "202502.21.0"
    end
  end

  # The VMware Desktop Provider uses a different naming scheme.
  config.vm.provider :vmware_desktop do |v, override|
    override.vm.box = 'bento/ubuntu-24.04'
    v.gui = false
  end

  # Hyper-V uses a different base box.
  config.vm.provider :hyperv do |_v, override|
    # override.vm.box = 'bento/ubuntu-24.04'
    # At the time of writing no Bento box existed for Ubuntu 2024 with the Hyper-V provider,
    # so we're using the most popular box available in the box catalog as a temporary measure.
    override.vm.box = "gusztavvargadr/ubuntu-server-2404-lts"
    override.vm.box_version = ">=2404.0.2503"
  end

  # Docker use image.
  config.vm.provider :docker do |d, override|
    d.image = 'pentatonicfunk/vagrant-ubuntu-base-images:24.04'
    d.has_ssh = true
    d.ports =  [
      "80:80", # HTTP
      "443:443", # HTTPS
      "3306:3306", # MySQL
      "8025:8025", # Mailhog
      "9003:9003" # Xdebug
    ]

    ## Fix goodhosts aliases format for docker
    override.goodhosts.aliases = {
      '127.0.0.1' => vvv_config['hosts'],
      '::1' => vvv_config['hosts']
    }
  end

  # Virtualbox.
  config.vm.provider :virtualbox do |_v, override|
    # Default Ubuntu Box
    #
    # This box is provided by Bento boxes via vagrantcloud.com and is a nicely sized
    # box containing the Ubuntu LTS release. Once this box is downloaded
    # to your host computer, it is cached for future use under the specified box name.
    override.vm.box = 'bento/ubuntu-24.04'

    # If we're at a contributor day, switch the base box to the prebuilt one
    if defined? vvv_config['vm_config']['wordcamp_contributor_day_box']
      if vvv_config['vm_config']['wordcamp_contributor_day_box'] == true
        override.vm.box = 'vvv/contribute'
      end
    end
  end

  config.vm.box = vvv_config['vm_config']['box'] if vvv_config['vm_config']['box']

  if defined? vvv_config['vm_config']['box_version']
    unless vvv_config['vm_config']['box_version'].nil?
      config.vm.box_version = vvv_config['vm_config']['box_version']
    end
  end

  config.vm.hostname = 'vvv'

  # Set up Networking.
  vvv_configure_networking( config, vvv_config )

  # Drive mapping
  #
  # The following config.vm.synced_folder settings will map directories in your Vagrant
  # virtual machine to directories on your local machine. Once these are mapped, any
  # changes made to the files in these directories will affect both the local and virtual
  # machine versions. Think of it as two different ways to access the same file. When the
  # virtual machine is destroyed with `vagrant destroy`, your files will remain in your local
  # environment.

  vvv_sync_provisioner_folders(config,vvv_config,vagrant_dir)

  # /srv/www/
  #
  # If a www directory exists in the same directory as your Vagrantfile, a mapped directory
  # inside the VM will be created that acts as the default location for nginx sites. Put all
  # of your project files here that you want to access through the web server
  config.vm.synced_folder 'www/', '/srv/www', owner: 'vagrant', group: 'www-data', mount_options: MOUNT_OPTIONS[:WWW][:VIRTUALBOX]

  vvv_config['sites'].each do |site, args|
    next if args['skip_provisioning']
    if args['local_dir'] != File.join(vagrant_dir, 'www', site)
      config.vm.synced_folder args['local_dir'], args['vm_dir'], owner: 'vagrant', group: 'www-data', mount_options: MOUNT_OPTIONS[:WWW][:VIRTUALBOX]
    end
  end

  config.vm.provider :docker do |_v, override|
    override.vm.synced_folder 'www/', '/srv/www', mount_options: MOUNT_OPTIONS[:WWW][:DOCKER]

    vvv_config['sites'].each do |site, args|
      next if args['skip_provisioning']
      if args['local_dir'] != File.join(vagrant_dir, 'www', site)
        override.vm.synced_folder args['local_dir'], args['vm_dir'], mount_options: MOUNT_OPTIONS[:WWW][:DOCKER]
      end
    end
  end

  config.vm.provider :parallels do |_v, override|
    override.vm.synced_folder 'www/', '/srv/www', owner: 'vagrant', group: 'www-data', mount_options: MOUNT_OPTIONS[:WWW][:PARALLELS]

    override.vm.synced_folder LOCAL_LOG_PATHS[:memcached], '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:PARALLELS]
    override.vm.synced_folder LOCAL_LOG_PATHS[:nginx], '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:PARALLELS]
    override.vm.synced_folder LOCAL_LOG_PATHS[:php], '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:PARALLELS]
    override.vm.synced_folder LOCAL_LOG_PATHS[:provisioners], '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:PARALLELS]

    use_db_share = vvv_use_db_share(vvv_config)
    if use_db_share == true
      # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 112, group: 115, mount_options: MOUNT_OPTIONS[:MYSQL][:PARALLELS]
    end

    vvv_config['sites'].each do |site, args|
      next if args['skip_provisioning']
      if args['local_dir'] != File.join(vagrant_dir, 'www', site)
        override.vm.synced_folder args['local_dir'], args['vm_dir'], owner: 'vagrant', group: 'www-data', mount_options: MOUNT_OPTIONS[:WWW][:PARALLELS]
      end
    end
  end

  # Under Hyper-V the normal shared folders need to be replaced with SMB shares.
  # Here we switch all the shared folders to use SMB and then override the www
  # folder with options that make it Hyper-V compatible.
  config.vm.provider :hyperv do |v, override|
    v.vmname = File.basename(vagrant_dir) + '_' + (Digest::SHA256.hexdigest vagrant_dir)[0..10]

    override.vm.synced_folder 'www/', '/srv/www', owner: 'vagrant', group: 'www-data', mount_options: MOUNT_OPTIONS[:WWW][:HYPERV]

    use_db_share = vvv_use_db_share(vvv_config)
    if use_db_share == true
      # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 112, group: 115, mount_options: MOUNT_OPTIONS[:MYSQL][:HYPERV]
    end

    override.vm.synced_folder LOCAL_LOG_PATHS[:memcached], '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:HYPERV]
    override.vm.synced_folder LOCAL_LOG_PATHS[:nginx], '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:HYPERV]
    override.vm.synced_folder LOCAL_LOG_PATHS[:php], '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:HYPERV]
    override.vm.synced_folder LOCAL_LOG_PATHS[:provisioners], '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:HYPERV]

    vvv_config['sites'].each do |site, args|
      next if args['skip_provisioning']
      if args['local_dir'] != File.join(vagrant_dir, 'www', site)
        override.vm.synced_folder args['local_dir'], args['vm_dir'], owner: 'vagrant', group: 'www-data', mount_options: MOUNT_OPTIONS[:WWW][:HYPERV]
      end
    end
  end

  # Specify the VMware Provider mount options for synced folders.
  config.vm.provider :vmware_desktop do |_v, override|
    override.vm.synced_folder 'www/', '/srv/www', owner: 'vagrant', group: 'www-data', mount_options: MOUNT_OPTIONS[:WWW][:VMWARE_DESKTOP]

    override.vm.synced_folder LOCAL_LOG_PATHS[:memcached], '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VMWARE_DESKTOP]
    override.vm.synced_folder LOCAL_LOG_PATHS[:nginx], '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VMWARE_DESKTOP]
    override.vm.synced_folder LOCAL_LOG_PATHS[:php], '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VMWARE_DESKTOP]
    override.vm.synced_folder LOCAL_LOG_PATHS[:provisioners], '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VMWARE_DESKTOP]

    use_db_share = vvv_use_db_share(vvv_config)
    if use_db_share == true
      # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 112, group: 115, mount_options: MOUNT_OPTIONS[:MYSQL][:VMWARE_DESKTOP]
    end

    vvv_config['sites'].each do |site, args|
      next if args['skip_provisioning']
      if args['local_dir'] != File.join(vagrant_dir, 'www', site)
        override.vm.synced_folder args['local_dir'], args['vm_dir'], owner: 'vagrant', group: 'www-data', mount_options: MOUNT_OPTIONS[:WWW][:VMWARE_DESKTOP]
      end
    end
  end

  vvv_customfiles(vvv_config,vagrant_dir)

  # Provisioning
  #
  # Process one or more provisioning scripts depending on the existence of custom files.

  vvv_configure_main_provisioners(config, vvv_config, vagrant_dir)
  vvv_configure_extension_provisioners(config, vvv_config)
  vvv_configure_site_provisioners(config, vvv_config)

  # provision-post.sh acts as a post-hook to the default provisioning. Anything that should
  # run after the shell commands laid out in provision.sh or provision-custom.sh should be
  # put into this file. This provides a good opportunity to install additional packages
  # without having to replace the entire default provisioning script.
  if File.exist?(File.join(vagrant_dir, 'provision', 'provision-post.sh'))
    config.vm.provision 'post', type: 'shell', keep_color: true, path: File.join('provision', 'provision-post.sh'), env: { "VVV_LOG" => "post" }
  end

  config.vm.provision "post-provision-script", type: 'shell', keep_color: true, path: File.join( 'config/homebin', 'vagrant_provision' ), env: { "VVV_LOG" => "post-provision-script" }

  vvv_configure_hosts(config,vvv_config,vagrant_dir)

  vvv_triggers(config)
end
