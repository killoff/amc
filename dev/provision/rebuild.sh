#!/bin/bash
SRC_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
APP_DIR="$(dirname "$SRC_DIR")"
APP_DIR=$( cd "$(dirname "$APP_DIR")" && pwd )
LOG_DIR="$APP_DIR/var"

mkdir -p $LOG_DIR

cd $APP_DIR

git pull >> $LOG_DIR/rebuild.log 2>&1
composer install >> $LOG_DIR/rebuild.log 2>&1

bin/magento setup:upgrade >> $LOG_DIR/rebuild.log 2>&1
bin/magento cache:flush >> $LOG_DIR/rebuild.log 2>&1
