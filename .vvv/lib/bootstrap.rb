# frozen_string_literal: true

module VVV
  # The Bootstrap class
  #
  # Used for determining which and if logos and other messages should be
  # displayed before the `Vagrant.configure` block in the `Vagrantfile`.
  class Bootstrap
    # Determine if the VVV logo and platform splash should be displayed
    def self.show_logo?
      return false if ENV['VVV_SKIP_LOGO']

      return true if %w[up resume status provision reload].include? ARGV[0]

      false
    end

    # Determine if the sudo warning should be displayed
    def self.show_sudo_bear?
      return true if !Vagrant::Util::Platform.windows? && Process.uid.zero?

      false
    end

    def self.box_overridden?(config)
      config['vm_config'].key?('box')
    end
  end
end
