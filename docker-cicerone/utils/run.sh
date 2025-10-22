#!/bin/bash

cleanup() {
    service mariadb stop
    service apache2 stop
    exit 0
}

# Capture signals, so that once completed, everything is stopped
trap cleanup SIGTERM SIGINT


service mariadb start
service apache2 start
#bash
while true; do sleep 1; done