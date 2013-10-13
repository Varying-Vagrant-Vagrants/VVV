# auto-site-setup.sh
#
# This script is responsible for finding new sites to setup.

# Kill previously symlinked Nginx configs
# We can't know what sites have been removed, so we have to remove all
# the configs and add them back in again.
find /etc/nginx/custom-sites -name 'vvv-auto-*.conf' -exec rm {} \;

# Look for Nginx vhost files, symlink them into the custom sites dir
for SITE_CONFIG_FILE in $(find /srv/www -maxdepth 4 -name 'vvv-nginx.conf'); do
	DEST_CONFIG_FILE=${SITE_CONFIG_FILE//\/srv\/www\//}
	DEST_CONFIG_FILE=${DEST_CONFIG_FILE//\//\-}
	DEST_CONFIG_FILE=${DEST_CONFIG_FILE/%-vvv-nginx.conf/}
	DEST_CONFIG_FILE="vvv-auto-$DEST_CONFIG_FILE-$(md5sum <<< $SITE_CONFIG_FILE | cut -c1-32).conf"
	# echo $DEST_CONFIG_FILE
	ln -s $SITE_CONFIG_FILE /etc/nginx/custom-sites/$DEST_CONFIG_FILE | echo "Symlinked Nginx config $SITE_CONFIG_FILE"
done

# Look for site setup scripts
find /srv/www -maxdepth 4 -name 'vvv-init.sh' -exec {} \;

# RESTART SERVICES AGAIN
#
# Make sure the services we expect to be running are running.
echo -e "\nRestart Nginx..."
# service nginx restart
