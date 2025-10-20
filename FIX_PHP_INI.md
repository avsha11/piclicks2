# Quick Fix: Remove Imagick Warning

## Problem
You're seeing this warning:
```
PHP Warning: Unable to load dynamic library 'php_imagick'
```

## Solution (1 minute)

1. **Open this file:**
   ```
   C:\xampp\php\php.ini
   ```

2. **Find this line:**
   ```ini
   extension=php_imagick
   ```
   OR
   ```ini
   extension=imagick
   ```

3. **Comment it out by adding `;` at the start:**
   ```ini
   ;extension=php_imagick
   ```

4. **Save the file**

5. **Restart Apache in XAMPP Control Panel**

Done! The warning will disappear.

