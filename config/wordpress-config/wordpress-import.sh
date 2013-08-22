# wordpress-import.sh

# Change dir to plugins and remember where we were
curr_dir=`pwd`
cd wp-content/plugins/

#
# In this file, you can modify WordPress after it's initially installed.
# The most common thing you'll probably want to do is install plugins.
# Below is a walk-through of how you'd do so, along with many examples
# from common plugins used by developers.
#
# To install any of them, simply uncomment the line (you should also
# uncomment the relevant printf statements so you know what the script
# is doing, in case it hangs). Add your own following the formats
# provided by the examples.


#
# Add plugins via WP CLI. From the WP CLI docs:
# wp plugin install <plugin|zip|url> [--version=<version>] [--activate] [--force]
# see http://wp-cli.org/commands/plugin/ for further reference
#
# printf "Installing plugins from wordpress.org repository\n"
# wp plugin install debug-bar
# wp plugin install debug-bar-action-hooks
# wp plugin install debug-bar-cron
# wp plugin install debug-bar-extender
# wp plugin install debug-bar-query-tracer
# wp plugin install debug-bar-super-globals
# wp plugin install debug-bar-transients
# wp plugin install developer
# wp plugin install jetpack
# wp plugin install log-deprecated-notices
# wp plugin install mp6
# wp plugin install piglatin
# wp plugin install rewrite-rules-inspector
# wp plugin install user-switching
# wp plugin install vip-scanner
# wp plugin install wordpress-importer
# wp plugin install zoninator


#
# If you already have plugin files you simply want copied over, you can add
# them to config/wordpress-config/plugins/. If this directory exists, any
# files will be copied into wp-content/plugins/ when you uncomment the
# following block.
#
# printf "Installing plugins from local wordpress-config directory\n"
# if [ -d /srv/config/wordpress-config/plugins ]
# then
# cp -R /srv/config/wordpress-config/plugins/* ./
# else
# 	printf "No local plugins found\n"
# fi


#
# Sometimes, you might want to install plugins via git, svn, or other means.
# This is a good place to do that. Note that for github, you should use the
# git://github.com URL (see example)
#
# printf "Installing plugins from other sources\n"
# git clone git://github.com/alleyinteractive/wordpress-fieldmanager.git


#
# Lastly, if you want to install any mu-plugins (some are included with VVV),
# you can uncomment this block to install them. They are excluded by default
# to keep VVV as "core" as possible, and to make it a conscious decision of
# you, the developer, to include them.
#
# printf "Installing mu-plugins from local wordpress-config directory\n"
# if [ -d /srv/config/wordpress-config/mu-plugins ]
# then
# 	if [ ! -d ../mu-plugins ]
# 	then
# 		mkdir ../mu-plugins
# 	fi
# 	cd ../mu-plugins/
# 	cp -R /srv/config/wordpress-config/mu-plugins/* ./
# else
# 	printf "No local mu-plugins found\n"
# fi


#
# The fun doesn't stop there!
#
# Want to install and/or activate themes?
#    http://wp-cli.org/commands/theme/
# Add users?
#    http://wp-cli.org/commands/user/
# Import WXR?
#    http://wp-cli.org/commands/import/
#    Need some WXR? Check out the theme unit test data found at
#    https://wpcom-themes.svn.automattic.com/demo/theme-unit-test-data.xml
#
# For the full reference, see http://wp-cli.org/commands/


# Change back to whatever dir we were in prior to running this file
cd $curr_dir