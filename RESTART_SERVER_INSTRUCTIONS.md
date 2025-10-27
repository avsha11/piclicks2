# How to Restart Your Web Server to Load .env File

## The Problem
The `.env` file has been created with API keys, but your web server is still using the old cached configuration (empty keys).

## Solution: Restart Your Web Server

Choose the method that matches how you're running your Laravel application:

---

### Option 1: Laravel Built-in Server (`php artisan serve`)

**If you started your server with `php artisan serve`:**

1. **Stop the server:**
   - Find the terminal/command prompt window where the server is running
   - Press `Ctrl + C` to stop it

2. **Restart the server:**
   ```bash
   cd "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"
   php artisan serve
   ```

3. **Refresh your browser** at `http://localhost:8000/checkout`

---

### Option 2: XAMPP

**If you're using XAMPP:**

1. **Open XAMPP Control Panel**
2. **Stop Apache:**
   - Click "Stop" button next to Apache
3. **Start Apache:**
   - Click "Start" button next to Apache
4. **Refresh your browser** at `http://localhost:8000/checkout`

---

### Option 3: WAMP

**If you're using WAMP:**

1. **Right-click WAMP icon** in system tray
2. **Click "Restart All Services"**
3. **Refresh your browser** at `http://localhost:8000/checkout`

---

### Option 4: Laragon

**If you're using Laragon:**

1. **Open Laragon**
2. **Click "Stop All"** button
3. **Click "Start All"** button
4. **Refresh your browser** at `http://localhost:8000/checkout`

---

### Option 5: Clear Cache (Alternative)

If you can't restart the server or don't know which server you're using, try clearing Laravel's cache:

**Find where PHP is installed** (common locations):
- `C:\xampp\php\php.exe`
- `C:\wamp64\bin\php\php8.x\php.exe`
- `C:\laragon\bin\php\php8.x\php.exe`
- `C:\php\php.exe`

**Then run these commands** (replace `C:\xampp\php\php.exe` with your PHP path):

```powershell
cd "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"

C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear
C:\xampp\php\php.exe artisan view:clear
```

---

## How to Verify It's Fixed

After restarting your server:

1. **Clear browser cache:**
   - Press `Ctrl + Shift + Delete`
   - Clear "Cached images and files"
   - Or do a hard refresh: `Ctrl + F5`

2. **Reload checkout page:**
   - Go to `http://localhost:8000/checkout`

3. **Open browser console** (F12):
   - You should now see different errors mentioning "DEMO_KEY" or "DEMO" instead of empty strings
   - The URLs should look like:
     - `https://www.paypal.com/sdk/js?client-id=DEMO&components=buttons`
     - `https://maps.googleapis.com/maps/api/js?key=DEMO_KEY&libraries=places`

4. **If you still see `client-id=` or `key=` (empty):**
   - The server wasn't restarted properly
   - Try the cache clearing method above

---

## Next Step After Server Restart

Once the server is restarted and you see the demo values being used, you'll need to:

1. **Get real API keys** (see `CHECKOUT_API_KEYS_FIX.md`)
2. **Update `.env` file** with real keys
3. **Restart server again** to load the real keys

---

## Quick Troubleshooting

**Still seeing empty `client-id=` or `key=`?**

Try this diagnostic:

1. Open PowerShell in the project directory
2. Run:
   ```powershell
   cd "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"
   Get-Content .env | Select-String "GOOGLE_API_KEY|PAYPAL_CLIENT_ID"
   ```

3. You should see:
   ```
   GOOGLE_API_KEY=DEMO_KEY
   PAYPAL_CLIENT_ID=DEMO
   ```

4. If you see these values but the browser still shows empty, the server MUST be restarted.

---

## Need Help?

Can't find your server type or having issues? Let me know:
- How did you start the Laravel application?
- What terminal command did you use?
- Are you using any GUI tools like XAMPP, WAMP, or Laragon?

