#!/bin/bash

# Install Composer if not already installed
if ! [ -x "$(command -v composer)" ]; then
  echo "Installing Composer..."
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
fi

# Install dependencies
echo "Installing dependencies..."
composer install

# Run tests
echo "Running tests..."
vendor/bin/phpunit

# Show test results
echo "Test execution completed."