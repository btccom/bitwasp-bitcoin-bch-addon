#!/bin/bash

wget https://scrutinizer-ci.com/ocular.phar

if [ "${OCULAR_TOKEN}" != "" ]; then
    php ocular.phar code-coverage:upload --format=php-clover build/coverage.clover --access-token=$(OCULAR_TOKEN)
else
    php ocular.phar code-coverage:upload --format=php-clover build/coverage.clover
fi
