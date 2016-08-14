#!/bin/bash

echo "Setting Locale Settings"
export LANGUAGE="en_US.UTF-8"
echo 'LANGUAGE="en_US.UTF-8"' >> /etc/default/locale
echo 'LC_ALL="en_US.UTF-8"' >> /etc/default/locale

echo "Installing vim/mc and set MC as default editor"
apt-get install -y vim vim-doc vim-scripts mc >> /tmp/vagrant_log 2>&1
update-alternatives --set editor /usr/bin/mcedit >> /tmp/vagrant_log 2>&1

echo "Installing Apache and PHP"
apt-get install -y php php-cli php-curl php-gd php-intl php-mcrypt php-mbstring php-zip php-mysql php-dev php-xdebug apache2 libapache2-mod-php >> /tmp/vagrant_log 2>&1

echo "Configuring Apache and PHP"
a2dissite 000-default >> /tmp/vagrant_log 2>&1
cp /vagrant/dev/provision/amc.conf /etc/apache2/sites-available/amc.conf
a2ensite amc >> /tmp/vagrant_log 2>&1
a2enmod rewrite >> /tmp/vagrant_log 2>&1

phpenmod mcrypt >> /tmp/vagrant_log 2>&1
phpenmod xdebug >> /tmp/vagrant_log 2>&1

a2enmod ssl >> /tmp/vagrant_log 2>&1
mkdir /etc/apache2/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt -subj "/C=GB/ST=London/L=London/O=Global Security/OU=IT Department/CN=example.com" >> /tmp/vagrant_log 2>&1

cp /vagrant/dev/provision/php/php7.ini /etc/php/7.0/apache2/php.ini
cp /vagrant/dev/provision/php/php7.ini /etc/php/7.0/cli/php.ini
cp /vagrant/dev/provision/php/20-xdebug.ini /etc/php/7.0/apache2/conf.d/20-xdebug.ini
sed -i 's/\(APACHE_RUN_USER=\)www-data/\1vagrant/g' /etc/apache2/envvars
chown vagrant:www-data /var/lock/apache2
service apache2 restart >> /tmp/vagrant_log 2>&1

echo "Installing MySQL server"
export DEBIAN_FRONTEND=noninteractive
apt-get -q -y install mysql-server >> /tmp/vagrant_log 2>&1


echo "Creating MySQL DB"
mysql -uroot -e "DROP DATABASE IF EXISTS amc;"
mysql -uroot -e "CREATE DATABASE amc;"
mysql -uroot -e "CREATE USER 'amc'@'localhost' IDENTIFIED BY 'amc'; GRANT ALL PRIVILEGES ON * . * TO 'amc'@'localhost'; FLUSH PRIVILEGES;"

echo "Installing composer"
if [ ! -f "/usr/local/bin/composer" ];
then
    php -r "readfile('https://getcomposer.org/installer');" | php  >> /tmp/vagrant_log 2>&1
    mv composer.phar /usr/local/bin/composer >> /tmp/vagrant_log 2>&1
fi

echo "Installing dependencies"
cd /vagrant
mkdir -p /home/vagrant/.config/composer
cp /vagrant/dev/provision/composer/auth.json /home/vagrant/.config/composer/auth.json
composer install --no-interaction >> /tmp/vagrant_log 2>&1

echo "Change SSH login dir"
echo "cd /vagrant" >> /home/vagrant/.bashrc

echo "Cleaning Up previous installation files"
rm -rf /vagrant/app/etc/config.php /vagrant/app/etc/env.php /vagrant/var/cache/* /vagrant/var/generation/* /vagrant/var/log/* /vagrant/var/page_cache/* /vagrant/var/view_preprocessed/*

echo "Setting Up Folders Perms"
find . -type d -exec chmod 700 {} \; && find . -type f -exec chmod 600 {} \;
chmod 777 -R /vagrant/app/etc /vagrant/var /vagrant/pub/static /vagrant/bin /vagrant/dev/provision/magento_install.sh

echo "Setting up application"
PATH="$PATH:/vagrant/bin" >> ~/.profile
/vagrant/dev/provision/magento_install.sh >> /tmp/vagrant_log 2>&1
/vagrant/dev/provision/magento_disable_modules.sh >> /tmp/vagrant_log 2>&1
/vagrant/dev/provision/magento_deploy_static.sh >> /tmp/vagrant_log 2>&1

echo "Setting Up Cronjobs"
cat /vagrant/dev/provision/crontab | crontab -; crontab -l
