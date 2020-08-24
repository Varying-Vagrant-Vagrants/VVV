# git
#
# apt-get does not have latest version of git,
# so let's the use ppa repository instead.
#
local STATUS=1
if [ ! -z "$(ls -A /etc/apt/sources.list.d/)" ]; then
  grep -Rq "^deb.*git-core/ppa" /etc/apt/sources.list.d/*.list
  STATUS=$?
fi
if [ "$STATUS" -ne "0" ]; then
  # Install prerequisites.
  echo " * Setting up Git PPA pre-requisites"
  sudo apt-get install -y python-software-properties software-properties-common &>/dev/null
  # Add ppa repo.
  echo " * Adding ppa:git-core/ppa repository"
  sudo add-apt-repository -y ppa:git-core/ppa &>/dev/null
  # Update apt-get info.
  sudo apt-get update --fix-missing
  echo " * git-core/ppa added"
else
  echo " * git-core/ppa already present, skipping"
fi

VVV_PACKAGE_LIST+=(
  git
  git-lfs
  git-svn
)