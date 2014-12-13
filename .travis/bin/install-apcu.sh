#!/bin/bash

if [ "$TRAVIS_PHP_VERSION" == "5.5" ] || [ "$TRAVIS_PHP_VERSION" == "5.6" ]
then
    exit 0
fi

echo yes | sudo pecl install channel://pecl.php.net/apcu-4.0.7
