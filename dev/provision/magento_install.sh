#!/bin/bash

php -f /vagrant/setup/index.php install --db_host=localhost --db_name=m2 --db_user=root --backend_frontname=admin --base_url=http://m2.loc/ --language=en_US --timezone=America/Chicago --currency=USD --admin_username=admin --admin_password=123123q --admin_email=admin@example.com --admin_firstname=John --admin_lastname=Smith --admin_use_security_key=0
