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
require_relative 'provision/vagrant/config'
require_relative 'provision/vagrant/splash'
require_relative 'provision/vagrant/providers'
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

show_logo = false

# whitelist when we show the logo, else it'll show on global Vagrant commands
show_logo = true if %w[up resume status provision reload].include? ARGV[0]
show_logo = false if ENV['VVV_SKIP_LOGO']

# Show the initial splash screen
if show_logo
  vvv_show_logo_splash(vagrant_dir)
end

vvv_migrate_db_folders(vagrant_dir)

vvv_config_file = vvv_config_location_and_migration(vagrant_dir)
vvv_config = vvv_load_raw_config(vvv_config_file)
vvv_config = vvv_set_config_defaults(vvv_config, vagrant_dir)

# Create a global variable to use in functions and classes
$vvv_config = vvv_config

# Show the second splash screen section
if show_logo
  vvv_show_secondary_splash(vvv_config)
end

if defined? vvv_config['vm_config']['provider']
  # Override or set the vagrant provider.
  ENV['VAGRANT_DEFAULT_PROVIDER'] = vvv_config['vm_config']['provider']
end

ENV['LC_ALL'] = 'en_US.UTF-8'

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  vvv_configure_vm(config, vvv_config, vagrant_dir)
  vvv_configure_plugins(config, vvv_config, vagrant_dir)
  vvv_setup_boxes(config, vvv_config)

  vvv_configure_networking(config, vvv_config)

  vvv_sync_provisioner_folders(config, vvv_config, vagrant_dir)
  vvv_sync_log_folders(config, vvv_config)
  vvv_sync_site_folders(config, vvv_config, vagrant_dir)

  vvv_shared_db_folders(config, vvv_config, vagrant_dir)

  vvv_customfiles(vvv_config,vagrant_dir)
  vvv_configure_main_provisioners(config, vvv_config, vagrant_dir)
  vvv_configure_extension_provisioners(config, vvv_config)
  vvv_configure_site_provisioners(config, vvv_config)
  vvv_post_provisioners(config, vagrant_dir)
  vvv_configure_hosts(config, vvv_config, vagrant_dir)

  vvv_triggers(config)
end
