# All projects live in a 'projects' directory

file { 'projects-directory':
	ensure		=> directory,
	path		=> '/vagrant/projects',
	owner		=> 'vagrant',
	mode		=> 0755,
	before		=> File['www-directory-sym-link'],
}

file { 'www-directory-sym-link':
	ensure		=> link,
	path		=> '/srv/www',
	target		=> '/vagrant/projects',
	mode		=> 0755,
	require		=> File['projects-directory'],
}