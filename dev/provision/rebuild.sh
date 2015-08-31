#!/bin/bash
SRC_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
APP_DIR="$(dirname "$SRC_DIR")"
APP_DIR=$( cd "$(dirname "$APP_DIR")" && pwd )
LOG_DIR="$APP_DIR/var"

mkdir -p $LOG_DIR

git pull >> $LOG_DIR/rebuild.log 2>&1
composer install >> $LOG_DIR/rebuild.log 2>&1

magento setup:uninstall --no-interaction >> $LOG_DIR/rebuild.log 2>&1

mkdir -p $LOG_DIR

magento setup:install \
--base-url=http://95.85.27.59:8000/ \
--backend-frontname=admin \
--db-engine=mysql \
--db-host=localhost \
--db-name=magento \
--db-user=root \
--db-password=password \
--admin-firstname=John \
--admin-lastname=Doe \
--admin-email=admin@example.com \
--admin-user=admin \
--admin-password=123123q \
--admin-use-security-key=0 \
--language=en_US \
--currency=USD \
--timezone=America/Chicago \
--cleanup-database \
--sales-order-increment-prefix="ORD$" \
--session-save=db >> $LOG_DIR/rebuild.log 2>&1
