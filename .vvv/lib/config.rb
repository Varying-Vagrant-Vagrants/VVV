# frozen_string_literal: true

module VVV
  class Config
    GITHUB_USER_URL    = 'https://github.com/Varying-Vagrant-Vagrants/'
    UTILITIES_REPO_URL = "#{GITHUB_USER_URL}vvv-utilities.git"
    DASHBOARD_REPO_URL = "#{GITHUB_USER_URL}dashboard.git"
    PRIVATE_NETWORK_IP = '192.168.56.4'

    CONFIG_FILE = File.join(VVV::Info.vagrant_dir, 'config/config.yml').freeze

    require 'yaml'

    def initialize
      @config = YAML.load_file(CONFIG_FILE)
      lint
    end

    def values
      @config
    end

    def lint
      # If 'hosts' isn't an array, redefine it as such
      @config['hosts'] = ['vvv.test'] unless @config['hosts'].is_a? Array

      @config['sites'].each do |site, args|
        # If the site's value is a string, treat it as the repo value
        if @config['sites'][site].is_a? String
          @config['sites'][site] = { repo: args }
        end

        # If the site's args aren't defined as a Hash already,
        # redefine it as such.
        @config['sites'][site] = {} unless @config['sites'][site].is_a? Hash

        merge_site_defaults(site)
        process_hosts_for_site(site)
      end
      process_dashboard
      process_utilities
      process_utility_sources
      process_extensions
      process_extension_sources
      process_vm_config
      process_general
      process_vagrant_plugins
      set_vagrant_default_provider_env_variable
    end

    private

    def set_vagrant_default_provider_env_variable
      return unless @config['vm_config']['provider']

      ENV['VAGRANT_DEFAULT_PROVIDER'] = @config['vm_config']['provider']
    end

    def process_vagrant_plugins
      @config['vagrant-plugins'] = {} unless @config['vagrant-plugins']
    end

    def process_utilities
      @config['utilities'] = {} unless @config['utilities'].is_a? Hash
    end

    def process_utility_sources
      return if @config['utility-sources'].is_a? Hash

      @config['utility-sources'] = {}
    end

    def process_extensions
      @config['extensions'] = {} unless @config['extensions'].is_a? Hash
    end

    def process_extension_sources
      if @config['extension-sources'].is_a? Hash
        @config['extension-sources'].each do |name, args|
          next unless args.is_a? String

          @config['extension-sources'][name] = { 'repo' => args,
                                                 'branch' => 'master' }
        end
        unless @config['extension-sources'].key?('core')
          @config['extension-sources']['core'] = { 'repo' => UTILITIES_REPO_URL,
                                                   'branch' => 'master' }
        end
      else
        @config['extension-sources'] = {}
      end
    end

    def process_vm_config
      @config['vm_config'] = {} unless @config['vm_config'].is_a? Hash
      merge_vm_config_defaults
    end

    def merge_vm_config_defaults
      defaults = { 'memory' => 2048, 'cores' => 1, 'provider' => 'virtualbox',
                   'private_network_ip' => PRIVATE_NETWORK_IP }
      defaults['provider'] = 'parallels' if Etc.uname[:version].include? 'ARM64'
      @config['vm_config'] = defaults.merge(@config['vm_config'])
    end

    def process_general
      @config['general'] = {} unless @config['general'].is_a? Hash
    end

    def process_dashboard
      @config['dashboard'] = {} unless @config['dashboard'].is_a? Hash
      merge_dashboard_defaults
    end

    def merge_dashboard_defaults
      defaults = { 'repo' => DASHBOARD_REPO_URL, 'branch' => 'master' }
      @config['dashboard'] = defaults.merge(@config['dashboard'])
    end

    def process_hosts_for_site(site)
      unless @config['sites'][site]['skip_provisioning']
        # Find vvv-hosts files and add their lines to the @config hash
        site_host_paths = Dir.glob(
          "#{@config['sites'][site]['local_dir']}/*/vvv-hosts"
        )
        site_host_paths.each do |path|
          lines = File.readlines(path).map(&:chomp).grep(/\A[^#]/)
          lines.each do |l|
            @config['sites'][site]['hosts'] << l
          end
        end

        # Add the site's hosts to the 'global' hosts array
        @config['hosts'] += if @config['sites'][site]['hosts'].is_a? Array
                              @config['sites'][site]['hosts']
                            else
                              ["#{site}.test"]
                            end
        @config['sites'][site].delete('hosts')
      end
      @config['hosts'] = @config['hosts'].uniq
    end

    def merge_site_defaults(site)
      # Merge the defaults with the currently defined site args
      @config['sites'][site] = site_defaults(site).merge(@config['sites'][site])
    end

    def site_defaults(site)
      { 'repo' => false,
        'vm_dir' => "/srv/www/#{site}",
        'local_dir' => File.join(VVV::Info.vagrant_dir, 'www', site),
        'branch' => 'master',
        'skip_provisioning' => false,
        'allow_customfile' => false,
        'nginx_upstream' => 'php',
        'hosts' => [] }
    end
  end
end
