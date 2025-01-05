#!/usr/bin/env bash

set -e

#########################
###      TESTING      ###
#########################

echo " :: TESTING NGINX"
nginx -t
echo " :: TESTING PHP-FPM"
php-fpm --fpm-config /etc/php/current/fpm/php-fpm.conf --allow-to-run-as-root -t


#########################
###     PREPARING     ###
#########################

echo " :: COPYING SYSTEM FILES"
mkdir -p /root/.ssh
cp /var/www/_docker/_ssh /root/.ssh -RT
chmod 0600 /root/.ssh -R


#########################
###      EXITING      ###
#########################

stop(){
  echo " :: EXITING"
  kill -s SIGTERM $(cat /run/supervisord.pid)
  wait $(cat /run/supervisord.pid)
  cp /root/.ssh /var/www/_docker/_ssh -RT
  exit 0
}


#########################
###      STARTING     ###
#########################

echo " :: STARTING"
trap stop SIGTERM SIGINT SIGQUIT SIGHUP
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf & SUPERVISOR_PID=$!
wait "${SUPERVISOR_PID}"
