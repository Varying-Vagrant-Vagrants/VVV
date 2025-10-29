# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:

def vvv_show_logo_splash( vagrant_dir )
  version = vvv_version(vagrant_dir)
  git_or_zip = 'zip-no-vcs'
  branch = ''
  commit = ''
  if File.directory?("#{vagrant_dir}/.git")
    git_or_zip = 'git::'
    branch = `git --git-dir="#{vagrant_dir}/.git" --work-tree="#{vagrant_dir}" rev-parse --abbrev-ref HEAD`
    branch = branch.chomp("\n"); # remove trailing newline so it doesn't break the ascii art
    commit = `git --git-dir="#{vagrant_dir}/.git" --work-tree="#{vagrant_dir}" rev-parse --short HEAD`
    commit = '(' + commit.chomp("\n") + ')'; # remove trailing newline so it doesn't break the ascii art
  end

  splashfirst = <<~HEREDOC
    \033[1;38;5;196m#{RED}__ #{GREEN}__ #{BLUE}__ __
    #{RED}\\ V#{GREEN}\\ V#{BLUE}\\ V / #{RED}v#{version} #{PURPLE}Path:"#{vagrant_dir}"
    #{RED} \\_/#{GREEN}\\_/#{BLUE}\\_/  #{CRESET}#{BRANCH_C}#{git_or_zip}#{branch}#{commit}#{CRESET}

  HEREDOC
  puts splashfirst
end

def vvv_show_links()
  links = <<~HEREDOC
    #{DOCS}Docs:       #{URL}https://varyingvagrantvagrants.org/
    #{DOCS}Contribute: #{URL}https://github.com/varying-vagrant-vagrants/vvv
    #{DOCS}Dashboard:  #{URL}http://vvv.test#{CRESET}

  HEREDOC
  puts links
end

def vvv_show_secondary_splash(vvv_config)
  platform = [ Vagrant::Util::Platform.platform]
  if Vagrant::Util::Platform.windows?
    platform << 'windows '
    platform << 'wsl ' if Vagrant::Util::Platform.wsl?
    platform << 'msys ' if Vagrant::Util::Platform.msys?
    platform << 'cygwin ' if Vagrant::Util::Platform.cygwin?
    if Vagrant::Util::Platform.windows_hyperv_enabled?
      platform << 'HyperV-Enabled '
    end
    platform << 'HyperV-Admin ' if Vagrant::Util::Platform.windows_hyperv_admin?
    if Vagrant::Util::Platform.windows_admin?
      platform << 'HasWinAdminPriv '
    else
      platform << 'missingWinAdminPriv ' unless Vagrant::Util::Platform.windows_admin?
    end
  else
    platform << 'shell:' + ENV['SHELL'] if ENV['SHELL']
    platform << 'systemd ' if Vagrant::Util::Platform.systemd?
  end

  platform << 'vagrant-hostmanager' if Vagrant.has_plugin?('vagrant-hostmanager')
  platform << 'vagrant-hostsupdater' if Vagrant.has_plugin?('vagrant-hostsupdater')
  platform << 'vagrant-goodhosts' if Vagrant.has_plugin?('vagrant-goodhosts')
  platform << 'vagrant-vbguest' if Vagrant.has_plugin?('vagrant-vbguest')
  platform << 'vagrant-disksize' if Vagrant.has_plugin?('vagrant-disksize')

  platform << 'CaseSensitiveFS' if Vagrant::Util::Platform.fs_case_sensitive?
  unless Vagrant::Util::Platform.terminal_supports_colors?
    platform << 'monochrome-terminal'
  end

  if defined? vvv_config['vm_config']['wordcamp_contributor_day_box']
    if vvv_config['vm_config']['wordcamp_contributor_day_box'] == true
      platform << 'contributor_day_box'
    end
  end

  if defined? vvv_config['vm_config']['box']
    unless vvv_config['vm_config']['box'].nil?
      puts "Custom Box: Box overridden via config/config.yml , this won't take effect until a destroy + reprovision happens"
      platform << 'box_override:' + vvv_config['vm_config']['box']
    end
  end

  if defined? vvv_config['general']['db_share_type']
    if vvv_config['general']['db_share_type'] != true
      platform << 'shared_db_folder_disabled'
    else
      platform << 'shared_db_folder_enabled'
    end
  else
    platform << 'shared_db_folder_default'
  end

  provider_version = '??'

  provider_meta = nil

  case vvv_config['vm_config']['provider']
  when 'virtualbox'
    provider_meta = VagrantPlugins::ProviderVirtualBox::Driver::Meta.new()
    provider_version = provider_meta.version
  when 'parallels'
    provider_version = '?'
    if defined? VagrantPlugins::Parallels
      provider_meta = VagrantPlugins::Parallels::Driver::Meta.new()
      provider_version = provider_meta.version
    end
  when 'vmware'
    provider_version = '??'
  when 'hyperv'
    provider_version = 'n/a'
  when 'docker'
    provider_version = `docker -v`.gsub("Docker version ", "")
  else
    provider_version = '??'
  end

  provider_version = provider_version.strip

  splashsecond = <<~HEREDOC
    #{YELLOW}Platform: #{YELLOW}#{platform.join(' ')}
    #{GREEN}Vagrant: #{GREEN}v#{Vagrant::VERSION}, #{BLUE}#{vvv_config['vm_config']['provider']}: #{BLUE}v#{provider_version}

  HEREDOC
  puts splashsecond

  if defined? vvv_config['general']['hide_splash_links']
    if vvv_config['general']['hide_splash_links'] != true
      vvv_show_links()
    end
  end
end
