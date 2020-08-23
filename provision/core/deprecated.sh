#!/bin/bash
#
# core/deprecated.sh
#
# This file contains deprecated functions that may be removed later.

depreacted_distro() {
  codename=$(lsb_release --codename | cut -f2)
  if [[ $codename == "trusty" ]]; then
    r="\e[0;32m"
    echo " "
    echo '__ __ __ __'
    echo '\ V\ V\ V / A message from the VVV team'
    echo ' \_/\_/\_/  '
    echo -e " "
    echo -e "We Have Some Good News and Some Bad News"
    echo -e "----------------------------------------"
    echo -e " "
    echo -e "The good news is that you updated to VVV 3+! Thanks for taking "
    echo -e "care of your install!"
    echo -e " "
    echo -e "The bad news is that your VM is still an Ubuntu 14 VM. VVV 3+ needs"
    echo -e "Ubuntu 18, and requires a vagant destroy and a reprovision."
    echo -e " "
    echo -e " "
    echo -e "\e[1;4;33mImportant: Destroying and reprovisioning will erase the database${r}"
    echo -e " "
    sqlcount=$(cd /srv/database/backups; ls -1q ./*.sql | wc -l)
    if [[ $sqlcount -gt 0 ]]; then
      echo -e "\e[0;33m "
      echo -e "\e[0;33mLuckily, VVV backs up the database to database/backups, and "
      echo -e "we found ${sqlcount} of those .sql files in database/backups from the last "
      echo -e "time you ran vagrant halt."

      echo -e "These DB backups can be restored after updating with this command:"
      echo -e "vagrant ssh -c \"db_restore\"${r}"
    else
      echo -e "\e[0;33m "
      echo -e "\e[0;33mNormally VVV takes backups, but we didn't find any existing backups${r}"
      echo -e " "
      echo -e "How Do I Grab Database Backups?"
      echo -e "--------------------------------"
      echo -e " "
      echo -e "If you've turned off database backups, or haven't turned off your VM"
      echo -e "in a while, take the following steps:"
      echo -e " "
      echo -e " 1. downgrade back to VVV 2:             git fetch --tags && git checkout 2.6.0"
      echo -e " 2. turn on the VM but don't provision:  vagrant up"
      echo -e " 3. run the backup DB script:            vagrant ssh -c \"db_backup\""
      echo -e " 4. turn off the VM:                     vagrant halt"
      echo -e " 5. return to VVV 3+:                    git checkout develop"
      echo -e " 6. you can now update your VM to VVV 3, see the instructions in the section above"
    fi
    echo -e " "
    echo -e " "
    echo -e "Updating Your VM To VVV 3+"
    echo -e "--------------------------"
    echo -e " "
    echo -e "If you're happy and have your database files, you can update your VM to VVV 3+ with these commands:"
    echo -e " "
    echo -e " 1. destroy the VM:              vagrant destroy"
    echo -e " 2. provision a new VM:          vagrant up --provision"
    echo -e " 3. optionally restore backups:  vagrant ssh -c \"db_restore\""
    echo -e " "
    echo -e " "
    echo -e " "
    exit 1
  fi
}

remove_v2_resources() {
  if [ -d /srv/provision/resources ]; then
    echo " * An old /srv/provision/resources folder was found, removing the deprecated folder ( utilities are stored in /srv/provision/utilitys now )"
    rm -rf /srv/provision/resources ## remove deprecated folder
  fi
}

support_v2_certificate_path() {
  # symlink the certificates folder for older site templates compat
  if [[ ! -d /vagrant/certificates ]]; then
    ln -s /srv/certificates /vagrant/certificates
  fi
}
