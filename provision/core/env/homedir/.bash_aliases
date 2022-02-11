# bash_aliases
#
# This file is copied into the home directory of the vagrant user on the virtual
# machine during provisioning and is included in the .bashrc automatically as
# provisioning is finished. This allows for various scripts and configurations to
# be available to us.
#

# set PATH so it includes user's private bin if it exists
if [ -d "$HOME/bin" ] ; then
    PATH="$HOME/bin:$PATH"
fi

# Set the WP_TESTS_DIR path directory so that we can use phpunit inside
# plugins almost immediately.
if [ -d "/srv/www/wordpress-trunk/public_html/tests/phpunit/" ]; then
    export WP_TESTS_DIR=/srv/www/wordpress-trunk/public_html/tests/phpunit/
elif [ -d "/srv/www/wordpress-develop/public_html/tests/phpunit/" ]; then
    export WP_TESTS_DIR=/srv/www/wordpress-develop/public_html/tests/phpunit/
fi

# Set the WP_CORE_DIR path so phpunit tests are run against WP trunk
if [ -d "/srv/www/wordpress-trunk/public_html/src/" ]; then
    export WP_CORE_DIR=/srv/www/wordpress-trunk/public_html/src/
elif [ -d "/srv/www/wordpress-develop/public_html/src/" ]; then
    export WP_CORE_DIR=/srv/www/wordpress-develop/public_html/src/
fi

# PHPCS path
if [[ $PATH != *"/srv/www/phpcs/scripts"* ]]; then
	export PATH="$PATH:/srv/www/phpcs/scripts"
fi


# Ruby Gems
if [ -d "$HOME/.gem/bin" ] ; then
	if [[ $PATH != *"${HOME}/.gem/bin"* ]]; then
		export PATH="$PATH:${HOME}/.gem/bin"
	fi
fi

# Vagrant scripts
if [[ $PATH != *"/srv/config/homebin"* ]]; then
	export PATH="$PATH:/srv/config/homebin"
fi

if [ -n "$BASH" ]; then
	# add autocomplete for grunt
	if [ ! type "grunt" > /dev/null 2>&1 ]; then
		eval "$(grunt --completion=bash)"
	fi

	# add autocomplete for wp-cli
	if [ -s "/srv/config/wp-cli/wp-completion.bash" ]; then
		. /srv/config/wp-cli/wp-completion.bash
	fi
fi
