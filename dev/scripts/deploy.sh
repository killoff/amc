#!/bin/bash

echo "Updating codebase"
git pull

echo "var/ directory cleanup"
rm -rf var/cache/* var/di/* var/generation/* var/view_preprocessed/*

echo "db schema and data upgrade: setup:upgrade"
bin/magento setup:upgrade

echo "compilation: setup:di:compile"
bin/magento setup:di:compile

echo "deploy static content: setup:static-content:deploy"
bin/magento setup:static-content:deploy en_US ru_RU
