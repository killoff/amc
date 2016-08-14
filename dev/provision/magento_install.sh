#!/bin/bash

magento setup:install \
--base-url=http://amc.loc/ \
--backend-frontname=admin \
--db-engine=mysql \
--db-host=localhost \
--db-name=m2 \
--db-user=root \
--db-password= \
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
--session-save=db \
--key=123123q
