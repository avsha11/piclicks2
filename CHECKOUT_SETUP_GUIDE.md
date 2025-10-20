# Checkout Setup Guide

## Issues Found

1. ❌ **Google Maps doesn't work** - API key not configured
2. ❌ **PayPal button missing** - PayPal credentials not configured
3. ℹ️ **Credit card option** - Not implemented (only PayPal is available)

---

## 🔧 Fix #1: Configure Google Maps API

### Step 1: Get Google Maps API Key

1. Go to: https://console.cloud.google.com/
2. Create a new project or select existing one
3. Enable **Maps JavaScript API** and **Places API**
4. Go to "Credentials" → "Create Credentials" → "API Key"
5. Copy your API key

### Step 2: Add to .env File

1. Look for `.env` file in `piclicks_live_code_17092025/` folder
2. If it doesn't exist, copy `.env.example` to `.env`:
   ```bash
   cd piclicks_live_code_17092025
   copy .env.example .env
   ```
3. Open `.env` and add:
   ```env
   GOOGLE_API_KEY=AIzaSy...your_actual_key_here
   ```
4. Restart your Laravel server

### Step 3: Verify
Go to checkout → shipping step → try typing an address. You should now see Google autocomplete suggestions!

---

## 🔧 Fix #2: Configure PayPal

### Option A: Use PayPal Sandbox (For Testing)

1. Go to: https://developer.paypal.com/
2. Log in with your PayPal account
3. Go to "Dashboard" → "My Apps & Credentials"
4. Under "Sandbox", click "Create App"
5. Copy:
   - **Client ID**
   - **Secret** (click "Show" to reveal it)

### Option B: Use Live PayPal (For Production)

1. Same steps as above, but use the "Live" tab instead of "Sandbox"
2. Your app must be approved by PayPal first

### Step 2: Add to .env File

Open `.env` and add:

```env
# For Sandbox (Testing)
PAYPAL_CLIENT_ID=your_sandbox_client_id_here
PAYPAL_CLIENT_SECRET=your_sandbox_secret_here
PAYPAL_API_BASEURL=https://api-m.sandbox.paypal.com/

# For Live (Production) - uncomment when ready
# PAYPAL_CLIENT_ID=your_live_client_id_here
# PAYPAL_CLIENT_SECRET=your_live_secret_here
# PAYPAL_API_BASEURL=https://api-m.paypal.com/
```

### Step 3: Verify
1. Restart your server
2. Go to checkout → payment step
3. You should now see the **PayPal button**!

---

## ℹ️ About Credit Card Payments

**Current Status:** The application only has PayPal integration. There is **NO credit card/Stripe integration** in the code.

### To Add Credit Card Payments:

You would need to integrate either:
- **Stripe** (most popular)
- **Square**
- **Authorize.Net**
- Or another payment gateway

This requires:
1. Creating an account with the payment provider
2. Getting API keys
3. Installing the PHP SDK
4. Adding the payment form to checkout page
5. Creating backend endpoints to process payments

**Would you like me to add Stripe integration?** Let me know and I can implement it!

---

## 📝 Quick Setup Checklist

### For Google Maps:
- [ ] Get Google Maps API key
- [ ] Add `GOOGLE_API_KEY` to `.env`
- [ ] Restart server
- [ ] Test address autocomplete

### For PayPal:
- [ ] Create PayPal developer account
- [ ] Create sandbox app
- [ ] Get Client ID and Secret
- [ ] Add `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, and `PAYPAL_API_BASEURL` to `.env`
- [ ] Restart server
- [ ] Test PayPal button appears

---

## 🧪 Testing PayPal Sandbox

Once configured, you can test with these PayPal sandbox accounts:

**Buyer Account (to make test payments):**
- Create one at: https://developer.paypal.com/dashboard/accounts
- Or use the default sandbox buyer account

**You can use sandbox credit card:**
- Card: Any test card from PayPal sandbox
- Money comes from the sandbox buyer account, not real money!

---

## 🚨 Common Issues

### Google Maps Error: "This page can't load Google Maps correctly"
- Your API key is invalid or not enabled for the Maps/Places APIs
- Billing must be enabled on your Google Cloud project

### PayPal Button Not Appearing
- Check browser console for errors
- Verify credentials in `.env` are correct
- Make sure you're using the right API base URL (sandbox vs live)
- Check `storage/logs/laravel.log` for errors

### "Invalid credentials" from PayPal
- Double-check Client ID and Secret (no extra spaces!)
- Make sure you're using sandbox credentials with sandbox URL
- Make sure you're using live credentials with live URL

---

## 🎯 Next Steps

1. **Set up Google Maps API** (5 minutes)
2. **Set up PayPal Sandbox** (10 minutes)
3. **Test the checkout flow**
4. **Optional: Request Stripe integration if you want credit card payments**

Need help with any step? Let me know!

