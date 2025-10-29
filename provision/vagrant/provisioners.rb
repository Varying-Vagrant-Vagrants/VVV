# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:

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

def vvv_customfiles(vvv_config,vagrant_dir)
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
end

def vvv_triggers( config )
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

def vvv_post_provisioners( config, vagrant_dir )
  # provision-post.sh acts as a post-hook to the default provisioning. Anything that should
  # run after the shell commands laid out in provision.sh or provision-custom.sh should be
  # put into this file. This provides a good opportunity to install additional packages
  # without having to replace the entire default provisioning script.
  if File.exist?(File.join(vagrant_dir, 'provision', 'provision-post.sh'))
    config.vm.provision 'post', type: 'shell', keep_color: true, path: File.join('provision', 'provision-post.sh'), env: { "VVV_LOG" => "post" }
  end

  config.vm.provision "post-provision-script", type: 'shell', keep_color: true, path: File.join( 'config/homebin', 'vagrant_provision' ), env: { "VVV_LOG" => "post-provision-script" }
end
