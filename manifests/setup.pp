exec { "apt-update":
	command => "/usr/bin/apt-get update"
}

file { 'projects-directory':
	ensure		=> directory,
	path		=> '/vagrant/projects',
	mode		=> 0755,
}