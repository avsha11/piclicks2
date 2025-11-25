<?php



use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\{Cart, Tile, Admin, Frame, DesignCollageModel, Countries, PriceManagement, Discount, OrderGiftcard,ArtGalleryFavourite};
use App\Services\PreviewRenderer;
use App\Repository\Eloquent\DesignCollageRepository;


if (!function_exists('getUserCartItems')) {

    // used from cart-item in offcanvas
    function getUserCartItems()

    {

        if (Auth::check()) {

            return Cart::with(['designCollage', 'designCollageMaster', 'giftcard'])->where('user_id', Auth::id())->get();
        } else {

            return Cart::with(['designCollage', 'designCollageMaster', 'giftcard'])->where('user_id', getUserId())->get();
        }

        return []; // Return an empty array if user is not logged in 

    }
}



if (!function_exists('getUserItemItems')) {

    function getUserItemItems($number, $priceId)
    {
        $countryCode = getCountryCode();
        $countryData = Countries::where('code', $countryCode)->first();

        $cost = 0;
        $cost = getFinalPrice($priceId, $number, $countryData->id);
        return (object) [
            'cost' => $cost,
        ];
    }
}

if (!function_exists('getUserAdminItems')) {

    function getUserAdminItems()

    {

        return Admin::first();
    }
}



if (!function_exists('getUserSalesTaxItems')) {

    function getUserSalesTaxItems($amount, $country = null)

    {

        $frameCost = 0;

        $country = getCountryCode();


        $countryData = $country ? Countries::where('code', $country)->first() ?? 0 : 0;


        $shippingAmount = $countryData['shipping_amount'] ?? 0;

        $saleTaxRate =  $countryData['vat'] ?? 0;



        if ($country == 'IL') {

            $data['saleAmount'] = ($saleTaxRate / 100) * $amount;
        } else {

            $data['saleAmount'] = 0;
        }



        $data['shippingAmount'] = $shippingAmount;

        $data['amount_Tax'] = $amount + $data['saleAmount'];


        $data['total'] = $amount + $frameCost + $shippingAmount;

        return $data;
    }
}





if (!function_exists('getCountryShippingCost')) {

    function getCountryShippingCost($country = null)

    {

        $shippingAmount = $country ? Countries::where('code', $country)->value('shipping_amount') ?? 0 : 0;

        return $shippingAmount;
    }
}



if (!function_exists('getUserFramesItems')) {

    function getUserFramesItems($frame_id)

    {

        if ($frame_id == 0) {

            return 0;
        } else {

            $data = Frame::where('frame_id', $frame_id)->first();

            return $data->cost;
        }
    }
}

if (!function_exists('getUserId')) {

    function getUserId()

    {

        // if (Auth::check()) {

        //     return Auth::id(); // Logged-in user ID

        // }



        // Get or generate guest ID

        if (session()->has('guest_id')) {

            $guestId = session()->get('guest_id');
        } else {

            $guestId = session()->get('guest_id', generateGuestId());

            session()->put('guest_id', $guestId);
        }

        // dd($guestId);

        return $guestId;
    }
}



if (!function_exists('generateGuestId')) {

    function generateGuestId()

    {

        return 'guest_' . uniqid();
    }
}

if (!function_exists('updateGuestCartToUser')) {

    function updateGuestCartToUser()

    {

        if (Auth::check()) {

            $realUserId = Auth::id();

            $guestId = session()->get('guest_id');

            // // Update guest cart items to the real user ID

            // Cart::where('user_id', $guestId)->update(['user_id' => $realUserId]);



            // // Remove guest session after updating cart

            // session()->forget('guest_id');



            if ($guestId) {

                // Fetch guest cart items

                $guestCartItems = Cart::where('user_id', $guestId)->get();



                foreach ($guestCartItems as $guestItem) {

                    $existingCartItem = Cart::where('user_id', $realUserId)

                        ->where('product_id', $guestItem->product_id)

                        ->first();



                    if ($existingCartItem) {

                        // Update quantity if product already exists

                        $existingCartItem->quantity += $guestItem->quantity;

                        $existingCartItem->save();



                        // Delete the duplicate guest cart item

                        $guestItem->delete();
                    } else {

                        // Assign guest cart item to the authenticated user

                        $guestItem->user_id = $realUserId;

                        $guestItem->save();
                    }
                }



                // Remove guest session after updating cart

                session()->forget('guest_id');
            }
        }
    }
}



if (!function_exists('updateGuestFavoritesToUser')) {
    function updateGuestFavoritesToUser($guestId): void
    {
        if (Auth::check()) {

            $realUserId = Auth::id();
            // $guestId = getUserId();

            if (!$guestId) return;

            $guestFavorites = ArtGalleryFavourite::where('guest_id', $guestId)->get();
            
            foreach ($guestFavorites as $fav) {
                $run = ArtGalleryFavourite::firstOrCreate([
                    'user_id' => $realUserId,
                    'unique_id' => $fav->unique_id
                ]);
            }
            
            $del_run = ArtGalleryFavourite::where('guest_id', $guestId)->delete();
        }
    }
}


if (!function_exists('getTileCount')) {

    function getTileCount($unique_id)

    {

        return DesignCollageModel::where(['unique_id' => $unique_id, 'is_deleted' => 0, 'empty' => 0])->get();
    }
}





if (!function_exists('generatePaypalAccessToken')) {



    // Generate an access token using client ID and app secret

    function generatePaypalAccessToken()

    {
        try {
            // Check if PayPal credentials are configured
            $clientId = env('PAYPAL_CLIENT_ID');
            $clientSecret = env('PAYPAL_CLIENT_SECRET');
            $baseUrl = env('PAYPAL_API_BASEURL');

            if (empty($clientId) || empty($clientSecret) || empty($baseUrl)) {
                \Log::warning('PayPal credentials not configured in .env file');
                return null; // Return null instead of throwing error
            }

            // Cache PayPal access token for 8 hours (tokens typically last 9 hours)
            // Use credentials hash as part of cache key to invalidate if credentials change
            $credentialsHash = md5($clientId . $clientSecret . $baseUrl);
            $cacheKey = 'paypal_access_token_' . $credentialsHash;
            
            return \Illuminate\Support\Facades\Cache::remember($cacheKey, 28800, function () use ($clientId, $clientSecret, $baseUrl) {
                $auth = base64_encode($clientId . ':' . $clientSecret);

                $url = $baseUrl . 'v1/oauth2/token';

                $headers = [
                    'Authorization: Basic ' . $auth,
                ];

                $data = 'grant_type=client_credentials';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode !== 200) {
                    \Log::error('PayPal API error: HTTP ' . $httpCode . ' | Response: ' . $response);
                    return null;
                }

                $data = json_decode($response, true);

                if (isset($data['access_token'])) {
                    return $data['access_token'];
                }

                \Log::error('PayPal access token not found in response: ' . $response);
                return null;
            });
        } catch (\Exception $e) {
            \Log::error('Error generating PayPal access token: ' . $e->getMessage());
            return null;
        }
    }
}


if (!function_exists('calculateCart')) {

    function calculateCart()

    {

        $cartItems = getUserCartItems();
        $collageCount = 0;
        $amountItemTotal = 0;
        $quantityTotal = 0;

        // Cache country data lookup (24 hours)
        $countryCode = getCountryCode();
        $cacheKey = 'country_data_' . $countryCode;
        $countryData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($countryCode) {
            return Countries::where('code', $countryCode)->first() ?? 0;
        });
        $shippingAmount = $countryData['shipping_amount'] ?? 0;
        $saleTaxRate = $countryData['vat'] ?? 0;


        foreach ($cartItems as $cartItemsData) {

            if ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery') {
                $count = $cartItemsData->designCollageMaster->total_tiles;
                $amountItem = getUserItemItems($count, $cartItemsData->price_id)->cost * $cartItemsData->quantity;
                /* if (!empty($saleTaxRate)) {
                    $amountItem = ($saleTaxRate * $amountItem) / 100 + $amountItem;
                } */
                $collageCount++;
            } elseif ($cartItemsData->name == 'giftcard') {
                $amountItem = $cartItemsData->price * $cartItemsData->quantity;
            }

            $amountItemTotal += $amountItem;
            $quantityTotal += $cartItemsData->quantity;
        }

        $subTotal = $amountItemTotal;
        if ($collageCount === 0) {
            $shippingAmount = 0;
        }
        $amountItemTotal = $amountItemTotal + $shippingAmount;

        $getGiftcardAmount = session('giftcard_amount', 0);
        $amountItemTotal = $amountItemTotal - $getGiftcardAmount;

        $discountAmount = session('discount_amount', 0);
        $disc_amt_type = session('disc_amt_type', null);
        $couponCode = session('coupon_code', null);

        if ($discountAmount != 0) {
            if ($disc_amt_type == 'percent') {
                $getDiscountPriceAmount = round(($amountItemTotal * $discountAmount) / 100, 2);
                $finalAmount = $amountItemTotal - $getDiscountPriceAmount;
            } else {
                $getDiscountPriceAmount = $discountAmount;
                $finalAmount = $amountItemTotal - $getDiscountPriceAmount;
            }
        } else {
            $getDiscountPriceAmount = 0;
            $finalAmount = $amountItemTotal;
        }

        // Cache view rendering if cart hasn't changed (5 minutes)
        $cartHash = md5($cartItems->pluck('id')->implode(',') . $quantityTotal . $finalAmount);
        $viewCacheKey = 'cart_views_' . $cartHash;
        
        $views = \Illuminate\Support\Facades\Cache::remember($viewCacheKey, 300, function () {
            return [
                'cartItem' => view('front.partials.Cart.cart-item')->render(),
                'cartTotal' => view('front.partials.Cart.cart-total')->render(),
                'checkoutshoppingCart' => view('front.checkout.checkout-shopping-cart')->render(),
                'checkoutOrderSummary' => view('front.checkout.checkout-order-summary')->render(),
            ];
        });

        return [
            'total' => round($finalAmount, 2),
            'cartItem' => $views['cartItem'],
            'cartTotal' => $views['cartTotal'],
            'cartTotalQuantity' => $quantityTotal,
            'checkoutshoppingCart' => $views['checkoutshoppingCart'],
            'checkoutOrderSummary' => $views['checkoutOrderSummary'],
        ];
    }
}

if (!function_exists('getCountryCode')) {
    function getCountryCode()
    {
        // Check if country code is already in session
        if (session()->has('user_countryCode')) {
            return session('user_countryCode');
        }

        // Get user's IP address
        $ip = $_SERVER['REMOTE_ADDR'] ?? '8.8.8.8'; // fallback IP for testing
        
        // Cache country code by IP for 24 hours to reduce API calls
        $cacheKey = 'country_code_' . md5($ip);
        $countryCode = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($ip) {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}");

            // If the request is successful and data is valid
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] === 'success') {
                    return $data['countryCode'];
                }
            }

            // Return default country code if everything fails
            return 'IL';
        });
        
        // Store in session for current request
        session(['user_countryCode' => $countryCode]);
        return $countryCode;
    }
}

if (!function_exists('getFinalPrice')) {

    function getFinalPrice($product_id, $tile_count, $country)
    {
        $product = PriceManagement::find($product_id);

        if (!$product || $tile_count <= 0) {
            return 0;
        }

        $pricePerTile = $product->price ?? 0;

        // Get discount based on product and tile count
        $discountData = Discount::where('price_management_id', $product_id)
            ->where('tile_from', '<=', $tile_count)
            ->where('tile_to', '>=', $tile_count)
            ->first();

        $discountPercent = $discountData->discount_percent ?? 0;

        // Get VAT percentage from country
        $vat_percent = Countries::where('id', $country)->value('vat') ?? 0;

        // Price calculations
        $amount = $pricePerTile * $tile_count;
        $discount = ($amount * $discountPercent) / 100;
        $afterDiscount = $amount - $discount;
        $vatAmount = ($afterDiscount * $vat_percent) / 100;
        $finalPrice = $afterDiscount + $vatAmount;

        return round($finalPrice, 2);
    }
}



if (!function_exists('getGiftcardCalculation')) {

    function getGiftcardCalculation($amountItemTotal)
    {
        $finalAmount = $amountItemTotal;
        $getGiftcardAmount = 0;
        $msg = '';

        $email = auth()->user()->id ?? 0;
        $giftcards = OrderGiftcard::where('redeem_by', $email)->where('status', 2)->orderBy('id')->get();

        if ($giftcards->isNotEmpty()) {
            $firstGiftcard = $giftcards->first();

            if ($firstGiftcard->total_amt >= $amountItemTotal) {
                // ✅ Use only the first gift card
                $getGiftcardAmount = $amountItemTotal;
                $finalAmount = 0;

                session()->put('giftcard_amount', $getGiftcardAmount);
                session()->put('giftcard_code', $firstGiftcard->code);

                // 🟡 Warn if some value on this gift card will be lost
                if ($firstGiftcard->total_amt > $amountItemTotal) {
                    $msg = "<b>Important:</b> Any remaining credit on your gift card(s) will not be saved. To avoid losing any value, please use the full amount during this purchase.";
                }
            } else {
                // 🧮 Use all gift cards combined
                $totalGiftAmount = $giftcards->sum('total_amt');
                $giftcardCodes = $giftcards->pluck('code')->toArray();

                $getGiftcardAmount = min($amountItemTotal, $totalGiftAmount);
                $finalAmount = $amountItemTotal - $getGiftcardAmount;

                session()->put('giftcard_amount', $getGiftcardAmount);
                session()->put('giftcard_code', implode(',', $giftcardCodes));

                // 🟡 Warn if some combined gift card amount will be lost
                if ($totalGiftAmount > $amountItemTotal) {
                    $msg = "<b>Important:</b> Any remaining credit on your gift card(s) will not be saved. To avoid losing any value, please use the full amount during this purchase.";
                }
            }
        }

        return [$getGiftcardAmount, $finalAmount, $msg];
    }
}

if (!function_exists('getCollagePreviewImagePath')) {
    /**
     * Get the preview image path for a collage using PreviewRenderer.
     * Generates the preview PNG on-demand if needed.
     * 
     * @param string|null $uniqueId The unique_id of the collage
     * @param string|null $fallbackPath Fallback image path if preview generation fails
     * @return string The preview image path or fallback path
     */
    function getCollagePreviewImagePath($uniqueId, $fallbackPath = null)
    {
        if (empty($uniqueId)) {
            return $fallbackPath ? asset('storage/' . $fallbackPath) : asset('assets/images/collage-image.png');
        }

        // Check if preview image already exists (generated by Preview page)
        // Use consistent filename so we reuse the exact same image
        $previewPath = 'temp/preview_' . $uniqueId . '.png';
        $previewFullPath = storage_path('app/public/' . $previewPath);
        
        if (file_exists($previewFullPath)) {
            // Reuse the existing preview image (same one used by Preview page)
            return asset('storage/' . $previewPath);
        }

        // If preview doesn't exist, generate it (shouldn't happen if Preview page was visited first)
        try {
            $designCollageRepository = app(DesignCollageRepository::class);
            
            // Get master data
            $masterData = $designCollageRepository->getOneMaster(['unique_id' => $uniqueId]);
            
            if (!$masterData) {
                return $fallbackPath ? asset('storage/' . $fallbackPath) : asset('assets/images/collage-image.png');
            }
            
            // Get tiles
            $tilesCollection = $designCollageRepository->getByWhere([
                'unique_id' => $uniqueId,
                'is_deleted' => 0
            ], ['seq' => 'asc']);
            
            if (!$tilesCollection || $tilesCollection->count() === 0) {
                return $fallbackPath ? asset('storage/' . $fallbackPath) : asset('assets/images/collage-image.png');
            }
            
            // Convert to arrays
            $masterArray = $masterData instanceof \Illuminate\Database\Eloquent\Model
                ? $masterData->toArray()
                : (array) $masterData;
            $masterArray['unique_id'] = $masterArray['unique_id'] ?? $uniqueId;
            
            $tilesArray = $tilesCollection->toArray();
            
            // Generate preview PNG using PreviewRenderer (same as Preview page)
            $previewRenderer = new PreviewRenderer();
            $previewResult = $previewRenderer->render($masterArray, $tilesArray);
            
            if ($previewResult && isset($previewResult['path'])) {
                return asset('storage/' . $previewResult['path']);
            }
        } catch (\Exception $e) {
            Log::warning('Error generating preview image for collage', [
                'unique_id' => $uniqueId,
                'error' => $e->getMessage()
            ]);
        }
        
        // Fallback to original image_path or default image
        return $fallbackPath ? asset('storage/' . $fallbackPath) : asset('assets/images/collage-image.png');
    }
}
