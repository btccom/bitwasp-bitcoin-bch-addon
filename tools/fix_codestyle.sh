#!/bin/bash
cd $(git rev-parse --show-toplevel)

vendor/bin/phpcbf -n --standard=PSR1,PSR2 src test
