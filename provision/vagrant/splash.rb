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
    #{RED}\\ V#{GREEN}\\ V#{BLUE}\\ V / #{PURPLE}v#{version} #{PURPLE}Ruby:#{RUBY_VERSION}, Path:"#{vagrant_dir}"
    #{RED} \\_/#{GREEN}\\_/#{BLUE}\\_/  #{CRESET}#{BRANCH_C}#{git_or_zip}#{branch}#{commit}#{CRESET}

  HEREDOC
  puts splashfirst
end
