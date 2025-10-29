# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:

def vvv_sync_provisioner_folders( config, vvv_config, vagrant_dir )
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
  use_db_share = vvv_use_db_share(vvv_config)
  if use_db_share == true
    # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
    config.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 9001, group: 9001, mount_options: MOUNT_OPTIONS[:MYSQL][:VIRTUALBOX]
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
end

def vvv_sync_log_folders( config, vvv_config )
  # If a log directory exists in the same directory as your Vagrantfile, a mapped
  # directory inside the VM will be created for some generated log files.
  config.vm.synced_folder LOCAL_LOG_PATHS[:memcached], '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VIRTUALBOX]
  config.vm.synced_folder LOCAL_LOG_PATHS[:nginx], '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VIRTUALBOX]
  config.vm.synced_folder LOCAL_LOG_PATHS[:php], '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VIRTUALBOX]
  config.vm.synced_folder LOCAL_LOG_PATHS[:provisioners], '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VIRTUALBOX]

  config.vm.provider :docker do |_v, override|
    override.vm.synced_folder LOCAL_LOG_PATHS[:memcached], '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:DOCKER]
    override.vm.synced_folder LOCAL_LOG_PATHS[:nginx], '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:DOCKER]
    override.vm.synced_folder LOCAL_LOG_PATHS[:php], '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:DOCKER]
    override.vm.synced_folder LOCAL_LOG_PATHS[:provisioners], '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:DOCKER]
  end

  config.vm.provider :parallels do |_v, override|
    override.vm.synced_folder LOCAL_LOG_PATHS[:memcached], '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:PARALLELS]
    override.vm.synced_folder LOCAL_LOG_PATHS[:nginx], '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:PARALLELS]
    override.vm.synced_folder LOCAL_LOG_PATHS[:php], '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:PARALLELS]
    override.vm.synced_folder LOCAL_LOG_PATHS[:provisioners], '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:PARALLELS]
  end

  config.vm.provider :hyperv do |v, override|
    override.vm.synced_folder LOCAL_LOG_PATHS[:memcached], '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:HYPERV]
    override.vm.synced_folder LOCAL_LOG_PATHS[:nginx], '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:HYPERV]
    override.vm.synced_folder LOCAL_LOG_PATHS[:php], '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:HYPERV]
    override.vm.synced_folder LOCAL_LOG_PATHS[:provisioners], '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:HYPERV]
  end

  # Specify the VMware Provider mount options for synced folders.
  config.vm.provider :vmware_desktop do |_v, override|
    override.vm.synced_folder LOCAL_LOG_PATHS[:memcached], '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VMWARE_DESKTOP]
    override.vm.synced_folder LOCAL_LOG_PATHS[:nginx], '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VMWARE_DESKTOP]
    override.vm.synced_folder LOCAL_LOG_PATHS[:php], '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VMWARE_DESKTOP]
    override.vm.synced_folder LOCAL_LOG_PATHS[:provisioners], '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS[:LOG][:VMWARE_DESKTOP]

    use_db_share = vvv_use_db_share(vvv_config)
    if use_db_share == true
      # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 112, group: 115, mount_options: MOUNT_OPTIONS[:MYSQL][:VMWARE_DESKTOP]
    end
  end

end

def vvv_sync_site_folders( config, vvv_config, vagrant_dir )
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
    override.vm.synced_folder 'www/', '/srv/www', owner: 'vagrant', group: 'www-data', mount_options: MOUNT_OPTIONS[:WWW][:HYPERV]
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
    vvv_config['sites'].each do |site, args|
      next if args['skip_provisioning']
      if args['local_dir'] != File.join(vagrant_dir, 'www', site)
        override.vm.synced_folder args['local_dir'], args['vm_dir'], owner: 'vagrant', group: 'www-data', mount_options: MOUNT_OPTIONS[:WWW][:VMWARE_DESKTOP]
      end
    end
  end
end

def vvv_shared_db_folders( config, vvv_config, vagrant_dir )
  config.vm.provider :parallels do |_v, override|
    use_db_share = vvv_use_db_share(vvv_config)
    if use_db_share == true
      # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 112, group: 115, mount_options: MOUNT_OPTIONS[:MYSQL][:PARALLELS]
    end
  end

  # Under Hyper-V the normal shared folders need to be replaced with SMB shares.
  # Here we switch all the shared folders to use SMB and then override the www
  # folder with options that make it Hyper-V compatible.
  config.vm.provider :hyperv do |v, override|
    use_db_share = vvv_use_db_share(vvv_config)
    if use_db_share == true
      # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 112, group: 115, mount_options: MOUNT_OPTIONS[:MYSQL][:HYPERV]
    end
  end

  # Specify the VMware Provider mount options for synced folders.
  config.vm.provider :vmware_desktop do |_v, override|
    use_db_share = vvv_use_db_share(vvv_config)
    if use_db_share == true
      # Map the MySQL Data folders on to mounted folders so it isn't stored inside the VM
      override.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 112, group: 115, mount_options: MOUNT_OPTIONS[:MYSQL][:VMWARE_DESKTOP]
    end
  end
end
