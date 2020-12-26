#!/bin/sh -eux

echo " * Running cleanup scripts to prepare for vagrant box creation"

# Delete all Linux headers
dpkg --list \
    | awk '{ print $2 }' \
    | grep 'linux-headers' \
    | xargs apt-get -y purge;

# Remove specific Linux kernels, such as linux-image-3.11.0-15-generic but
# keeps the current kernel and does not touch the virtual packages,
# e.g. 'linux-image-generic', etc.
dpkg --list \
    | awk '{ print $2 }' \
    | grep 'linux-image-.*-generic' \
    | grep -v $(uname -r) \
    | xargs apt-get -y purge;

# Delete Linux source
dpkg --list \
    | awk '{ print $2 }' \
    | grep linux-source \
    | xargs apt-get -y purge;

# Delete development packages
#dpkg --list \
#    | awk '{ print $2 }' \
#    | grep -- '-dev$' \
#    | xargs apt-get -y purge;
#apt-get -y purge autotools-dev

# delete docs packages
dpkg --list \
    | awk '{ print $2 }' \
    | grep -- '-doc$' \
    | xargs apt-get -y purge;

# Delete X11 libraries
apt-get -y purge libx11-data xauth libxmuu1 libxcb1 libx11-6 libxext6;

# Delete obsolete networking
apt-get -y purge ppp pppconfig pppoeconf;

# Delete oddities
apt-get -y purge popularity-contest installation-report command-not-found command-not-found-data friendly-recovery bash-completion fonts-ubuntu-font-family-console laptop-detect;

# Exlude the files we don't need w/o uninstalling linux-firmware
echo "==> Setup dpkg excludes for linux-firmware"
cat <<_EOF_ | cat >> /etc/dpkg/dpkg.cfg.d/excludes
#VVV-BEGIN
path-exclude=/lib/firmware/*
path-exclude=/usr/share/doc/linux-firmware/*
#VVV-END
_EOF_

# Delete the massive firmware packages
rm -rf /lib/firmware/*
rm -rf /usr/share/doc/linux-firmware/*

apt-get -y autoremove;
apt-get -y clean;

# Remove docs
rm -rf /usr/share/doc/*

# Remove caches
find /var/cache -type f -exec rm -rf {} \;

# delete any logs that have built up during the install
find /var/log/ -name "*.log" -exec rm -f {} \;

# Blank netplan machine-id (DUID) so machines get unique ID generated on boot.
truncate -s 0 /etc/machine-id

echo " * Cleanup scripts have been ran in preparation of box creation"
