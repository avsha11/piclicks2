# Quick Fix: Get Sandbox API Keys

## You're Right - This Should Just Work!

Your `.env` file was deleted/missing. Getting new sandbox keys will fix it in 10 minutes.

---

## Step 1: PayPal Sandbox (5 minutes) - REQUIRED

1. **Go to**: https://developer.paypal.com/dashboard/
2. **Sign in** (use your PayPal account or create free developer account)
3. **Click**: "Apps & Credentials" (top menu)
4. **Make sure** you're on the "Sandbox" tab (NOT Live)
5. **Look for** "Default Application" or click "Create App"
6. **You'll see**:
   - Client ID: `AbcdEfGhIjKlMnOpQrStUvWxYz...` (long string)
   - Secret: Click "Show" to reveal
7. **Copy both**

---

## Step 2: Google Maps API (5 minutes) - REQUIRED

1. **Go to**: https://console.cloud.google.com/
2. **Sign in** with Google account
3. **Create project**: 
   - Click "Select a project" (top)
   - Click "New Project"
   - Name: "Piclicks Dev"
   - Click "Create"
4. **Enable APIs**:
   - Click "Enable APIs and Services" (big blue button)
   - Search "Places API" → Click it → Enable
   - Go back, search "Maps JavaScript API" → Click it → Enable
5. **Create API Key**:
   - Click "Credentials" (left sidebar)
   - Click "Create Credentials" → "API Key"
   - Copy the key: `AIzaSyXXXXXXXXXXXXXXXXXXXXXXXX`

---

## Step 3: Update .env (1 minute)

```powershell
notepad "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025\.env"
```

**Find these lines:**
```
GOOGLE_API_KEY=DEMO_KEY
PAYPAL_CLIENT_ID=DEMO
PAYPAL_CLIENT_SECRET=DEMO
```

**Replace with your real keys:**
```
GOOGLE_API_KEY=AIzaSyXXXXXXXXXXXXXXXXXXXXXXXX
PAYPAL_CLIENT_ID=AbcdEfGhIjKlMnOpQrStUvWxYz1234567890
PAYPAL_CLIENT_SECRET=EFghIjKlMnOpQrStUvWxYz1234567890-ABCD
```

**Save and close**

---

## Step 4: Restart Everything (1 minute)

```powershell
cd "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear
```

Then:
1. Open XAMPP Control Panel
2. Stop Apache
3. Start Apache
4. Refresh checkout page (Ctrl+F5)

---

## ✅ It Will Work

After this:
- ✅ No more 400 errors
- ✅ PayPal buttons will appear
- ✅ Address autocomplete will work
- ✅ You can test print files with checkout

**Sandbox = Free testing with fake money**

---

## Need Help Getting Keys?

If you get stuck at any step, let me know which step and I'll help you through it.

