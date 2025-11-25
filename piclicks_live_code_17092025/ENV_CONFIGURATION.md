# Environment Configuration for Performance

Add or update these settings in your `.env` file:

```env
# Set log level to warning to reduce verbose logging in production
LOG_LEVEL=warning

# Ensure cache driver is set (file is default, but can use redis/memcached for better performance)
CACHE_DRIVER=file
```

**Note:** After updating `.env`, clear config cache:
```bash
php artisan config:clear
php artisan config:cache
```

