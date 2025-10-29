# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:

def vvv_config_location_and_migration(vagrant_dir)
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
  return vvv_config_file
end

def vvv_load_raw_config( vvv_config_file )
  begin
    vvv_config = YAML.load_file( vvv_config_file )
    return vvv_config
  rescue StandardError => e
    puts "#{RED}config/config.yml isn't a valid YAML file.#{CRESET}\n\n"
    puts "#{RED}VVV cannot be executed!#{CRESET}\n\n"

    warn e.message
    exit
  end
  return false
end

def vvv_get_vm_defaults()
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

  return vm_defaults
end

def vvv_set_config_defaults( vvv_config, vagrant_dir )
  unless vvv_config['sites'].is_a? Hash
    vvv_config['sites'] = {}
    puts "#{RED}config/config.yml is missing a sites section.#{CRESET}\n\n"
  end

  vvv_config['sites'] = {} unless vvv_config['sites'].is_a? Hash
  vvv_config['utilities'] = {} unless vvv_config['utilities'].is_a? Hash
  vvv_config['utility-sources'] = {} unless vvv_config['utility-sources'].is_a? Hash
  vvv_config['extensions'] = {} unless vvv_config['extensions'].is_a? Hash
  vvv_config['vm_config'] = {} unless vvv_config['vm_config'].is_a? Hash

  vvv_config['general'] = {} unless vvv_config['general'].is_a? Hash

  vvv_config['hosts'] = [] unless vvv_config['hosts'].is_a? Hash
  vvv_config['hosts'] += ['vvv.test']

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
  vvv_config['dashboard'] = dashboard_defaults.merge( vvv_config['dashboard'] )

  unless vvv_config['extension-sources'].key?('core')
    vvv_config['extension-sources']['core'] = {
      'repo' => 'https://github.com/Varying-Vagrant-Vagrants/vvv-utilities.git',
      'branch' => 'master'
    }
  end

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

  vm_defaults = vvv_get_vm_defaults()

  vvv_config['vm_config'] = vm_defaults.merge(vvv_config['vm_config'])

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

  vvv_config['hosts'] = vvv_config['hosts'].uniq

  vvv_config['vagrant-plugins'] = {} unless vvv_config['vagrant-plugins']


  return vvv_config
end
