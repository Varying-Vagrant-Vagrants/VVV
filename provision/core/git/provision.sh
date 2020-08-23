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
  # Add ppa repo.
  echo " * Adding ppa:git-core/ppa repository"
  sudo add-apt-repository -y ppa:git-core/ppa &>/dev/null
  echo " * git-core/ppa added"
else
  echo " * git-core/ppa already present, skipping"
fi

VVV_PACKAGE_LIST+=(
  git
  git-lfs
  git-svn
)