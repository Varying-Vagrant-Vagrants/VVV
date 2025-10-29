# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:

def vvv_get_provider_box( provider, vvv_config )
  box = VVV_BOXES[provider]
  box = vvv_config['vm_config']['box'] if vvv_config['vm_config']['box']
  return box
end

def vvv_get_provider_box_version( provider, vvv_config )
  box_version = VVV_BOX_VERSION[provider]
  box_version = vvv_config['vm_config']['box_version'] if vvv_config['vm_config']['box_version']
  return box_version
end

def vvv_setup_boxes( config, vvv_config )
  config.vm.box_check_update = false
  config.vm.box_version = '>= 0'

  config.vm.provider :virtualbox do |_v, override|
    override.vm.box = vvv_get_provider_box( :VIRTUALBOX, vvv_config )
    override.vm.box_version = vvv_get_provider_box_version( :VIRTUALBOX, vvv_config )
  end

  config.vm.provider :parallels do |_v, override|
    override.vm.box = vvv_get_provider_box( :PARALLELS, vvv_config )
    override.vm.box_version = vvv_get_provider_box_version( :PARALLELS, vvv_config )
  end

  config.vm.provider :vmware_desktop do |v, override|
    override.vm.box = vvv_get_provider_box( :VMWARE_DESKTOP, vvv_config )
    override.vm.box_version = vvv_get_provider_box_version( :VMWARE_DESKTOP, vvv_config )
  end

  config.vm.provider :hyperv do |_v, override|
    override.vm.box = vvv_get_provider_box( :HYPERV, vvv_config )
    override.vm.box_version = vvv_get_provider_box_version( :HYPERV, vvv_config )
  end

  config.vm.provider :docker do |d, override|
    d.image = vvv_get_provider_box( :DOCKER, vvv_config )
  end
end

def vvv_configure_vm( config, vvv_config, vagrant_dir )

  config.vm.hostname = 'vvv'

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

  config.vm.provider :vmware_desktop do |v, override|
    v.gui = false
  end

  config.vm.provider :hyperv do |_v, override|
    _v.vmname = File.basename(vagrant_dir) + '_' + (Digest::SHA256.hexdigest vagrant_dir)[0..10]
  end

  config.vm.provider :docker do |d, override|
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

end
