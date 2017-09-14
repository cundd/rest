#!/bin/sh

#
# Composer install script used when building the Docker image
#

EXPECTED_SIGNATURE=$(curl -s https://composer.github.io/installer.sig)

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")

if [ "$EXPECTED_SIGNATURE" = "$ACTUAL_SIGNATURE" ]
then
    php composer-setup.php --install-dir=/usr/local/bin
    RESULT=$?
    rm composer-setup.php
    exit ${RESULT}
else
    >&2 echo 'ERROR: Invalid installer signature'
    rm composer-setup.php
    exit 1
fi