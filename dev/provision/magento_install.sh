#!/bin/bash

php bin/magento setup:install --base_url=http://m2.loc/ \
--backend_frontname=admin \
--db_host=localhost \
--db_name=m2 \
--db_user=root \
--admin_firstname=John \
--admin_lastname=Doe \
--admin_email=admin@example.com \
--admin_user=admin \
--admin_password=123123q \
--admin_use_security_key=0 \
--language=en_US \
--currency=USD \
--timezone=America/Chicago \
--cleanup_database \
--sales_order_increment_prefix="ORD$" \
--session_save=db
