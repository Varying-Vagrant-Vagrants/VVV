FROM phusion/baseimage

RUN rm -f /etc/service/sshd/down

RUN echo "mysql-server mysql-server/root_password password root"       | debconf-set-selections && \
    echo "mysql-server mysql-server/root_password_again password root" | debconf-set-selections && \
    echo "postfix postfix/main_mailer_type select Internet Site"       | debconf-set-selections && \
    echo "postfix postfix/mailname string vvv"                         | debconf-set-selections && \
    apt-get update && apt-get install -y \
        colordiff \
        dos2unix \
        gettext \
        git \
        graphviz \
        imagemagick \
        memcached \
        mysql-server \
        nginx \
        ngrep \
        ntp \
        php-imagick \
        php-memcache \
        php-pear \
        php7.0-cli \
        php7.0-common \
        php7.0-curl \
        php7.0-dev \
        php7.0-fpm \
        php7.0-gd \
        php7.0-imap \
        php7.0-json \
        php7.0-mbstring \
        php7.0-mcrypt \
        php7.0-mysql \
        php7.0-soap \
        php7.0-xml \
        php7.0-zip \
        postfix \
        rsync \
        subversion \
        sudo \
        unzip \
        vim \
        wget \
        zip

# Setup the vagrant user and default SSH Key
RUN if ! getent passwd vagrant; then useradd -d /home/vagrant -m -s /bin/bash vagrant; fi \
    && echo vagrant:vagrant | chpasswd \
    && echo 'vagrant ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers \
    && mkdir -p /etc/sudoers.d \
    && echo 'vagrant ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers.d/vagrant \
    && chmod 0440 /etc/sudoers.d/vagrant

RUN mkdir -p /home/vagrant/.ssh \
    && chmod 0700 /home/vagrant/.ssh \
    && curl https://raw.github.com/mitchellh/vagrant/master/keys/vagrant.pub \
      -L -o /home/vagrant/.ssh/authorized_keys \
    && chmod 0600 /home/vagrant/.ssh/authorized_keys \
    && chown -R vagrant /home/vagrant/.ssh

CMD ["/sbin/my_init"]
