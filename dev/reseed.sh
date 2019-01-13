#!/usr/bin/env bash

cd /vagrant/

mysql -uroot -proot mailserver -e "DELETE FROM alias"
mysql -uroot -proot mailserver -e "DELETE FROM mailbox"

php artisan migrate:reset
php artisan doctrine:migration:refresh
php artisan migrate
php artisan hms:database:refresh-viwes
php artisan hms:database:refresh-procedures
php artisan permission:defaults
php artisan db:seed
php artisan passport:install