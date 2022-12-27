# frozen_string_literal: true

# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 et:

VAGRANTFILE_API_VERSION = "2"

Vagrant.require_version '>= 2.2.4'
require 'yaml'

branch_c = "\033[38;5;6m" # 111m"
red = "\033[38;5;9m" # 124m"
green = "\033[1;38;5;2m" # 22m"
blue = "\033[38;5;4m" # 33m"
purple = "\033[38;5;5m" # 129m"
docs = "\033[0m"
yellow = "\033[38;5;3m" # 136m"
yellow_underlined = "\033[4;38;5;3m" # 136m"
url = yellow_underlined
creset = "\033[0m"

splashfirst = <<~HEREDOC
  \033[1;38;5;196m#{red}__ #{green}__ #{blue}__ __
  #{red}\\ V#{green}\\ V#{blue}\\ V / 
  #{red} \\_/#{green}\\_/#{blue}\\_/{creset}

HEREDOC
puts splashfirst

puts "#{yellow}┌-──────────────────────────────────────────────────────────────────────────────┐#{creset}"
puts "#{yellow}│                                                                               │#{creset}"
puts "#{yellow}│ ! ▄▀▀▀▄▄▄▄▄▄▄▀▀▀▄ !  ACTION REQUIRED!                                         │#{creset}"
puts "#{yellow}│  !█▒▒░░░░░░░░░▒▒█                                                             │#{creset}"
puts "#{yellow}│    █░░█░▄▄░░█░░█ !   We don't use the master branch anymore!                  │#{creset}"
puts "#{yellow}│     █░░█░░█░▄▄█    ! Run this command to switch to stable:                    │#{creset}"
puts "#{yellow}│  !  ▀▄░█░░██░░█                                                               │#{creset}"
puts "#{yellow}│                      #{green}git checkout stable && git pull#{yellow}                          │#{creset}"
puts "#{yellow}│                                                                               │#{creset}"
puts "#{yellow}└───────────────────────────────────────────────────────────────────────────────┘#{creset}"
exit
