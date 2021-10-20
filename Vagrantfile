# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:
Vagrant.require_version '>= 2.2.4'
require 'yaml'
require 'fileutils'

def sudo_warnings
  red = "\033[38;5;9m" # 124m"
  creset = "\033[0m"
  puts "#{red}┌-──────────────────────────────────────────────────────────────────────────────┐#{creset}"
  puts "#{red}│                                                                               │#{creset}"
  puts "#{red}│  ⚠ DANGER DO NOT USE SUDO ⚠                                                   │#{creset}"
  puts "#{red}│                                                                               │#{creset}"
  puts "#{red}│ ! ▄▀▀▀▄▄▄▄▄▄▄▀▀▀▄ !  You should never use sudo or root with vagrant.          │#{creset}"
  puts "#{red}│  !█▒▒░░░░░░░░░▒▒█    It causes lots of problems :(                            │#{creset}"
  puts "#{red}│    █░░█░▄▄░░█░░█ !                                                            │#{creset}"
  puts "#{red}│     █░░█░░█░▄▄█    ! We're really sorry but you may need to do painful        │#{creset}"
  puts "#{red}│  !  ▀▄░█░░██░░█      cleanup commands to fix this.                            │#{creset}"
  puts "#{red}│                                                                               │#{creset}"
  puts "#{red}│  If vagrant does not work for you without sudo, open a GitHub issue instead   │#{creset}"
  puts "#{red}│  In the future, this warning will halt provisioning to prevent new users      │#{creset}"
  puts "#{red}│  making this mistake.                                                         │#{creset}"
  puts "#{red}│                                                                               │#{creset}"
  puts "#{red}│  ⚠ DANGER SUDO DETECTED!                                                      │#{creset}"
  puts "#{red}│                                                                               │#{creset}"
  puts "#{red}│  In the future the VVV team will be making it harder to use VVV with sudo.    │#{creset}"
  puts "#{red}│  We will require a config option so that users can do data recovery, and      │#{creset}"
  puts "#{red}│  disable sites and the dashboard.                                             │#{creset}"
  puts "#{red}│                                                                               │#{creset}"
  puts "#{red}│  DO NOT USE SUDO, use ctrl+c/cmd+c and cancel this command ASAP!!!            │#{creset}"
  puts "#{red}│                                                                               │#{creset}"
  puts "#{red}└───────────────────────────────────────────────────────────────────────────────┘#{creset}"
  # exit
end

vagrant_dir = __dir__
show_logo = false
branch_c = "\033[38;5;6m" # 111m"
red = "\033[38;5;9m" # 124m"
green = "\033[1;38;5;2m" # 22m"
blue = "\033[38;5;4m" # 33m"
purple = "\033[38;5;5m" # 129m"
docs = "\033[0m"
yellow = "\033[38;5;3m" # 136m"
yellow_underlined = "\033[4;38;5;3m" # 136m"
url = yellow_underlined
creset = "\033[0m"

version = '?'
File.open("#{vagrant_dir}/version", 'r') do |f|
  version = f.read
  version = version.gsub("\n", '')
end

unless Vagrant::Util::Platform.windows?
  if Process.uid == 0
    sudo_warnings
  end
end

unless Vagrant::Util::Platform.windows?
  if Process.uid == 0
    puts " "
    puts "#{red} ⚠ DANGER VAGRANT IS RUNNING AS ROOT/SUDO, DO NOT USE SUDO ⚠#{creset}"
    puts " "
  end
end

# whitelist when we show the logo, else it'll show on global Vagrant commands
show_logo = true if %w[up resume status provision reload].include? ARGV[0]
show_logo = false if ENV['VVV_SKIP_LOGO']

# Show the initial splash screen
if show_logo
  git_or_zip = 'zip-no-vcs'
  branch = ''
  commit = ''
  if File.directory?("#{vagrant_dir}/.git")
    git_or_zip = 'git::'
    branch = `git --git-dir="#{vagrant_dir}/.git" --work-tree="#{vagrant_dir}" rev-parse --abbrev-ref HEAD`
    branch = branch.chomp("\n"); # remove trailing newline so it doesn't break the ascii art
    commit = `git --git-dir="#{vagrant_dir}/.git" --work-tree="#{vagrant_dir}" rev-parse --short HEAD`
    commit = '(' + commit.chomp("\n") + ')'; # remove trailing newline so it doesn't break the ascii art
  end

  splashfirst = <<~HEREDOC
    \033[1;38;5;196m#{red}__ #{green}__ #{blue}__ __
    #{red}\\ V#{green}\\ V#{blue}\\ V / #{purple}v#{version} #{purple}Path:"#{vagrant_dir}"
    #{red} \\_/#{green}\\_/#{blue}\\_/  #{creset}#{branch_c}#{git_or_zip}#{branch}#{commit}#{creset}

  HEREDOC
  puts splashfirst
end

# Load the config file before the second section of the splash screen

# Perform file migrations from older versions
vvv_config_file = File.join(vagrant_dir, 'config/config.yml')
unless File.file?(vvv_config_file)
  old_vvv_config = File.join(vagrant_dir, 'vvv-custom.yml')
  if File.file?(old_vvv_config)
    puts "#{yellow}Migrating #{red}vvv-custom.yml#{yellow} to #{green}config/config.yml#{yellow}\nIMPORTANT NOTE: Make all modifications to #{green}config/config.yml#{yellow}.#{creset}\n\n"
    FileUtils.mv(old_vvv_config, vvv_config_file)
  else
    puts "#{yellow}Copying #{red}config/default-config.yml#{yellow} to #{green}config/config.yml#{yellow}\nIMPORTANT NOTE: Make all modifications to #{green}config/config.yml#{yellow} in future so that they are not lost when VVV updates.#{creset}\n\n"
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

    puts "#{red}config/config.yml is missing a sites section.#{creset}\n\n"
  end
rescue StandardError => e
  puts "#{red}config/config.yml isn't a valid YAML file.#{creset}\n\n"
  puts "#{red}VVV cannot be executed!#{creset}\n\n"

  warn e.message
  exit
end

vvv_config['hosts'] = [] unless vvv_config['hosts'].is_a? Hash

vvv_config['hosts'] += ['vvv.test']

vvv_config['sites'].each do |site, args|
  if args.is_a? String
    repo = args
    args = {}
    args['repo'] = repo
  end

  args = {} unless args.is_a? Hash

  defaults = {}
  defaults['repo'] = false
  defaults['vm_dir'] = "/srv/www/#{site}"
  defaults['local_dir'] = File.join(vagrant_dir, 'www', site)
  defaults['branch'] = 'master'
  defaults['skip_provisioning'] = false
  defaults['allow_customfile'] = false
  defaults['nginx_upstream'] = 'php'
  defaults['hosts'] = []

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

if vvv_config['utility-sources'].is_a? Hash
  vvv_config['utility-sources'].each do |name, args|
    next unless args.is_a? String

    repo = args
    args = {}
    args['repo'] = repo
    args['branch'] = 'master'

    vvv_config['utility-sources'][name] = args
  end
else
  vvv_config['utility-sources'] = {}
end

vvv_config['dashboard'] = {} unless vvv_config['dashboard']
dashboard_defaults = {}
dashboard_defaults['repo'] = 'https://github.com/Varying-Vagrant-Vagrants/dashboard.git'
dashboard_defaults['branch'] = 'master'
vvv_config['dashboard'] = dashboard_defaults.merge(vvv_config['dashboard'])

unless vvv_config['utility-sources'].key?('core')
  vvv_config['utility-sources']['core'] = {}
  vvv_config['utility-sources']['core']['repo'] = 'https://github.com/Varying-Vagrant-Vagrants/vvv-utilities.git'
  vvv_config['utility-sources']['core']['branch'] = 'master'
end

vvv_config['utilities'] = {} unless vvv_config['utilities'].is_a? Hash

vvv_config['vm_config'] = {} unless vvv_config['vm_config'].is_a? Hash

vvv_config['general'] = {} unless vvv_config['general'].is_a? Hash

defaults = {}
defaults['memory'] = 2048
defaults['cores'] = 1
defaults['provider'] = 'virtualbox'
# This should rarely be overridden, so it's not included in the config/default-config.yml file.
defaults['private_network_ip'] = '192.168.50.4'

vvv_config['vm_config'] = defaults.merge(vvv_config['vm_config'])
vvv_config['hosts'] = vvv_config['hosts'].uniq

vvv_config['vagrant-plugins'] = {} unless vvv_config['vagrant-plugins']

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
    provider_meta = VagrantPlugins::Parallels::Driver::Meta.new()
    provider_version = provider_meta.version
  when 'vmware'
    provider_version = '??'
  when 'hyperv'
    provider_version = 'n/a'
  else
    provider_version = '??'
  end

  splashsecond = <<~HEREDOC
    #{yellow}Platform: #{yellow}#{platform.join(' ')}
    #{green}Vagrant: #{green}v#{Vagrant::VERSION}, #{blue}#{vvv_config['vm_config']['provider']}: #{blue}v#{provider_version}

    #{docs}Docs:       #{url}https://varyingvagrantvagrants.org/
    #{docs}Contribute: #{url}https://github.com/varying-vagrant-vagrants/vvv
    #{docs}Dashboard:  #{url}http://vvv.test#{creset}

  HEREDOC
  puts splashsecond
end

if defined? vvv_config['vm_config']['provider']
  # Override or set the vagrant provider.
  ENV['VAGRANT_DEFAULT_PROVIDER'] = vvv_config['vm_config']['provider']
end

ENV['LC_ALL'] = 'en_US.UTF-8'

Vagrant.configure('2') do |config|
  # Store the current version of Vagrant for use in conditionals when dealing
  # with possible backward compatible issues.
  vagrant_version = Vagrant::VERSION.sub(/^v/, '')

  # Configurations from 1.0.x can be placed in Vagrant 1.1.x specs like the following.
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
    v.update_guest_tools = true
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
    v.enable_virtualization_extensions = true
    v.linked_clone = true
  end

  # Auto Download Vagrant plugins, supported from Vagrant 2.2.0
  unless Vagrant.has_plugin?('vagrant-hostsupdater') && Vagrant.has_plugin?('vagrant-goodhosts') && Vagrant.has_plugin?('vagrant-hostsmanager')
    if File.file?(File.join(vagrant_dir, 'vagrant-goodhosts.gem'))
      system('vagrant plugin install ' + File.join(vagrant_dir, 'vagrant-goodhosts.gem'))
      File.delete(File.join(vagrant_dir, 'vagrant-goodhosts.gem'))
      puts "#{yellow}VVV needed to install the vagrant-goodhosts plugin which is now installed. Please run the requested command again.#{creset}"
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

  # Default Ubuntu Box
  #
  # This box is provided by Bento boxes via vagrantcloud.com and is a nicely sized
  # box containing the Ubuntu 20.04 Focal 64 bit release. Once this box is downloaded
  # to your host computer, it is cached for future use under the specified box name.
  config.vm.box_check_update = false

  # If we're at a contributor day, switch the base box to the prebuilt one
  if defined? vvv_config['vm_config']['wordcamp_contributor_day_box']
    if vvv_config['vm_config']['wordcamp_contributor_day_box'] == true
      config.vm.box = 'vvv/contribute'
    end
  end

  # The Parallels Provider uses a different naming scheme.
  config.vm.provider :parallels do |_v, override|
    override.vm.box = 'bento/ubuntu-20.04'

    # Vagrant currently runs under Rosetta on M1 devices. As a result,
    # this seems to be the most reliable way to detect whether or not we're
    # running under ARM64.
    if Etc.uname[:version].include? 'ARM64'
      override.vm.box = 'rueian/ubuntu20-m1'
      override.vm.box_version = "0.0.1"
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

  # virtualbox
  config.vm.provider :virtualbox do |_v, override|
    override.vm.box = 'bento/ubuntu-20.04'
  end

  # Docker use image.
  config.vm.provider :docker do |d|
    d.image = 'pentatonicfunk/vagrant-ubuntu-base-images:20.04'
    d.has_ssh = true
    if Vagrant::Util::Platform.platform == 'darwin19'
        # Docker in mac need explicit ports publish to access
        d.ports = [ "#{vvv_config['vm_config']['private_network_ip']}:80:80" ]
        d.ports += [ "#{vvv_config['vm_config']['private_network_ip']}:443:443" ]
        d.ports += [ "#{vvv_config['vm_config']['private_network_ip']}:3306:3306" ]
        d.ports += [ "#{vvv_config['vm_config']['private_network_ip']}:8025:8025" ]
        d.ports += [ "#{vvv_config['vm_config']['private_network_ip']}:1025:1025" ]
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
  # Map the provision folder so that utilities and provisioners can access helper scripts
  config.vm.synced_folder 'provision/', '/srv/provision'

  # /srv/certificates
  #
  # This is a location for the TLS certificates to be accessible inside the VM
  config.vm.synced_folder 'certificates/', '/srv/certificates', create: true

  # /var/log/
  #
  # If a log directory exists in the same directory as your Vagrantfile, a mapped
  # directory inside the VM will be created for some generated log files.
  config.vm.synced_folder 'log/memcached', '/var/log/memcached', owner: 'root', create: true, group: 'syslog', mount_options: ['dmode=777', 'fmode=666']
  config.vm.synced_folder 'log/nginx', '/var/log/nginx', owner: 'root', create: true, group: 'syslog', mount_options: ['dmode=777', 'fmode=666']
  config.vm.synced_folder 'log/php', '/var/log/php', create: true, owner: 'root', group: 'syslog', mount_options: ['dmode=777', 'fmode=666']
  config.vm.synced_folder 'log/provisioners', '/var/log/provisioners', create: true, owner: 'root', group: 'syslog', mount_options: ['dmode=777', 'fmode=666']

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

    override.vm.synced_folder 'log/memcached', '/var/log/memcached', owner: 'root', create: true, group: 'syslog', mount_options: [ 'share' ]
    override.vm.synced_folder 'log/nginx', '/var/log/nginx', owner: 'root', create: true, group: 'syslog', mount_options: [ 'share' ]
    override.vm.synced_folder 'log/php', '/var/log/php', create: true, owner: 'root', group: 'syslog', mount_options: [ 'share' ]
    override.vm.synced_folder 'log/provisioners', '/var/log/provisioners', create: true, owner: 'root', group: 'syslog', mount_options: [ 'share' ]

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

    override.vm.synced_folder 'log/memcached', '/var/log/memcached', owner: 'root', create: true, group: 'syslog', mount_options: ['dir_mode=0777', 'file_mode=0666']
    override.vm.synced_folder 'log/nginx', '/var/log/nginx', owner: 'root', create: true, group: 'syslog', mount_options: ['dir_mode=0777', 'file_mode=0666']
    override.vm.synced_folder 'log/php', '/var/log/php', create: true, owner: 'root', group: 'syslog', mount_options: ['dir_mode=0777', 'file_mode=0666']
    override.vm.synced_folder 'log/provisioners', '/var/log/provisioners', create: true, owner: 'root', group: 'syslog', mount_options: ['dir_mode=0777', 'file_mode=0666']

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

    override.vm.synced_folder 'log/memcached', '/var/log/memcached', owner: 'root', create: true, group: 'syslog', mount_options: ['umask=000']
    override.vm.synced_folder 'log/nginx', '/var/log/nginx', owner: 'root', create: true, group: 'syslog', mount_options: ['umask=000']
    override.vm.synced_folder 'log/php', '/var/log/php', create: true, owner: 'root', group: 'syslog', mount_options: ['umask=000']
    override.vm.synced_folder 'log/provisioners', '/var/log/provisioners', create: true, owner: 'root', group: 'syslog', mount_options: ['umask=000']

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
  #{blue}#{creset}
  #{blue}    ▄▀▀▀▄▄▄▄▄▄▄▀▀▀▄    ▄   ▄    #{green}A full provision will take a bit.#{creset}
  #{blue}    █▒▒░░░░░░░░░▒▒█   █   █     #{green}Sit back, relax, and have some tea.#{creset}
  #{blue}     █░░█░░░░░█░░█   ▀   ▀      #{creset}
  #{blue}  ▄▄  █░░░▀█▀░░░█   █▀▀▀▀▀▀█    #{green}If you didn't want to provision you can#{creset}
  #{blue} █░░█ ▀▄░░░░░░░▄▀▄▀▀█      █    #{green}turn VVV on with 'vagrant up'.#{creset}
  #{blue}───────────────────────────────────────────────────────────────────────#{creset}
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
    config.vm.provision "utility-source-#{name}",
                        type: 'shell',
                        keep_color: true,
                        path: File.join('provision', 'provision-utility-source.sh'),
                        args: [
                          name,
                          args['repo'].to_s,
                          args['branch']
                        ],
                        env: { "VVV_LOG" => "utility-source-#{name}" }
  end

  vvv_config['utilities'].each do |name, utilities|
    utilities = {} unless utilities.is_a? Array
    utilities.each do |utility|
      if utility == 'tideways'
        vvv_config['hosts'] += ['tideways.vvv.test']
        vvv_config['hosts'] += ['xhgui.vvv.test']
      end
      config.vm.provision "utility-#{name}-#{utility}",
                          type: 'shell',
                          keep_color: true,
                          path: File.join('provision', 'provision-utility.sh'),
                          args: [
                            name,
                            utility
                          ],
                          env: { "VVV_LOG" => "utility-#{name}-#{utility}" }
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

  if Vagrant.has_plugin?('vagrant-goodhosts')
    config.goodhosts.aliases = vvv_config['hosts']
    config.goodhosts.remove_on_suspend = true
  elsif Vagrant.has_plugin?('vagrant-hostsmanager')
    config.hostmanager.aliases = vvv_config['hosts']
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
    config.hostmanager.manage_guest = true
    config.hostmanager.ignore_private_ip = false
    config.hostmanager.include_offline = true
  elsif Vagrant.has_plugin?('vagrant-hostsupdater')
    # Pass the found host names to the hostsupdater plugin so it can perform magic.
    config.hostsupdater.aliases = vvv_config['hosts']
    config.hostsupdater.remove_on_suspend = true
  else
    show_check = true if %w[up halt resume suspend status provision reload].include? ARGV[0]
    if show_check
      puts ""
      puts " X ! There is no hosts file vagrant plugin installed!"
      puts " X You need the vagrant-goodhosts plugin (or HostManager/ HostsUpdater ) for domains to work in the browser"
      puts " X Run 'vagrant plugin install --local' to fix this."
      puts ""
    end
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

  # specific trigger for mac and docker
  if Vagrant::Util::Platform.platform == 'darwin19' && vvv_config['vm_config']['provider'] == 'docker'
    config.trigger.before :up do |trigger|
      trigger.name = "VVV Setup docker local network before up"
      trigger.run = {inline: "bash -c 'sudo ifconfig lo0 alias #{vvv_config['vm_config']['private_network_ip']}/24'"}
    end
    config.trigger.after :halt do |trigger|
      trigger.name = 'VVV delete docker local network after halt'
      trigger.run = {inline: "bash -c 'sudo ifconfig lo0 inet delete #{vvv_config['vm_config']['private_network_ip']}'"}
      trigger.on_error = :continue
    end
    config.trigger.after :destroy do |trigger|
      trigger.name = 'VVV delete docker local network after destroy'
      trigger.run = {inline: "bash -c 'sudo ifconfig lo0 inet delete #{vvv_config['vm_config']['private_network_ip']}'"}
      trigger.on_error = :continue
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
