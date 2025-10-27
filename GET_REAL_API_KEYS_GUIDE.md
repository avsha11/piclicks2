# Get Real API Keys - Quick Guide

## Current Status ✅
Your checkout page is now loading correctly! The environment variables are working.

The errors you see are **EXPECTED** because the placeholder values (`DEMO`, `DEMO_KEY`) aren't real API credentials.

---

## Option 1: Get Real API Keys (Recommended)

### 🗺️ Google Maps API Key (5 minutes)

1. **Go to**: https://console.cloud.google.com/
2. **Sign in** with your Google account
3. **Create a project** or select existing
4. **Enable APIs**:
   - Click "Enable APIs and Services"
   - Search for "Places API" → Enable
   - Search for "Maps JavaScript API" → Enable
5. **Create credentials**:
   - Go to "Credentials" (left sidebar)
   - Click "Create Credentials" → "API Key"
   - Copy the API key
6. **Update .env**:
   ```
   GOOGLE_API_KEY=AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
   ```

### 💳 PayPal Sandbox Credentials (5 minutes)

1. **Go to**: https://developer.paypal.com/dashboard/
2. **Sign in** or create developer account
3. **Go to**: Apps & Credentials → Sandbox tab
4. **Create app**:
   - Click "Create App"
   - Enter app name (e.g., "Piclicks Dev")
   - Click "Create App"
5. **Copy credentials**:
   - Client ID (shown on the page)
   - Secret (click "Show" to reveal)
6. **Update .env**:
   ```
   PAYPAL_CLIENT_ID=AbcdEfGhIjKlMnOpQrStUvWxYz1234567890
   PAYPAL_CLIENT_SECRET=EFghIjKlMnOpQrStUvWxYz1234567890-ABCD
   PAYPAL_API_BASEURL=https://api-m.sandbox.paypal.com/
   ```

---

## Option 2: Quick Test Without Real Keys

If you want to **test the checkout flow without API functionality**, you can work around it:

### Disable PayPal temporarily
The PayPal error will prevent the payment section from loading. You can:
1. Skip the payment step for now
2. Or use a different payment method if available

### Disable Google Maps autocomplete
The address form will still work, you just won't have autocomplete suggestions.

---

## After Getting Real Keys

### Update the .env file:

1. **Open**: `piclicks_live_code_17092025\.env`
2. **Replace**:
   ```
   GOOGLE_API_KEY=DEMO_KEY
   PAYPAL_CLIENT_ID=DEMO
   PAYPAL_CLIENT_SECRET=DEMO
   ```
   
   **With your real keys**:
   ```
   GOOGLE_API_KEY=AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
   PAYPAL_CLIENT_ID=AbcdEfGhIjKlMnOpQrStUvWxYz1234567890
   PAYPAL_CLIENT_SECRET=EFghIjKlMnOpQrStUvWxYz1234567890-ABCD
   ```

3. **Clear cache**:
   ```powershell
   cd "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"
   C:\xampp\php\php.exe artisan config:clear
   C:\xampp\php\php.exe artisan cache:clear
   ```

4. **Restart Apache** in XAMPP

5. **Refresh** checkout page

---

## What Will Work After Adding Real Keys

✅ **Google Maps Autocomplete**:
- Type address → See suggestions
- Select address → Auto-fill city/state/zip/country

✅ **PayPal Payments**:
- PayPal button appears
- Click → PayPal login modal
- Complete payment (sandbox = fake money for testing)

---

## Security Notes for Real Keys

⚠️ **Google Maps API Key**:
- After creating, restrict it to your domain
- Set up billing alerts (free tier = $200/month credit)
- In production, restrict to your live domain

⚠️ **PayPal**:
- Use **Sandbox** credentials for development/testing
- Use **Live** credentials only on production server
- Never commit credentials to git

---

## Testing Without Real Keys (Alternative)

If you can't get real keys right now, you can modify the checkout to work without them:

### Option A: Comment out API scripts temporarily

Edit: `piclicks_live_code_17092025/resources/views/front/checkout/checkout.blade.php`

Line 569, change:
```php
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&libraries=places"></script>
```

To:
```php
{{-- Temporarily disabled --}}
{{-- <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&libraries=places"></script> --}}
```

And comment out the PayPal include wherever it's loaded.

### Option B: Make APIs optional

Wrap the API-dependent JavaScript in try-catch blocks to gracefully handle missing APIs.

---

## Quick Verification

After adding real keys, open checkout page console. You should see:
- ✅ No PayPal 400 error
- ✅ No Google Maps InvalidKey error
- ✅ PayPal buttons render
- ✅ Address autocomplete works

---

## Need Help?

**Getting real keys should take about 10 minutes total.** Both Google and PayPal have free tiers for development:
- Google: $200/month free credit
- PayPal Sandbox: Unlimited fake transactions for testing

**Can't get keys right now?** Let me know and I can help you:
1. Temporarily disable these features
2. Or set up mock/fallback behavior

