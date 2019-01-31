# Developing Berlussimo

---
**Note**
The config files you are about to use are for Ubuntu 18.04. 
Other operating systems should be possible but may require changes to the config. 

---

This document describes how to set up your development environment to build Berlussimo.
This environment mainly consists of a virtual machine, that contains all necessary binaries.
The source code is edited on the host and mapped into the virtual machine for execution.
This approach enables a wider choice in host operating systems. It also makes the environments easier to distribute.
That being said, as the initial note implies; I have only used Ubuntu 18.04 as host 
and therefore this is not a general setup instruction.
This approach also enables you to use the editor/IDE of your choice.  

* [Prerequisite Software](#prerequisite-software)
* [Setup](#setup)
* [Development](#development)

## Prerequisite Software

Before you can build Berlussimo, you must install and configure the
following products on your development machine:

* [Git](http://git-scm.com/) for version control

* [Vagrant](https://www.vagrantup.com/) manages a virtual machine which is used to run a development web server

* [VirtualBox 6.x](https://virtualbox.org) the hypervisor used to run the virtual machine

---
**Note**
If you are using Ubuntu, the Vagrant plugin [`vagrant-notify-forwarder`](https://github.com/mhallin/vagrant-notify-forwarder)
is highly recommended. It propagates file system changes from the host to the virtual machine.
This is needed for hot module reload. 

---

## Setup

### Installing the VM

Install the Laravel [Homestead](https://laravel.com/docs/5.8/homestead) VM to be used by Vagrant.

```shell
# Add homestead image to vagrant:
vagrant box add laravel/homestead

# Change into project root folder of your desire
cd <project root>

# Clone homestead base configuration:
git clone https://github.com/laravel/homestead.git

# Checkout release
cd homestead; git checkout v9.2.2; cd ..
```

### Getting the sources

Install Berlussimo sources.

```shell
# Clone berlussimo sources:
git clone https://github.com/BerlusGmbH/Berlussimo.git
```

### Initial config

The Homestead base configuration needs to be modified with Berlussimo
specific parts.

```shell
# Copy the file Homestead.yaml.example in place and edit it to your liking
# Especially the location of the source code and the location of the composer cache e.g.
#
# folders:
#  - map: /home/joe/Documents/berlussimo
#    to: /home/vagrant/code
#  - map: /home/joe/.composer
#    to: /home/vagrant/.composer
# 
# But also the RAM, IP and ssh key file
cp berlussimo/vagrant/Homestead.yaml.example berlussimo/vagrant/Homestead.yaml 

# After editing link files from berlussimo to homestead:
ln -s ../berlussimo/vagrant/Homestead.yaml ../berlussimo/vagrant/after.sh homestead/
ln -s ../berlussimo/vagrant/serve-berlussimo.sh homestead/scripts/site-types/berlussimo.sh
ln -s resources/aliases homestead/aliases

# Copy the environment file
cp berlussimo/.env.example berlussimo/.env
```

### First start
While inside the `homestead` directory you can manipulate the state of the VM.

```shell
# Switch to homestead directory:
cd homestead

# Start the VM:
vagrant up

# SSH into the VM
vagrant ssh
```

### First install of the dependencies
The external libraries have to be downloaded into the `code` folder using `npm` and `composer`.

```shell
# change into code folder
cd code

# Install dependencies
composer install
npm install
```

### First setup of laravel

```shell
# Genrate an APP_KEY
php artisan key:generate

# Generate a PUSHER_APP_KEY
php artisan pusher-key:generate

# Generate oauth keys
php artisan passport:keys
```

### Setup the Database

```shell
# Import schema into db
php artisan migrate
```

### Run the background workers for websockets and queues

```shell
# Run the workers, if not running
sudo supervisorctl update
```

### Compile client code
```shell
# Compile static artefacts for legacy client
npm run dev

# Compile hot reloading code for javascript client
npm run hot
```

---
**Note**
`npm run hot` is only needed, if you want to develop client code with hot module reload enabled (recommended).

---

## Development
Berlussimo should now be available at [https://berlussimo.test:44300](https://berlussimo.test:44300). The default credentials are:

```
login: admin@berlussimo
password: password
```

---
**Note**
If you connect for the first time a certificate warning might appear. Depending on the browser it might reappear over time.
With hot module reload you might have to accept the certificate exception for another server. The hot module reload 
resources are served from another server. 

---

### Ports

The VM exposes several ports to the processes that make up berlussimo (host -> vm).

* SSH: 2222 -> 22
* HTTPS: 44300 -> 443
* MariaDB: 33060 -> 3306

See the description for Laravel [Homestead](https://laravel.com/docs/5.8/homestead#ports) for additional information.

### Debugging

The php interpreter is configured to run with `xdebug` enabled. `xdebug` will connect to the calling IP on port `9000`.
The debugger has to be triggered by the browser. One way to do this is by a browser plugin. For example 
[Xdebug helper](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc).

Additional information is also available in the [Homestead](https://laravel.com/docs/5.8/homestead#debugging-web-requests)
documentation.

During development of the JavaScript Client two tools have proven to be very helpful:

* [Vue.js devtools](https://chrome.google.com/webstore/detail/vuejs-devtools/nhdogjmejiglipccpnnnanhbledajbpd) 
development plugin for vue applications
* [Apollo Client Developer Tools](https://chrome.google.com/webstore/detail/apollo-client-developer-t/jdkknkkbebbapilgoeccciglkfbmbnfm)
development plugin for apollo applications 
