#!/bin/bash

# Script to prepare the codebase for Ubuntu 22.04.05 with WHM cPanel
# This script will install required PHP extensions and fix composer issues

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print status messages
print_status() {
    echo -e "${YELLOW}[*] $1${NC}"
}

# Function to print success messages
print_success() {
    echo -e "${GREEN}[+] $1${NC}"
}

# Function to print error messages
print_error() {
    echo -e "${RED}[-] $1${NC}"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root or with sudo"
    exit 1
fi

print_status "Setting up environment for Laravel application on Ubuntu 22.04.05 with WHM cPanel"

# Install required PHP extensions
print_status "Installing required PHP extensions..."

# Check if we're using EasyApache 4 (cPanel)
if [ -d "/opt/cpanel/ea-php81" ]; then
    # EasyApache 4 with PHP 8.1
    print_status "Detected EasyApache 4 with PHP 8.1"
    
    # Install fileinfo extension
    if yum install -y ea-php81-php-fileinfo; then
        print_success "PHP fileinfo extension installed successfully"
    else
        print_error "Failed to install PHP fileinfo extension"
        exit 1
    fi
    
    # Restart PHP-FPM
    if systemctl restart ea-php81-php-fpm; then
        print_success "PHP-FPM restarted successfully"
    else
        print_error "Failed to restart PHP-FPM"
        exit 1
    fi
else
    # Standard Ubuntu PHP installation
    print_status "Using standard Ubuntu PHP installation"
    
    # Install PHP extensions
    if apt-get update && apt-get install -y php8.1-fileinfo; then
        print_success "PHP fileinfo extension installed successfully"
    else
        print_error "Failed to install PHP fileinfo extension"
        exit 1
    fi
    
    # Restart PHP-FPM
    if systemctl restart php8.1-fpm; then
        print_success "PHP-FPM restarted successfully"
    else
        print_error "Failed to restart PHP-FPM"
        exit 1
    fi
fi

# Fix composer issues
print_status "Fixing composer issues..."

# Remove composer.lock to regenerate it
if [ -f "composer.lock" ]; then
    print_status "Removing composer.lock to regenerate it"
    rm composer.lock
    print_success "composer.lock removed"
fi

# Run composer update with ignore-platform-req for fileinfo (in case it's not yet loaded)
print_status "Running composer update..."
if composer update --ignore-platform-req=ext-fileinfo --with-all-dependencies; then
    print_success "Composer update completed successfully"
else
    print_error "Composer update failed. Please check the error messages above."
    exit 1
fi

print_success "Setup completed successfully!"
print_status "Your Laravel application should now be compatible with Ubuntu 22.04.05 running WHM cPanel"
print_status "Next steps:"
print_status "1. Make sure your .env file is properly configured"
print_status "2. Run 'php artisan migrate' to set up your database"
print_status "3. Run 'php artisan storage:link' to create the storage symlink"
print_status "4. Set proper permissions on storage and bootstrap/cache directories"

exit 0
