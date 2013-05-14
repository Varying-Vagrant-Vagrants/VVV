package { 'vim':
	ensure		=> present,
	name 		=> 'vim',
}

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