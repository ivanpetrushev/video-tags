FROM ubuntu
VOLUME /var/log/

RUN apt-get update && apt-get install -y \
    bash \
    cron \
    vim \
    snmp \
    php7*

ADD crontab /var/spool/cron/crontabs/root
RUN chmod 0600 /var/spool/cron/crontabs/root
CMD touch /var/log/cron.log && cron && env > /root/env.sh && tail -f /var/log/cron.log

WORKDIR /var/www/laravel