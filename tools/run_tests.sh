#!/bin/bash
cd $(git rev-parse --show-toplevel)

if [ "${TESTS}" = "true" ]; then
    if [ "${COVERAGE_HTML}" = "true" ]; then
        vendor/bin/phpunit --coverage-html=build/
    elif [ "${COVERAGE}" = "true" ]; then
        vendor/bin/phpunit --coverage-clover=coverage.clover
    else
        vendor/bin/phpunit
    fi
fi
