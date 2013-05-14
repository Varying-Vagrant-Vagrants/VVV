# Version control systems

package { 'git':
	ensure		=> present,
	name 		=> 'git',
}

package { 'subversion':
	ensure		=> present,
	name 		=> 'subversion',
}



# Editors

package { 'vim':
	ensure		=> present,
	name 		=> 'vim',
}


# Misc CLI tools

package { 'ack-grep':
	ensure		=> present,
	name 		=> 'ack-grep',
	before		=> File['ack-sym-link'],
}

file { 'ack-sym-link':
	ensure		=> link,
	path		=> '/usr/bin/ack',
	target		=> '/usr/bin/ack-grep',
	mode		=> 0755,
	require		=> Package['ack-grep'],
}