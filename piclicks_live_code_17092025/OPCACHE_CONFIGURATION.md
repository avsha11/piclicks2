# PHP OPcache Configuration

OPcache improves PHP performance by storing precompiled script bytecode in memory, eliminating the need for PHP to load and parse scripts on each request.

## Check if OPcache is Enabled

Run this command to check:
```bash
php -i | grep opcache
```

Or create a PHP file with `<?php phpinfo(); ?>` and look for "Zend OPcache" section.

## Recommended OPcache Settings

Add or update these settings in your `php.ini` file:

```ini
; Enable OPcache
opcache.enable=1

; Enable OPcache for CLI (optional, useful for development)
opcache.enable_cli=0

; Memory size for OPcache (adjust based on your server)
; 128MB is good for most applications
opcache.memory_consumption=128

; Maximum number of files that can be stored in the cache
opcache.max_accelerated_files=10000

; How often to check for script changes (in seconds)
; 0 = check on every request (development)
; 60 = check every 60 seconds (production)
opcache.revalidate_freq=60

; Interned strings buffer size (8MB is usually sufficient)
opcache.interned_strings_buffer=8

; Maximum memory for interned strings
opcache.max_wasted_percentage=5

; Enable file-based caching (faster)
opcache.file_cache=/tmp/opcache

; Validate timestamps (set to 0 in production for better performance)
opcache.validate_timestamps=1

; Fast shutdown (improves performance)
opcache.fast_shutdown=1
```

## Production Settings

For production, use these more aggressive settings:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
opcache.fast_shutdown=1
```

**Note:** When `opcache.validate_timestamps=0`, you must restart PHP or clear OPcache after deploying new code.

## Clear OPcache

To clear OPcache without restarting PHP:

1. Create a file `clear_opcache.php` in your public directory:
```php
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared!";
} else {
    echo "OPcache is not enabled.";
}
```

2. Access it via browser: `http://yourdomain.com/clear_opcache.php`
3. Delete the file after use for security

Or restart your PHP-FPM service:
```bash
# For systemd
sudo systemctl restart php-fpm

# For service
sudo service php-fpm restart
```

## Verify OPcache is Working

Check OPcache statistics:
```php
<?php
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    print_r($status);
} else {
    echo "OPcache is not enabled.";
}
```

## XAMPP Specific

If using XAMPP on Windows, edit `C:\xampp\php\php.ini` and restart Apache.

## Benefits

- **40-60% faster page loads** for PHP applications
- Reduced server CPU usage
- Better handling of high traffic
- Lower memory usage per request

