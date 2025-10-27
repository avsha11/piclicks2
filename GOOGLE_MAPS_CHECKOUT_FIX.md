# Google Maps Checkout Address Autocomplete Fix

## Problem

On the checkout page, Google Maps address autocomplete is not working to fill in location details (address, city, state, postal code, country).

## Root Cause

The `.env` file currently has:
```
GOOGLE_API_KEY=DEMO_KEY
```

This is a placeholder value that doesn't work with the Google Maps Places API. The checkout page loads the Google Maps JavaScript API with this invalid key, causing the autocomplete to fail silently.

## Solution

You need to obtain a valid Google Maps API key and configure it properly.

### Step 1: Get a Google Maps API Key

1. **Go to Google Cloud Console**:
   - Visit: https://console.cloud.google.com/

2. **Create a Project** (if you don't have one):
   - Click "Select a project" → "New Project"
   - Name it something like "Piclicks"
   - Click "Create"

3. **Enable Required APIs**:
   - Go to "APIs & Services" → "Library"
   - Search for and enable:
     - **Places API** (for address autocomplete)
     - **Maps JavaScript API** (for map display)
     - **Geocoding API** (optional, for lat/lng lookups)

4. **Create API Key**:
   - Go to "APIs & Services" → "Credentials"
   - Click "Create Credentials" → "API Key"
   - Copy the generated API key

5. **Restrict API Key** (IMPORTANT for security):
   - Click on your new API key to edit it
   - Under "Application restrictions":
     - Select "HTTP referrers (websites)"
     - Add your website URLs:
       ```
       http://localhost/*
       http://localhost:8000/*
       https://yourdomain.com/*
       https://www.yourdomain.com/*
       ```
   - Under "API restrictions":
     - Select "Restrict key"
     - Select:
       - Places API
       - Maps JavaScript API
       - Geocoding API (if needed)
   - Click "Save"

### Step 2: Update Your .env File

1. Open your `.env` file (in the project root)
2. Replace the line:
   ```
   GOOGLE_API_KEY=DEMO_KEY
   ```
   With:
   ```
   GOOGLE_API_KEY=YOUR_ACTUAL_API_KEY_HERE
   ```

3. Save the file

### Step 3: Clear Config Cache

Run these commands to ensure Laravel picks up the new configuration:

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 4: Test the Checkout

1. Go to your checkout page
2. Click on the "Address" field (ID: `shopping_address`)
3. Start typing an address
4. You should now see Google's address suggestions appear
5. Select an address from the dropdown
6. The following fields should auto-fill:
   - City
   - State
   - Postal Code
   - Country

## How It Works

### Current Implementation

**File**: `piclicks_live_code_17092025/resources/views/front/checkout/checkout.blade.php`

**Line 569** - Loads Google Maps API:
```html
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&libraries=places"></script>
```

**Lines 779-818** - Autocomplete initialization:
```javascript
function initAutocomplete() {
    var input = document.getElementById("shopping_address");
    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.addListener("place_changed", function() {
        var place = autocomplete.getPlace();
        if (!place.geometry) {
            return;
        }
        let addressComponents = place.address_components;
        let city = "", state = "", country = "", postal_code = "";
        
        // Parse address components
        addressComponents.forEach((component) => {
            let types = component.types;
            if (types.includes("locality")) {
                city = component.long_name;
            } else if (types.includes("administrative_area_level_1")) {
                state = component.long_name;
            } else if (types.includes("country")) {
                country = component.long_name;
            } else if (types.includes("postal_code")) {
                postal_code = component.long_name;
            }
        });
        
        // Auto-fill form fields
        document.getElementById("shopping_city").value = city;
        document.getElementById("shopping_state").value = state;
        document.getElementById("shopping_postalcode").value = postal_code;
        
        // Select country in dropdown
        let countryDropdown = document.getElementById("shopping_country");
        for (let i = 0; i < countryDropdown.options.length; i++) {
            if (countryDropdown.options[i].text.trim().toLowerCase() === country.trim().toLowerCase()) {
                countryDropdown.value = countryDropdown.options[i].value;
                break;
            }
        }
        
        updateAmounts(); // Recalculate shipping costs
    });
}

// Initialize on page load
window.addEventListener("load", initAutocomplete);
```

## Troubleshooting

### Issue: Autocomplete still not working

**Check 1: API Key Restrictions**
- Make sure your domain is added to the HTTP referrer restrictions
- For local development, add `http://localhost/*` and `http://localhost:8000/*`

**Check 2: Browser Console Errors**
- Open browser Developer Tools (F12)
- Go to Console tab
- Look for errors like:
  - "Google Maps JavaScript API error: InvalidKeyMapError"
  - "This API key is not authorized to use this service or API"

**Check 3: Config Cache**
- Run: `php artisan config:clear`
- Refresh the page

**Check 4: API Billing**
- Google Maps requires billing to be enabled
- Go to Google Cloud Console → Billing
- Add a payment method (you get $200/month free credit)
- Note: Address autocomplete is usually free within the monthly quota

### Issue: API Key Error Messages

**"RefererNotAllowedMapError"**
- Your domain is not in the API key restrictions
- Add your domain to the HTTP referrer restrictions

**"ApiNotActivatedMapError"**
- The Places API is not enabled
- Go to APIs & Services → Library → Enable Places API

**"RequestDenied"**
- Billing is not enabled on your Google Cloud project
- Enable billing (you still get free credits)

## Cost Information

Google Maps API pricing (as of 2024):
- **Free Credits**: $200/month
- **Places Autocomplete**: ~$2.83 per 1,000 requests
- **Typical Usage**: Most small to medium sites stay within free tier

With $200 credit, you get approximately:
- ~70,000 autocomplete requests per month FREE

## Alternative: Disable Autocomplete (Not Recommended)

If you don't want to use Google Maps, you can have users manually enter their address. However, this provides a much worse user experience and increases cart abandonment.

The autocomplete is already implemented and just needs a valid API key to work.

## Security Best Practices

1. **Always restrict your API key**:
   - Use HTTP referrer restrictions
   - Restrict to specific APIs
   - Never commit API keys to version control

2. **Monitor usage**:
   - Set up billing alerts in Google Cloud Console
   - Monitor API usage regularly

3. **Rotate keys periodically**:
   - Generate new keys every 6-12 months
   - Revoke old keys after rotation

## Files Involved

- `.env` - Contains the Google API key configuration
- `piclicks_live_code_17092025/resources/views/front/checkout/checkout.blade.php` - Checkout page with Google Maps integration
- No code changes needed - just configuration

---

**Date**: October 16, 2025  
**Issue Type**: Configuration  
**Priority**: High (affects checkout UX)  
**Status**: Requires API key from user

Once you have your Google API key configured, the address autocomplete will work perfectly!

