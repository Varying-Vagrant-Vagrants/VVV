def sudo_warnings
  red = "\033[38;5;9m" # 124m"
  creset = "\033[0m"
  puts "#{RED}┌-──────────────────────────────────────────────────────────────────────────────┐#{CRESET}"
  puts "#{RED}│                                                                               │#{CRESET}"
  puts "#{RED}│  ⚠ DANGER DO NOT USE SUDO ⚠                                                   │#{CRESET}"
  puts "#{RED}│                                                                               │#{CRESET}"
  puts "#{RED}│ ! ▄▀▀▀▄▄▄▄▄▄▄▀▀▀▄ !  You should never use sudo or root with vagrant.          │#{CRESET}"
  puts "#{RED}│  !█▒▒░░░░░░░░░▒▒█    It causes lots of problems :(                            │#{CRESET}"
  puts "#{RED}│    █░░█░▄▄░░█░░█ !                                                            │#{CRESET}"
  puts "#{RED}│     █░░█░░█░▄▄█    ! We're really sorry but you may need to do painful        │#{CRESET}"
  puts "#{RED}│  !  ▀▄░█░░██░░█      cleanup commands to fix this.                            │#{CRESET}"
  puts "#{RED}│                                                                               │#{CRESET}"
  puts "#{RED}│  If vagrant does not work for you without sudo, open a GitHub issue instead   │#{CRESET}"
  puts "#{RED}│  In the future, this warning will halt provisioning to prevent new users      │#{CRESET}"
  puts "#{RED}│  making this mistake.                                                         │#{CRESET}"
  puts "#{RED}│                                                                               │#{CRESET}"
  puts "#{RED}│  ⚠ DANGER SUDO DETECTED!                                                      │#{CRESET}"
  puts "#{RED}│                                                                               │#{CRESET}"
  puts "#{RED}│  In the future the VVV team will be making it harder to use VVV with sudo.    │#{CRESET}"
  puts "#{RED}│  We will require a config option so that users can do data recovery, and      │#{CRESET}"
  puts "#{RED}│  disable sites and the dashboard.                                             │#{CRESET}"
  puts "#{RED}│                                                                               │#{CRESET}"
  puts "#{RED}│  DO NOT USE SUDO, use ctrl+c/cmd+c and cancel this command ASAP!!!            │#{CRESET}"
  puts "#{RED}│                                                                               │#{CRESET}"
  puts "#{RED}└───────────────────────────────────────────────────────────────────────────────┘#{CRESET}"
  # exit
end


def vvv_is_docker_present()
  if `docker version`
    return true
  end
  return false
end

def vvv_is_parallels_present()
  return Vagrant.has_plugin?("vagrant-parallels")
end

def vvv_version( vagrant_dir )
  version = '?'
  File.open("#{vagrant_dir}/version", 'r') do |f|
    version = f.read
    version = version.gsub("\n", '')
  end
  return version
end

def vvv_use_db_share(vvv_config)
  use_db_share = false

  if defined? vvv_config['general']['db_share_type']
    use_db_share = vvv_config['general']['db_share_type'] == true
  end
  return use_db_share
end
