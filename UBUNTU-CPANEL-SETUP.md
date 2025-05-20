# Ubuntu 22.04.05 WHM cPanel Setup Guide

This guide provides instructions for setting up the Laravel application on Ubuntu 22.04.05 with WHM cPanel.

## Issues Fixed

1. **Missing PHP Extensions**
   - Added the required `fileinfo` PHP extension
   
2. **Composer Dependency Issues**
   - Updated PHP version requirements to support PHP 8.1
   - Added explicit requirement for `ext-fileinfo` extension
   - Fixed issues with package version conflicts

## Automatic Setup

We've provided a setup script that will automatically fix these issues:

```bash
# Make the script executable
chmod +x setup-ubuntu-cpanel.sh

# Run the script as root or with sudo
sudo ./setup-ubuntu-cpanel.sh
```

## Manual Setup

If you prefer to fix the issues manually, follow these steps:

### 1. Install Required PHP Extensions

For EasyApache 4 (cPanel):

```bash
# Install fileinfo extension
sudo yum install -y ea-php81-php-fileinfo

# Restart PHP-FPM
sudo systemctl restart ea-php81-php-fpm
```

For standard Ubuntu PHP:

```bash
# Install fileinfo extension
sudo apt-get update
sudo apt-get install -y php8.1-fileinfo

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

### 2. Fix Composer Issues

```bash
# Remove composer.lock to regenerate it
rm composer.lock

# Run composer update with ignore-platform-req for fileinfo (in case it's not yet loaded)
composer update --ignore-platform-req=ext-fileinfo --with-all-dependencies
```

## Post-Installation Steps

After running the setup script or manual steps, complete the following:

1. Configure your `.env` file with the correct database and application settings
2. Run database migrations:
   ```bash
   php artisan migrate
   ```
3. Create the storage symlink:
   ```bash
   php artisan storage:link
   ```
4. Set proper permissions on storage and bootstrap/cache directories:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R $USER:www-data storage bootstrap/cache
   ```

## Troubleshooting

### Composer Update Fails

If you still encounter issues with `composer update`, try the following:

```bash
# Clear composer cache
composer clear-cache

# Update with verbose output to see detailed errors
composer update -vvv --ignore-platform-req=ext-fileinfo --with-all-dependencies
```

### PHP Extension Issues

If you're having trouble with PHP extensions, verify they're properly installed:

```bash
# For EasyApache 4 (cPanel)
php -m | grep fileinfo

# Check PHP configuration
php --ini
```

### Permission Issues

If you encounter permission issues, ensure your web server has proper access:

```bash
# Set ownership to the appropriate user (replace 'cpanel-user' with your actual cPanel username)
chown -R cpanel-user:nobody /path/to/your/laravel/app

# Set proper permissions
find /path/to/your/laravel/app -type f -exec chmod 644 {} \;
find /path/to/your/laravel/app -type d -exec chmod 755 {} \;
chmod -R 775 /path/to/your/laravel/app/storage
chmod -R 775 /path/to/your/laravel/app/bootstrap/cache
```

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs/8.x)
- [cPanel Documentation](https://docs.cpanel.net/)
- [PHP 8.1 Documentation](https://www.php.net/manual/en/)
