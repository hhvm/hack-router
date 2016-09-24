#!/bin/bash
set -e
cd /var/test

echo '********************'

hhvm --version

echo '********************'

hh_client
hhvm vendor/bin/phpunit
