#!/bin/bash

# TheOneDesk Deployment Script
echo "Starting TheOneDesk deployment..."

# Copy environment file if not exists
if [ ! -f .env ]; then
    echo "Creating .env file from .env.docker..."
    cp .env.docker .env
    
    # Generate application key
    echo "Generating application key..."
    docker-compose exec app php artisan key:generate
fi

# Build and start containers
echo "Building and starting Docker containers..."
docker-compose up -d --build

# Install dependencies
echo "Installing Composer dependencies..."
docker-compose exec app composer install --optimize-autoloader --no-dev

# Run migrations
echo "Running database migrations..."
docker-compose exec app php artisan migrate --force

# Optimize Laravel
echo "Optimizing Laravel..."
docker-compose exec app php artisan optimize
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Build frontend assets
echo "Building frontend assets..."
docker-compose exec app npm install
docker-compose exec app npm run production

# Create storage link
echo "Creating storage link..."
docker-compose exec app php artisan storage:link

echo "Deployment completed successfully!"
