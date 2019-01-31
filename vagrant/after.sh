#!/usr/bin/env bash

# If you would like to do some extra provisioning you may
# add any commands you wish to this file and they will
# be run after the Homestead machine is provisioned.
#
# If you have user-specific configurations you would like
# to apply, you may also create user-customizations.sh,
# which will be run after this script.

if [ ! -f /etc/supervisor/conf.d/websockets.conf ]
then
    echo "Adding laravel websockets"
    sudo ln -s /home/vagrant/code/vagrant/websockets.conf /etc/supervisor/conf.d/websockets.conf
fi

if [ ! -f /etc/supervisor/conf.d/queue.conf ]
then
    echo "Adding laravel queue worker"
    sudo ln -s /home/vagrant/code/vagrant/queue.conf /etc/supervisor/conf.d/queue.conf
fi

if [ -d /etc/nginx/ssl ]
then
    echo "Copying certificates for reuse by webpack-dev-server"
    sudo cp -r /etc/nginx/ssl/ /home/vagrant/.ssl
    sudo chown -R vagrant:vagrant /home/vagrant/.ssl/
fi

echo "Installing apollo-cli"
sudo npm install -g apollo

echo "Updating supervisor"
sudo supervisorctl update