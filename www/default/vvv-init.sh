# Provision vvv-dashboard by @topdown
echo -e "\n\n "
echo -e "\n================================== "
echo -e "\n Provision topdown/VVV-Dashboard"
echo -e "\n================================== "

# Constants
DESTDIR="/srv/www/default"

if [[ ! -f /srv/www/index.php ]]; then
	echo -e "\nInstalling vvv-dashboard..."
	echo -e "\n\n "
else
	echo -e "\nUpdating vvv-dashboard..."
	echo -e "\n\n "
fi

# Download files
cd $DESTDIR
mkdir -p bower_components/jquery/dist src/js
wget https://raw.githubusercontent.com/topdown/VVV-Dashboard/master/index.php --output-document=index.php --progress=bar:force
wget https://raw.githubusercontent.com/topdown/VVV-Dashboard/master/style.css --output-document=style.css --progress=bar:force
wget https://raw.githubusercontent.com/topdown/VVV-Dashboard/master/bower_components/jquery/dist/jquery.min.js --output-document=bower_components/jquery/dist/jquery.min.js --progress=bar:force
wget https://raw.githubusercontent.com/topdown/VVV-Dashboard/master/src/js/scripts.js --output-document=src/js/scripts.js --progress=bar:force

# Prepend 'dashboard-custom.php' loading if present
sed -i '1i <?php\
/**\
 * If a custom dashboard file exists, load that instead of the default\
 * dashboard provided by Varying Vagrant Vagrants. This file should be\
 * located in the `www/default/` directory.\
 */\
if \( file_exists\( "dashboard-custom.php" \) \) {\
	include\( "dashboard-custom.php" \);\
	exit;\
}\
\
\/\/ Begin default dashboard.\
?>' index.php

echo -e "\n\n "
echo -e "\n\033[33;32m...vvv-dashboard installed/updated.\033[0m"
echo -e "\n "
