#!/bin/bash

status_code=$(curl --write-out %{http_code} --silent --output /dev/null "http://127.0.0.1:80/fpm_ping")
if [ "$status_code" != "200" ]; then exit 1; fi
exit 0;
