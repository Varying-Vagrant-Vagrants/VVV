# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:

def vvv_configure_networking(config, vvv_config)
  # Private Network (default)
  #
  # A private network is created by default. This is the IP address through which your
  # host machine will communicate to the guest. In this default configuration, the virtual
  # machine will have an IP address of 192.168.56.4 and a virtual network adapter will be
  # created on your host machine with the IP of 192.168.50.1 as a gateway.
  #
  # Access to the guest machine is only available to your local host. To provide access to
  # other devices, a public network should be configured or port forwarding enabled.
  #
  # Note: If your existing network is using the 192.168.56.x subnet, this default IP address
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
end
