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
    config.vm.synced_folder 'database/data/', '/var/lib/mysql', create: true, owner: 9001, group: 9001, mount_options: MOUNT_OPTIONS_VIRTUALBOX_MYSQL
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
  config.vm.synced_folder LOCAL_LOG_PATHS[:memcached], '/var/log/memcached', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS_VIRTUALBOX_LOG
  config.vm.synced_folder LOCAL_LOG_PATHS[:nginx], '/var/log/nginx', owner: 'root', create: true, group: 'root', mount_options: MOUNT_OPTIONS_VIRTUALBOX_LOG
  config.vm.synced_folder LOCAL_LOG_PATHS[:php], '/var/log/php', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS_VIRTUALBOX_LOG
  config.vm.synced_folder LOCAL_LOG_PATHS[:provisioners], '/var/log/provisioners', create: true, owner: 'root', group: 'root', mount_options: MOUNT_OPTIONS_VIRTUALBOX_LOG

end
