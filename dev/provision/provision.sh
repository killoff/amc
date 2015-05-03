#!/bin/bash

# refresh packages
echo "Refreshing packages"
apt-get update > /tmp/vagrant_log 2>&1
apt-get upgrade > /tmp/vagrant_log 2>&1
echo "done"

echo "Setting Locale Settings"
export LANGUAGE="en_US.UTF-8"
echo 'LANGUAGE="en_US.UTF-8"' >> /etc/default/locale
echo 'LC_ALL="en_US.UTF-8"' >> /etc/default/locale

echo "Installing vim and set as default editor"
apt-get install -y vim vim-doc vim-scripts mc >> /tmp/vagrant_log 2>&1
update-alternatives --set editor /usr/bin/vim.basic
echo "done"

# install Apache and PHP
echo "Installing Apache and PHP"
apt-get install -y php-apc php5 php5-cli php5-curl php5-mhash php5-gd php5-intl php5-mcrypt php5-gd php5-mysql php-pear php5-sqlite php5-dev php5-memcached >> /tmp/vagrant_log 2>&1
echo "done"

echo "Installing Memcached"
apt-get install memcached
#service memcached start
echo "done"

# configure Apache and PHP
echo "Configuring Apache and PHP"
a2dissite 000-default
cp /vagrant/dev/provision/m2.conf /etc/apache2/sites-available/m2.conf
a2ensite m2
a2enmod rewrite

php5enmod mcrypt

a2enmod ssl
mkdir /etc/apache2/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt -subj "/C=GB/ST=London/L=London/O=Global Security/OU=IT Department/CN=example.com" >> /tmp/vagrant_log 2>&1

cp /vagrant/dev/provision/php.ini /etc/php5/apache2/php.ini
cp /vagrant/dev/provision/php.ini /etc/php5/cli/php.ini
# cp /vagrant/dev/provision/xdebug.ini /etc/php5/apache2/conf.d/20-xdebug.ini
sed -i 's/\(APACHE_RUN_USER=\)www-data/\1vagrant/g' /etc/apache2/envvars
chown vagrant:www-data /var/lock/apache2
service apache2 restart
echo "done"

# install MySQL
echo "Installing MySQL server"
export DEBIAN_FRONTEND=noninteractive
apt-get -q -y install mysql-server-5.6 >> /tmp/vagrant_log 2>&1
echo "done"

# install Git
echo "Installing Git"
apt-get install -y git-core >> /tmp/vagrant_log 2>&1
echo "done"

# install composer
echo "Installing composer"
if [ ! -f "/usr/local/bin/composer" ];
then
    php -r "readfile('https://getcomposer.org/installer');" | php
    mv composer.phar /usr/local/bin/composer
fi

echo "done"

echo "Change SSH login dir"
echo "cd /vagrant" >> /home/vagrant/.bashrc
echo "done"

echo "Creating MySQL DB"
mysql -uroot -e "DROP DATABASE IF EXISTS m2;"
mysql -uroot -e "CREATE DATABASE m2;"
echo "done"

echo "Setting Up Folders Perms"
find . -type d -exec chmod 700 {} \; && find . -type f -exec chmod 600 {} \;
chmod 777 -R /vagrant/app/etc /vagrant/var /vagrant/pub/static /vagrant/dev/provision/magento_install.sh

/vagrant/dev/provision/magento_install.sh
/vagrant/dev/provision/magento_disable_modules.sh
