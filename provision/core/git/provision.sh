# git
#
# apt-get does not have latest version of git,
# so let's the use ppa repository instead.
#
if ! vvv_src_list_has "git-core/ppa"; then
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