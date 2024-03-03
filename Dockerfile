FROM ubuntu:20.04

LABEL maintainer="vvv@tomjn.com"

ENV DEBIAN_FRONTEND noninteractive

# install common dependencies
# ca-certificates usually needed by vagrant to download stuff
# some others are just attempt to speed the provision up
RUN apt-get update && apt-get install -y \
    locales \
    curl \
    lsb-release \
    openssh-server \
    sudo \
    python \
    ca-certificates \
    gnupg2 \
    software-properties-common \
    apt-utils \
    iputils-ping \
    net-tools \
    nano \
    less

## in case ca-cert already installed, force upgrade ( to get latest chain )
RUN apt-get upgrade -y ca-certificates

# ensure we have the en_US.UTF-8 locale available
RUN locale-gen en_US.UTF-8

# setup the vagrant user
RUN if ! getent passwd vagrant; then useradd -d /home/vagrant -m -s /bin/bash vagrant; fi \
    && echo vagrant:vagrant | chpasswd \
    && echo 'vagrant ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers \
    && mkdir -p /etc/sudoers.d \
    && echo 'vagrant ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers.d/vagrant \
    && chmod 0440 /etc/sudoers.d/vagrant

# add the vagrant insecure public key
RUN mkdir -p /home/vagrant/.ssh \
    && chmod 0700 /home/vagrant/.ssh \
    && wget --no-check-certificate \
      https://raw.githubusercontent.com/hashicorp/vagrant/main/keys/vagrant.pub \
      -O /home/vagrant/.ssh/authorized_keys \
    && chmod 0600 /home/vagrant/.ssh/authorized_keys \
    && chown -R vagrant /home/vagrant/.ssh

# don't clean packages, we might be using vagrant-cachier
RUN rm /etc/apt/apt.conf.d/docker-clean

# create the privilege separation directory for sshd
RUN mkdir -p /run/sshd

# run sshd in the foreground
CMD /usr/sbin/sshd -D \
    -o UseDNS=no\
    -o UsePAM=no\
    -o PasswordAuthentication=yes\
    -o PidFile=/tmp/sshd.pid
