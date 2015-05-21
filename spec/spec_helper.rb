require 'serverspec'
require 'serverspec'
require 'net/ssh'
require 'tempfile'

if 'localhost' == ENV['TARGET']
  set :backend, :exec
else
  set :backend, :ssh
  set :sudo_password, ENV['SUDO_PASSWORD']

  host = ENV['TARGET_HOST']

  `vagrant up #{host}`

  config = Tempfile.new('', Dir.tmpdir)
  `vagrant ssh-config #{host} > #{config.path}`

  options = Net::SSH::Config.for(host, [config.path])

  options[:user] ||= Etc.getlogin

  set :host,        options[:host_name] || host
  set :ssh_options, options
end
