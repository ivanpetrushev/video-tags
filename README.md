# docker-laravel

## Install

* Checkout the repository in your local directory
* This project is using `apache`, `mysql` and `phpmyadmin`. If you have other apps using those services, edit `docker-compose.yml` and change port numbers
* Edit `docker-compose.yml` and change this left side to mount your filesystem directory: `- /home/ivanatora/snimki/:/var/www/html/docroot/public/data` (there are 2 lines like this)
* `$ docker-compose up` will build and start containers
* `$ docker exec -it videotags_php_1 bash` followed by:
* * `# composer.phar install`
* * c`# chmod a+rw /var/www/laravel/storage/logs/*`


This projects come with preinstalled Laravel 5.4. If you want to wipe it, just remove everything in `laravel/`
