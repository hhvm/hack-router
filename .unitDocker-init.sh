#!/bin/bash

set -e

apt-get update -y
apt-get install -y curl git
apt-get clean
rm -rf /var/lib/apt/lists/*

mkdir /opt/composer
curl -sS https://getcomposer.org/installer | \
  hhvm --php -- --install-dir=/opt/composer
touch /opt/composer/.hhconfig

hhvm /opt/composer/composer.phar install
