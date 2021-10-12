#!/usr/bin/env bash
# @description This file contains deprecated functions that may be removed later.

# @description Check if we're on Ubuntu 14 and abort provisioning
# @noargs
function deprecated_distro() {
  local command_exist
  if ! command -v lsb_release &> /dev/null; then
    return 0
  fi
  vvv_info " * checking Ubuntu version"
  codename=$(lsb_release --codename | cut -f2)
  if [[ $codename == "trusty" ]]; then
    r="\e[0;32m"
    vvv_error " "
    vvv_error '__ __ __ __'
    vvv_error '\ V\ V\ V / A message from the VVV team'
    vvv_error ' \_/\_/\_/  '
    vvv_error " "
    vvv_error "We Have Some Good News and Some Bad News"
    vvv_error "----------------------------------------"
    vvv_error " "
    vvv_error "The good news is that you updated to VVV 3+! Thanks for taking "
    vvv_error "care of your install!"
    vvv_error " "
    vvv_error "The bad news is that your VM is still an Ubuntu 14 VM. VVV 3+ needs"
    vvv_error "Ubuntu 18, and requires a DB backup, then a vagant destroy,"
    vvv_error "a reprovision, then a database restore."
    vvv_error " "
    vvv_error " "
    vvv_error "<b>Important: Destroying and reprovisioning will erase the database</b>"
    vvv_error " "
    sqlcount=$(cd /srv/database/backups; ls -1q ./*.sql | wc -l)
    if [[ $sqlcount -gt 0 ]]; then
      vvv_error " "
      vvv_error "Luckily, VVV backs up the database to database/backups, and "
      vvv_error "we found ${sqlcount} of those .sql files in database/backups from the last "
      vvv_error "time you ran vagrant halt."

      vvv_error "These DB backups can be restored after updating with this command:"
      vvv_error "vagrant ssh -c \"db_restore\""
    else
      vvv_error " "
      vvv_error "Normally VVV takes backups, but we didn't find any existing backups"
      vvv_error " "
      vvv_error "How Do I Grab Database Backups?"
      vvv_error "--------------------------------"
      vvv_error " "
      vvv_error "If you've turned off database backups, or haven't turned off your VM"
      vvv_error "in a while, take the following steps:"
      vvv_error " "
      vvv_error " 1. downgrade back to VVV 2:             git fetch --tags && git checkout 2.6.0"
      vvv_error " 2. turn on the VM but don't provision:  vagrant up"
      vvv_error " 3. run the backup DB script:            vagrant ssh -c \"db_backup\""
      vvv_error " 4. turn off the VM:                     vagrant halt"
      vvv_error " 5. return to VVV 3+:                    git checkout develop"
      vvv_error " 6. you can now update your VM to VVV 3, see the instructions in the section above"
    fi
    vvv_error " "
    vvv_error " "
    vvv_error "Updating Your VM To VVV 3+"
    vvv_error "--------------------------"
    vvv_error " "
    vvv_error "If you're happy and have your database files, you can update your VM to VVV 3+ with these commands:"
    vvv_error " "
    vvv_error " 1. destroy the VM:              vagrant destroy"
    vvv_error " 2. provision a new VM:          vagrant up --provision"
    vvv_error " 3. optionally restore backups:  vagrant ssh -c \"db_restore\""
    vvv_error " "
    vvv_error " "
    vvv_error " "
    exit 1
  fi
}

# @description Clean up the old resources folder, as VVV 3 moved extensions to a utilities folder
remove_v2_resources() {
  if [ -d /srv/provision/resources ]; then
    vvv_warn " * An old /srv/provision/resources folder was found, removing the deprecated folder ( utilities are stored in /srv/provision/utilitys now )"
    rm -rf /srv/provision/resources ## remove deprecated folder
  fi
}

# @description Clean up the old utilities folder, as VVV 3.8 moved utilities to an extensions folder
move_v3_utilities() {
  if [ -d /srv/provision/utilities ]; then
    vvv_warn " * An old /srv/provision/utilities folder was found, renaming to extensions"
    mv /srv/provision/utilities /srv/provision/extensions
  fi
}

# @description Symlink `/vagrant/certificates` for backwards compatibility.
# New scripts should use `/srv/certificates` instead.
support_v2_certificate_path() {
  # symlink the certificates folder for older site templates compat
  if [[ ! -d /vagrant/certificates ]]; then
    ln -s /srv/certificates /vagrant/certificates
  fi
}

vvv_add_hook init remove_v2_resources 5
vvv_add_hook init move_v3_utilities 5
vvv_add_hook init support_v2_certificate_path 5
vvv_add_hook init deprecated_distro 5
