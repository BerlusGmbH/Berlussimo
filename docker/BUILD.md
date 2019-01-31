# Building Berlussimo

This document describes how to build and subsequently push the docker images.  

* [Build](#build)
* [Push](#push) to docker hub

## Build

Berlussimo consists of three images. One base image, one image containing the php interpreter 
and one image containing the web server:

* [berlus/berlussimo-base](https://hub.docker.com/repository/docker/berlus/berlussimo-base)
* [berlus/berlussimo-fpm](https://hub.docker.com/repository/docker/berlus/berlussimo-fpm)
* [berlus/berlussimo-web](https://hub.docker.com/repository/docker/berlus/berlussimo-web)


The commands below build the images. The images `berlussimo-fpm` and `berlussimo-web` depend on `berlussimo-base`.
The `build-arg` `TAG` determines the version of the image. In the example below the `dev` version is build.
These commands have to be run from the project root (the folder above the folder this file is in).
```shell
#docker build --build-arg TAG=<version> -t berlus/berlussimo-base:<version> -f docker/base/Dockerfile .

docker build --build-arg TAG=dev -t berlus/berlussimo-base:dev -f docker/base/Dockerfile .
docker build --build-arg TAG=dev -t berlus/berlussimo-fpm:dev -f docker/fpm/Dockerfile .
docker build --build-arg TAG=dev -t berlus/berlussimo-web:dev -f docker/web/Dockerfile .
```

## Push
After the images have been built, they can be published to docker hub.

---
**Note**
You might need to [docker login](https://docs.docker.com/engine/reference/commandline/login/) to be able to upload to docker hub. 

---

The commands below will upload the images to docker hub.
```shell
docker push berlus/berlussimo-base
docker push berlus/berlussimo-fpm
docker push berlus/berlussimo-web
```
