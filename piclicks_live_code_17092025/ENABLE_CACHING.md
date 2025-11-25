# Enable Laravel Caching

To enable Laravel caching for optimal performance, run these commands:

```bash
cd piclicks_live_code_17092025
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Note:** After running these commands:
- Config changes require running `php artisan config:cache` again
- Route changes require running `php artisan route:cache` again
- View changes require running `php artisan view:cache` again

To clear all caches:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

