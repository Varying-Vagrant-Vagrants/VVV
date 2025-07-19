
# Configures the main/tools/dashboard provisioners.
def vvv_configure_main_provisioners( config, vvv_config, vagrant_dir )
  unless Vagrant::Util::Platform.windows?
    if Process.uid == 0
      # the VM should know if vagrant was ran by a root user or using sudo
      config.vm.provision 'flag-root-vagrant-command',
        type: 'shell',
        keep_color: true,
        inline: "mkdir -p /vagrant && touch /vagrant/provisioned_as_root"
    end
  end

  long_provision_bear = <<~HTML
  #{BLUE}#{CRESET}
  #{BLUE}    ▄▀▀▀▄▄▄▄▄▄▄▀▀▀▄    ▄   ▄    #{GREEN}A full provision will take a bit.#{CRESET}
  #{BLUE}    █▒▒░░░░░░░░░▒▒█   █   █     #{GREEN}Sit back, relax, and have some tea.#{CRESET}
  #{BLUE}     █░░█░░░░░█░░█   ▀   ▀      #{CRESET}
  #{BLUE}  ▄▄  █░░░▀█▀░░░█   █▀▀▀▀▀▀█    #{GREEN}If you didn't want to provision you can#{CRESET}
  #{BLUE} █░░█ ▀▄░░░░░░░▄▀▄▀▀█      █    #{GREEN}turn VVV on with 'vagrant up'.#{CRESET}
  #{BLUE}───────────────────────────────────────────────────────────────────────#{CRESET}
  HTML

  # Changed the message here because it's going to show the first time you do vagrant up, which might be confusing
  config.vm.provision "pre-provision-script",
    type: 'shell',
    keep_color: true,
    inline: "echo \"#{long_provision_bear}\""

  # provison-pre.sh acts as a pre-hook to our default provisioning script. Anything that
  # should run before the shell commands laid out in provision.sh (or your provision-custom.sh
  # file) should go in this script. If it does not exist, no extra provisioning will run.
  if File.exist?(File.join(vagrant_dir, 'provision', 'provision-pre.sh'))
    config.vm.provision 'pre',
      type: 'shell',
      keep_color: true,
      path: File.join('provision', 'provision-pre.sh'),
      env: { "VVV_LOG" => "pre" }
  end

  # provision.sh or provision-custom.sh
  #
  # By default, Vagrantfile is set to use the provision.sh bash script located in the
  # provision directory. If it is detected that a provision-custom.sh script has been
  # created, that is run as a replacement. This is an opportunity to replace the entirety
  # of the provisioning provided by default.
  if File.exist?(File.join(vagrant_dir, 'provision', 'provision-custom.sh'))
    config.vm.provision 'custom',
      type: 'shell',
      keep_color: true,
      path: File.join('provision', 'provision-custom.sh'),
      env: { "VVV_LOG" => "main-custom" }
  else
    config.vm.provision 'default',
      type: 'shell',
      keep_color: true,
      path: File.join('provision', 'provision.sh'),
      env: { "VVV_LOG" => "main" }
  end

  config.vm.provision 'tools',
    type: 'shell',
    keep_color: true,
    path: File.join('provision', 'provision-tools.sh'),
    env: { "VVV_LOG" => "tools" }

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

end

# Configures the extensions and legacy utility provisioners.
def vvv_configure_extension_provisioners( config, vvv_config )
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
end

def vvv_configure_site_provisioners( config, vvv_config )

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
end
