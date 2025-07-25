#!/bin/bash

# Start Docker container
echo "Starting Docker container..."
docker-compose up -d

# Wait for container to be ready
echo "Waiting for container to be ready..."
sleep 10

# Run tests
echo "Running tests..."
docker-compose exec php vendor/bin/phpunit

# Show test results
echo "Test execution completed."