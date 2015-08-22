#!/bin/bash

cd /opt/RouterAPI
git pull
service php5-fpm start
nginx