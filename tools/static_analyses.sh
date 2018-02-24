#!/bin/bash
if [ "${ANALYSES}" = "true" ]; then
    cd $(git rev-parse --show-toplevel)

    vendor/bin/phpcs -n --standard=PSR1,PSR2 src test && echo "phpcs: OK" || exit 1

    if [ "${MUTATION_TEST}" = "true" ]; then
        if [ ! -f "infection.phar" ]; then
            wget https://github.com/infection/infection/releases/download/0.7.1/infection.phar
            wget https://github.com/infection/infection/releases/download/0.7.1/infection.phar.pubkey
            chmod +x infection.phar
        fi
        ./infection.phar
    fi
fi
