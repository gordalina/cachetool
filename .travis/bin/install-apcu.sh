#!/bin/bash

if [ "$TRAVIS_PHP_VERSION" == "5.3" ]
then
    exit 0
fi

# this is helpful to compile extension
sudo apt-get install autoconf

# install this version
APCU=4.0.6

# compile manually, because `pecl install apcu-beta` keep asking questions
wget http://pecl.php.net/get/apcu-$APCU.tgz
tar zxvf apcu-$APCU.tgz
cd "apcu-${APCU}"
phpize && ./configure && make install && echo "Installed ext/apcu-${APCU}"
