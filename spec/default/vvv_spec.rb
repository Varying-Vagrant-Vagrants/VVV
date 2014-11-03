require 'spec_helper'
require 'shellwords'


# All hosts entries should be resolvable
hosts = %w{
  local.wordpress.dev
  local.wordpress-trunk.dev
  src.wordpress-develop.dev
  build.wordpress-develop.dev
}

hosts.each do |host|
  describe host(host) do
    it { should be_resolvable.by('hosts') }
  end
end


# All packages are should be enabling and running
packages = %w{
  nginx
  mysql
  php5-fpm
}

packages.each do |pkg|
  describe service(pkg) do
    it { should be_enabled }
    it { should be_running }
  end
end


# All ports are should be listening
ports = %w{
  80
  443
}

ports.each do |port|
  describe port(port) do
    it { should be_listening }
  end
end


# All WordPresses should be running
wps = %w{
  local.wordpress.dev
  local.wordpress-trunk.dev
  src.wordpress-develop.dev
  build.wordpress-develop.dev
}
wps.each do |wp|
  describe command("wget -q http://#{Shellwords.shellescape(wp)}/ -O - | head -100 | grep generator") do
    its(:stdout) { should match /<meta name="generator" content="WordPress .*"/i }
  end
end


# All commands should be return status 0
commands = [
  'wp --info',
  'phpunit --version',
  'git --version',
  'dos2unix --version',
  'ngrep -V',
  'composer --version',
  'grunt --version',
]

commands.each do |cmd|
  describe command(cmd) do
    let(:disable_sudo) { true } # It should be vagrant user
    its(:exit_status) { should eq 0 }
  end
end
