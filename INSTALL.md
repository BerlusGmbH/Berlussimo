# Installing Berlussimo

This document describes how to install Berlussimo on a server. Berlussimo is a web application, currently consisting of 
two main parts: 

* A legacy forms based web application
* A single page web application backed by a GraphQL API

To drive these two parts, multiple processes are needed. The processes are isolated into `docker` containers. The 
containers are orchestrated by `docker-compose`. The installation is split into the following parts:

* [Prerequisite Software](#prerequisite-software)
* [Install](#install)
* [Development](#development)

## Prerequisite Software

Before you can install Berlussimo, you must install and configure the
following products on your server:

* [docker-engine](https://docs.docker.com/install/overview/) to run the different processes of berlussimo

* [docker-compose](https://docs.docker.com/compose/) to orchestrate the different processes of berlussmio

## Install

### Download `docker-compose` files

Download the `docker-compose` files. These files hold all information to start the docker containers of berlussimo.

```shell
# Download config and .env file:
wget https://raw.githubusercontent.com/BerlusGmbH/Berlussimo/feature/graphql/docker/docker-compose.yml
wget https://raw.githubusercontent.com/BerlusGmbH/Berlussimo/feature/graphql/docker/.env.example

# Rename .env.example to .env
mv .env.example .env
```

Generate two keys and assign them in `.env` to `APP_KEY` and `PUSHER_APP_SECRET`

```
$ docker run --rm berlus/berlussimo-fpm:dev php artisan key:generate --show
Application key set successfully.
Pusher application key set successfully.
Encryption keys generated successfully.
base64:rgtk2obQ3FwLucTFapOf1AZQm/eBkLRUWv0uWJIksHw=

$ docker run --rm berlus/berlussimo-fpm:dev php artisan key:generate --show
Application key set successfully.
Pusher application key set successfully.
Encryption keys generated successfully.
base64:Qa43vvQMAr2FLe6jxE7v6f6qaDCRKf4rKJ+/GJ8NUdI=

#In .env
APP_KEY=base64:rgtk2obQ3FwLucTFapOf1AZQm/eBkLRUWv0uWJIksHw=
PUSHER_APP_SECRET=base64:Qa43vvQMAr2FLe6jxE7v6f6qaDCRKf4rKJ+/GJ8NUdI=
```



### Initial Setup

Berlussimo is now ready to be run for the first time, but it has not been initialized yet. 

```shell
# Startup Berlussimo
docker-compose -p berlussimo up

# Switch into the database container
docker-compose -p berlussimo exec db bash 

# Inside the database container. Create the Database
mysqladmin create -u root -p berlussimo
# The default password is "password"

# Leave the database container
exit

# Enter fpm container
docker-compose -p berlussimo exec fpm bash

# Initialize the database
php artisan migrate
# Answer with yes
```

### First Login

Connect to the Website at [http://\<ip of your server\>](). The default credentials are:

* User: admin@berlussimo
* Password: password

---
**Note**
Consider running an SSL Reverse [Proxy](https://en.wikipedia.org/wiki/Proxy_server) to protect the communication between Server and Client.  

---
