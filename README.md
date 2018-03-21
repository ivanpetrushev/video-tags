# docker-laravel

## Install

* Checkout the repository in your local directory
* This project is using `apache`, `mysql` and `phpmyadmin`. If you have other apps using those services, edit `docker-compose.yml` and change port numbers
* `$ docker-compose up` will build and start containers
* `$ docker exec -it videotags_php_1 composer.phar install`


This projects come with preinstalled Laravel 5.4. If you want to wipe it, just remove everything in `laravel/`
