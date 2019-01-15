# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:
Vagrant.require_version ">= 2.1.4"
require 'yaml'
require 'fileutils'


def virtualbox_version()
    vboxmanage = Vagrant::Util::Which.which("VBoxManage") || Vagrant::Util::Which.which("VBoxManage.exe")
    if vboxmanage != nil
        s = Vagrant::Util::Subprocess.execute(vboxmanage, '--version')
        return s.stdout.strip!
    else
        return 'unknown'
    end
end

vagrant_dir = File.expand_path(File.dirname(__FILE__))
show_logo = false
branch_c = "\033[38;5;6m"#111m"
red="\033[38;5;9m"#124m"
green="\033[1;38;5;2m"#22m"
blue="\033[38;5;4m"#33m"
purple="\033[38;5;5m"#129m"
docs="\033[0m"
yellow="\033[38;5;3m"#136m"
yellow_underlined="\033[4;38;5;3m"#136m"
url=yellow_underlined
creset="\033[0m"
versionfile = File.open("#{vagrant_dir}/version", "r")
version = versionfile.read

# whitelist when we show the logo, else it'll show on global Vagrant commands
if [ 'up', 'halt', 'resume', 'suspend', 'status', 'provision', 'reload' ].include? ARGV[0] then
  show_logo = true
end
if ENV['VVV_SKIP_LOGO'] then
  show_logo = false
end
if show_logo then

  platform = 'platform-' + Vagrant::Util::Platform.platform + ' '
  if Vagrant::Util::Platform.windows? then
    platform = platform + 'windows '
    if Vagrant::Util::Platform.wsl? then
      platform = platform + 'wsl '
    end
    if Vagrant::Util::Platform.msys? then
      platform = platform + 'msys '
    end
    if Vagrant::Util::Platform.cygwin? then
      platform = platform + 'cygwin '
    end
    if Vagrant::Util::Platform.windows_hyperv_enabled? then
      platform = platform + 'HyperV-Enabled '
    end
    if Vagrant::Util::Platform.windows_hyperv_admin? then
      platform = platform + 'HyperV-Admin '
    end
    if Vagrant::Util::Platform.windows_admin? then
      platform = platform + 'HasWinAdminPriv '
    end
  else

    if ENV['SHELL'] then
      platform = platform + "shell:" + ENV['SHELL'] + ' '
    end
    if Vagrant::Util::Platform.systemd? then
      platform = platform + 'systemd '
    end
  end

  if Vagrant.has_plugin?('vagrant-hostsupdater') then
    platform = platform + 'vagrant-hostsupdater '
  end

  if Vagrant.has_plugin?('vagrant-vbguest') then
    platform = platform + 'vagrant-vbguest '
  end

  if Vagrant::Util::Platform.fs_case_sensitive? then
    platform = platform + 'CaseSensitiveFS '
  end
  if ! Vagrant::Util::Platform.terminal_supports_colors? then
    platform = platform + 'NoColour '
  end

  git_or_zip = "zip-no-vcs"
  branch = ''
  if File.directory?("#{vagrant_dir}/.git") then
    git_or_zip = "git::"
    branch = `git --git-dir="#{vagrant_dir}/.git" --work-tree="#{vagrant_dir}" rev-parse --abbrev-ref HEAD`
    branch = branch.chomp("\n"); # remove trailing newline so it doesnt break the ascii art
  end

  splash = <<-HEREDOC
\033[1;38;5;196m#{red}__ #{green}__ #{blue}__ __
#{red}\\ V#{green}\\ V#{blue}\\ V / #{red}Varying #{green}Vagrant #{blue}Vagrants
#{red} \\_/#{green}\\_/#{blue}\\_/  #{purple}v#{version}#{creset}-#{branch_c}#{git_or_zip}#{branch}

#{yellow}Platform:   #{yellow}#{platform}
#{green}Vagrant:    #{green}#{Vagrant::VERSION}
#{blue}VirtualBox: #{blue}#{virtualbox_version()}

#{docs}Docs:       #{url}https://varyingvagrantvagrants.org/
#{docs}Contribute: #{url}https://github.com/varying-vagrant-vagrants/vvv
#{docs}Dashboard:  #{url}http://vvv.test#{creset}

  HEREDOC
  puts splash
end

if File.file?(File.join(vagrant_dir, 'vvv-custom.yml')) == false then
  puts "#{yellow}Copying #{red}vvv-config.yml#{yellow} to #{green}vvv-custom.yml#{yellow}\nIMPORTANT NOTE: Make all modifications to #{green}vvv-custom.yml#{yellow} in future so that they are not lost when VVV updates.#{creset}\n\n"
  FileUtils.cp( File.join(vagrant_dir, 'vvv-config.yml'), File.join(vagrant_dir, 'vvv-custom.yml') )
end

vvv_config_file = File.join(vagrant_dir, 'vvv-custom.yml')

vvv_config = YAML.load_file(vvv_config_file)

if ! vvv_config['sites'].kind_of? Hash then
  vvv_config['sites'] = Hash.new
end

if ! vvv_config['hosts'].kind_of? Hash then
  vvv_config['hosts'] = Array.new
end

vvv_config['hosts'] += ['vvv.dev'] # Deprecated
vvv_config['hosts'] += ['vvv.test']
vvv_config['hosts'] += ['vvv.local']
vvv_config['hosts'] += ['vvv.localhost']

vvv_config['sites'].each do |site, args|
  if args.kind_of? String then
      repo = args
      args = Hash.new
      args['repo'] = repo
  end

  if ! args.kind_of? Hash then
      args = Hash.new
  end

  defaults = Hash.new
  defaults['repo']   = false
  defaults['vm_dir'] = "/srv/www/#{site}"
  defaults['local_dir'] = File.join(vagrant_dir, 'www', site)
  defaults['branch'] = 'master'
  defaults['skip_provisioning'] = false
  defaults['allow_customfile'] = false
  defaults['nginx_upstream'] = 'php'
  defaults['hosts'] = Array.new

  vvv_config['sites'][site] = defaults.merge(args)

  if ! vvv_config['sites'][site]['skip_provisioning'] then
    site_host_paths = Dir.glob(Array.new(4) {|i| vvv_config['sites'][site]['local_dir'] + '/*'*(i+1) + '/vvv-hosts'})
    vvv_config['sites'][site]['hosts'] += site_host_paths.map do |path|
      lines = File.readlines(path).map(&:chomp)
      lines.grep(/\A[^#]/)
    end.flatten

    vvv_config['hosts'] += vvv_config['sites'][site]['hosts']
  end
  vvv_config['sites'][site].delete('hosts')
end

if ! vvv_config['utility-sources'].kind_of? Hash then
  vvv_config['utility-sources'] = Hash.new
else
  vvv_config['utility-sources'].each do |name, args|
    if args.kind_of? String then
        repo = args
        args = Hash.new
        args['repo'] = repo
        args['branch'] = 'master'

        vvv_config['utility-sources'][name] = args
    end
  end
end

if ! vvv_config['dashboard']
  vvv_config['dashboard'] = Hash.new
end
dashboard_defaults = Hash.new
dashboard_defaults['repo'] = 'https://github.com/Varying-Vagrant-Vagrants/dashboard.git'
dashboard_defaults['branch'] = 'master'
vvv_config['dashboard'] = dashboard_defaults.merge(vvv_config['dashboard'])

if ! vvv_config['utility-sources'].key?('core')
  vvv_config['utility-sources']['core'] = Hash.new
  vvv_config['utility-sources']['core']['repo'] = 'https://github.com/Varying-Vagrant-Vagrants/vvv-utilities.git'
  vvv_config['utility-sources']['core']['branch'] = 'master'
end

if ! vvv_config['utilities'].kind_of? Hash then
  vvv_config['utilities'] = Hash.new
end

if ! vvv_config['vm_config'].kind_of? Hash then
  vvv_config['vm_config'] = Hash.new
end

defaults = Hash.new
defaults['memory'] = 2048
defaults['cores'] = 1
# This should rarely be overridden, so it's not included in the default vvv-config.yml file.
defaults['private_network_ip'] = '192.168.50.4'

vvv_config['vm_config'] = defaults.merge(vvv_config['vm_config'])

if defined? vvv_config['vm_config']['provider'] then
  # Override or set the vagrant provider.
  ENV['VAGRANT_DEFAULT_PROVIDER'] = vvv_config['vm_config']['provider']
end

vvv_config['hosts'] = vvv_config['hosts'].uniq

Vagrant.configure("2") do |config|

  # Store the current version of Vagrant for use in conditionals when dealing
  # with possible backward compatible issues.
  vagrant_version = Vagrant::VERSION.sub(/^v/, '')

  # Configurations from 1.0.x can be placed in Vagrant 1.1.x specs like the following.
  config.vm.provider :virtualbox do |v|
    v.customize ["modifyvm", :id, "--memory", vvv_config['vm_config']['memory']]
    v.customize ["modifyvm", :id, "--cpus", vvv_config['vm_config']['cores']]
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--natdnsproxy1", "on"]

    # see https://github.com/hashicorp/vagrant/issues/7648
    v.customize ['modifyvm', :id, '--cableconnected1', 'on']

    v.customize ["modifyvm", :id, "--rtcuseutc", "on"]
    v.customize ["modifyvm", :id, "--audio", "none"]
    v.customize ["modifyvm", :id, "--paravirtprovider", "kvm"]
    v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate//srv/www", "1"]
    v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate//srv/config", "1"]

    # Set the box name in VirtualBox to match the working directory.
    vvv_pwd = Dir.pwd
    v.name = File.basename(vagrant_dir) + "_" + (Digest::SHA256.hexdigest vagrant_dir)[0..10]
  end

  # Configuration options for the Parallels provider.
  config.vm.provider :parallels do |v|
    v.update_guest_tools = true
    v.customize ["set", :id, "--longer-battery-life", "off"]
    v.memory = vvv_config['vm_config']['memory']
    v.cpus = vvv_config['vm_config']['cores']
  end

  # Configuration options for the VMware Fusion provider.
  config.vm.provider :vmware_fusion do |v|
    v.vmx["memsize"] = vvv_config['vm_config']['memory']
    v.vmx["numvcpus"] = vvv_config['vm_config']['cores']
  end

  # Configuration options for Hyper-V provider.
  config.vm.provider :hyperv do |v, override|
    v.memory = vvv_config['vm_config']['memory']
    v.cpus = vvv_config['vm_config']['cores']
    v.enable_virtualization_extensions = true
    v.linked_clone = true
  end

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
  # This box is provided by Ubuntu vagrantcloud.com and is a nicely sized (332MB)
  # box containing the Ubuntu 14.04 Trusty 64 bit release. Once this box is downloaded
  # to your host computer, it is cached for future use under the specified box name.
  config.vm.box = "ubuntu/trusty64"

  # The Parallels Provider uses a different naming scheme.
  config.vm.provider :parallels do |v, override|
    override.vm.box = "parallels/ubuntu-14.04"
  end

  # The VMware Fusion Provider uses a different naming scheme.
  config.vm.provider :vmware_fusion do |v, override|
    override.vm.box = "puphpet/ubuntu1404-x64"
  end

  # VMWare Workstation can use the same package as Fusion
  config.vm.provider :vmware_workstation do |v, override|
    override.vm.box = "puphpet/ubuntu1404-x64"
  end

  # Hyper-V uses a different base box.
  config.vm.provider :hyperv do |v, override|
    override.vm.box = "bento/ubuntu-14.04"
  end

  config.vm.hostname = "vvv"

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
  config.vm.network :private_network, id: "vvv_primary", ip: vvv_config['vm_config']['private_network_ip']

  config.vm.provider :hyperv do |v, override|
    override.vm.network :private_network, id: "vvv_primary", ip: nil
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

  # /srv/database/
  #
  # If a database directory exists in the same directory as your Vagrantfile,
  # a mapped directory inside the VM will be created that contains these files.
  # This directory is used to maintain default database scripts as well as backed
  # up MariaDB/MySQL dumps (SQL files) that are to be imported automatically on vagrant up
  config.vm.synced_folder "database/", "/srv/database"

  # If the mysql_upgrade_info file from a previous persistent database mapping is detected,
  # we'll continue to map that directory as /var/lib/mysql inside the virtual machine. Once
  # this file is changed or removed, this mapping will no longer occur. A db_backup command
  # is now available inside the virtual machine to backup all databases for future use. This
  # command is automatically issued on halt, suspend, and destroy
  if File.exists?(File.join(vagrant_dir,'database/data/mysql_upgrade_info')) then
    config.vm.synced_folder "database/data/", "/var/lib/mysql", :mount_options => [ "dmode=777", "fmode=777" ]

    # The Parallels Provider does not understand "dmode"/"fmode" in the "mount_options" as
    # those are specific to Virtualbox. The folder is therefore overridden with one that
    # uses corresponding Parallels mount options.
    config.vm.provider :parallels do |v, override|
      override.vm.synced_folder "database/data/", "/var/lib/mysql", :mount_options => []
    end
    # Neither does the HyperV provider
    config.vm.provider :hyperv do |v, override|
      override.vm.synced_folder "database/data/", "/var/lib/mysql", :mount_options => []
    end
  end

  # /srv/config/
  #
  # If a server-conf directory exists in the same directory as your Vagrantfile,
  # a mapped directory inside the VM will be created that contains these files.
  # This directory is currently used to maintain various config files for php and
  # nginx as well as any pre-existing database files.
  config.vm.synced_folder "config/", "/srv/config"

  # /var/log/
  #
  # If a log directory exists in the same directory as your Vagrantfile, a mapped
  # directory inside the VM will be created for some generated log files.
  config.vm.synced_folder "log/", "/var/log", :owner => "vagrant", :mount_options => [ "dmode=777", "fmode=777" ]

  # /srv/www/
  #
  # If a www directory exists in the same directory as your Vagrantfile, a mapped directory
  # inside the VM will be created that acts as the default location for nginx sites. Put all
  # of your project files here that you want to access through the web server
  config.vm.synced_folder "www/", "/srv/www", :owner => "www-data", :mount_options => [ "dmode=775", "fmode=774" ]

  vvv_config['sites'].each do |site, args|
    if args['local_dir'] != File.join(vagrant_dir, 'www', site) then
      config.vm.synced_folder args['local_dir'], args['vm_dir'], :owner => "www-data", :mount_options => [ "dmode=775", "fmode=774" ]
    end
  end

  config.vm.provision "fix-no-tty", type: "shell" do |s|
    s.privileged = false
    s.inline = "sudo sed -i '/tty/!s/mesg n/tty -s \\&\\& mesg n/' /root/.profile"
  end

  # The Parallels Provider does not understand "dmode"/"fmode" in the "mount_options" as
  # those are specific to Virtualbox. The folder is therefore overridden with one that
  # uses corresponding Parallels mount options.
  config.vm.provider :parallels do |v, override|
    override.vm.synced_folder "www/", "/srv/www", :owner => "www-data", :mount_options => []
    override.vm.synced_folder "log/", "/var/log", :owner => "vagrant", :mount_options => []

    vvv_config['sites'].each do |site, args|
      if args['local_dir'] != File.join(vagrant_dir, 'www', site) then
        override.vm.synced_folder args['local_dir'], args['vm_dir'], :owner => "www-data", :mount_options => []
      end
    end
  end

  # The Hyper-V Provider does not understand "dmode"/"fmode" in the "mount_options" as
  # those are specific to Virtualbox. Furthermore, the normal shared folders need to be
  # replaced with SMB shares. Here we switch all the shared folders to us SMB and then
  # override the www folder with options that make it Hyper-V compatible.
  config.vm.provider :hyperv do |v, override|
    override.vm.synced_folder "www/", "/srv/www", :owner => "www-data", :mount_options => []
    override.vm.synced_folder "log/", "/var/log", :owner => "vagrant", :mount_options => []
    vvv_config['sites'].each do |site, args|
      if args['local_dir'] != File.join(vagrant_dir, 'www', site) then
        override.vm.synced_folder args['local_dir'], args['vm_dir'], :owner => "www-data", :mount_options => []
      end
    end
  end

  # Customfile - POSSIBLY UNSTABLE
  #
  # Use this to insert your own (and possibly rewrite) Vagrant config lines. Helpful
  # for mapping additional drives. If a file 'Customfile' exists in the same directory
  # as this Vagrantfile, it will be evaluated as ruby inline as it loads.
  #
  # Note that if you find yourself using a Customfile for anything crazy or specifying
  # different provisioning, then you may want to consider a new Vagrantfile entirely.
  if File.exists?(File.join(vagrant_dir,'Customfile')) then
    puts "Running Custom Vagrant file with additional vagrant configs at #{File.join(vagrant_dir,'Customfile')}\n\n"
    eval(IO.read(File.join(vagrant_dir,'Customfile')), binding)
    puts "Finished running Custom Vagrant file with additional vagrant configs, resuming normal vagrantfile execution\n\n"
  end

  vvv_config['sites'].each do |site, args|
    if args['allow_customfile'] then
      paths = Dir[File.join(args['local_dir'], '**', 'Customfile')]
      paths.each do |file|
        puts "Running additional site vagrant customfile at #{file}\n\n"
        eval(IO.read(file), binding)
      end
    end
  end

  # Provisioning
  #
  # Process one or more provisioning scripts depending on the existence of custom files.
  #
  # provison-pre.sh acts as a pre-hook to our default provisioning script. Anything that
  # should run before the shell commands laid out in provision.sh (or your provision-custom.sh
  # file) should go in this script. If it does not exist, no extra provisioning will run.
  if File.exists?(File.join(vagrant_dir,'provision','provision-pre.sh')) then
    config.vm.provision "pre", type: "shell", path: File.join( "provision", "provision-pre.sh" )
  end

  # provision.sh or provision-custom.sh
  #
  # By default, Vagrantfile is set to use the provision.sh bash script located in the
  # provision directory. If it is detected that a provision-custom.sh script has been
  # created, that is run as a replacement. This is an opportunity to replace the entirety
  # of the provisioning provided by default.
  if File.exists?(File.join(vagrant_dir,'provision','provision-custom.sh')) then
    config.vm.provision "custom", type: "shell", path: File.join( "provision", "provision-custom.sh" )
  else
    config.vm.provision "default", type: "shell", path: File.join( "provision", "provision.sh" )
  end

  # Provision the dashboard that appears when you visit vvv.test
  config.vm.provision "dashboard",
      type: "shell",
      path: File.join( "provision", "provision-dashboard.sh" ),
      args: [
        vvv_config['dashboard']['repo'],
        vvv_config['dashboard']['branch']
      ]

  vvv_config['utility-sources'].each do |name, args|
    config.vm.provision "utility-source-#{name}",
      type: "shell",
      path: File.join( "provision", "provision-utility-source.sh" ),
      args: [
          name,
          args['repo'].to_s,
          args['branch'],
      ]
  end

  vvv_config['utilities'].each do |name, utilities|

    if ! utilities.kind_of? Array then
      utilities = Hash.new
    end
    utilities.each do |utility|
        if utility == 'tideways' then
          vvv_config['hosts'] += ['tideways.vvv.test']
        end
        config.vm.provision "utility-#{name}-#{utility}",
          type: "shell",
          path: File.join( "provision", "provision-utility.sh" ),
          args: [
              name,
              utility
          ]
      end
  end

  vvv_config['sites'].each do |site, args|
    if args['skip_provisioning'] === false then
      config.vm.provision "site-#{site}",
        type: "shell",
        path: File.join( "provision", "provision-site.sh" ),
        args: [
          site,
          args['repo'].to_s,
          args['branch'],
          args['vm_dir'],
          args['skip_provisioning'].to_s,
          args['nginx_upstream']
        ]
    end
  end

  # provision-post.sh acts as a post-hook to the default provisioning. Anything that should
  # run after the shell commands laid out in provision.sh or provision-custom.sh should be
  # put into this file. This provides a good opportunity to install additional packages
  # without having to replace the entire default provisioning script.
  if File.exists?(File.join(vagrant_dir,'provision','provision-post.sh')) then
    config.vm.provision "post", type: "shell", path: File.join( "provision", "provision-post.sh" )
  end

  # Local Machine Hosts
  #
  # If the Vagrant plugin hostsupdater (https://github.com/cogitatio/vagrant-hostsupdater) is
  # installed, the following will automatically configure your local machine's hosts file to
  # be aware of the domains specified below. Watch the provisioning script as you may need to
  # enter a password for Vagrant to access your hosts file.
  #
  # By default, we'll include the domains set up by VVV through the vvv-hosts file
  # located in the www/ directory and in vvv-config.yml.
  if defined?(VagrantPlugins::HostsUpdater)

    # Pass the found host names to the hostsupdater plugin so it can perform magic.
    config.hostsupdater.aliases = vvv_config['hosts']
    config.hostsupdater.remove_on_suspend = true
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
  config.trigger.after :up do |trigger|
    trigger.name = "VVV Post-Up"
    trigger.run_remote = { inline: "/vagrant/config/homebin/vagrant_up" }
    trigger.on_error = :continue
  end
  config.trigger.before :reload do |trigger|
    trigger.name = "VVV Pre-Reload"
    trigger.run_remote = { inline: "/vagrant/config/homebin/vagrant_halt" }
    trigger.on_error = :continue
  end
  config.trigger.after :reload do |trigger|
    trigger.name = "VVV Post-Reload"
    trigger.run_remote = { inline: "/vagrant/config/homebin/vagrant_up" }
    trigger.on_error = :continue
  end
  config.trigger.before :halt do |trigger|
    trigger.name = "VVV Pre-Halt"
    trigger.run_remote = { inline: "/vagrant/config/homebin/vagrant_halt" }
    trigger.on_error = :continue
  end
  config.trigger.before :suspend do |trigger|
    trigger.name = "VVV Pre-Suspend"
    trigger.run_remote = { inline: "/vagrant/config/homebin/vagrant_suspend" }
    trigger.on_error = :continue
  end
  config.trigger.before :destroy do |trigger|
    trigger.name = "VVV Pre-Destroy"
    trigger.run_remote = { inline: "/vagrant/config/homebin/vagrant_destroy" }
    trigger.on_error = :continue
  end
end
