<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FrontEnd\Cart\CartController;
use App\Http\Controllers\FrontEnd\OrderController;
use App\Http\Controllers\GiftCardController;

// Route group for frontend
Route::name('front.')->namespace('App\Http\Controllers')->group(function () {
    Route::get('/', 'HomeController@index')->name('index');

    Route::get('/refresh-token', 'HomeController@refreshToken')->name('refresh-token');

    // Route::get('/login', 'HomeController@index')->name('login');//wrong URL
    Route::get('/contact-us', 'HomeController@contact_us')->name('contact-us');
    Route::post('/submit_contact', 'HomeCrudController@submit_contact_us_form')->name('submit_contact');

    Route::get('/terms-and-conditions', 'HomeController@terms_and_conditions')->name('terms-and-conditions');
    Route::get('/privacy-policy', 'HomeController@privacy_policy')->name('privacy-policy');
    Route::get('/upload-photos', 'CollageController@uploadPhotos')->name('upload-photos');
    Route::post('/save-upload-photos', 'CollageController@saveUploadPhotos')->name('save-upload-photos');
    Route::post('/update-collage', 'CollageController@updateCollage')->name('update-collage');
    Route::get('/design-collage/{unique_id}', 'CollageController@designCollage')->name('design-collage');
    Route::get('/design-collage_2/{unique_id}', 'CollageController@designCollage_2')->name('design-collage_2');
    Route::post('/save-collage', 'CollageController@saveCollage')->name('save-collage');
    Route::get('/delete-collage/{unique_id}', 'CollageController@deleteCollage')->name('deleteCollage');

    // preview design collage
    Route::get('/preview-design-collage/{unique_id}', 'CollageController@previewDesignCollage')->name('preview-design-collage');
    Route::get('/art-gallery-edit/{unique_id}', 'CollageController@artGalleryEdit')->name('art-gallery-edit');

    // refresh product item
    Route::post('/refresh-product-item', 'CollageController@refreshProductItem')->name('refresh-product-item');

    Route::post('/cart/add', 'FrontEnd\Cart\CartController@addToCart')->name('cart.add');
    Route::get('/cart/items', 'FrontEnd\Cart\CartController@getCartItems')->name('cart.items');
    Route::post('/cart/update', 'FrontEnd\Cart\CartController@updateCart')->name('cart.update');
    Route::post('/cart/clear', 'FrontEnd\Cart\CartController@removeFromCart')->name('cart.clear');
    Route::post('/cart/remove', 'FrontEnd\Cart\CartController@removeFromCart')->name('cart.remove');
    Route::post('/cart/getCountryShppingAmount', 'FrontEnd\Cart\CartController@getCountryShppingAmount')->name('cart.getCountryShppingAmount');

    // gift card page
    // Route::get('/gift-card', 'GiftCardController@giftcard')->name('giftcard'); // old giftcard page
    Route::get('/gift-cards', 'GiftCardController@giftcardsList')->name('giftcard');
    Route::post('/get-gift-card', 'GiftCardController@getGiftCard')->name('getGiftCard');
    Route::post('/purchase-gift-card', 'GiftCardController@purchaseGiftCard')->name('purchaseGiftCard');
    Route::get('/redeem-giftcard/{code}', 'GiftCardController@redeemGiftcard')->name('redeem-giftcard');

    Route::get('/art-gallery', 'HomeController@artGallery')->name('art-gallery');
    Route::get('/get-filtered-artgallery', 'HomeController@getFilteredArtGallery')->name('get-filtered-artgallery');
    Route::post('/toggle-gallery-favorite', 'HomeController@toggleFavorite')->name('toggle-gallery-favorite');


    // apply coupon code
    Route::post('/apply-coupon-code', 'FrontEnd\Cart\CartController@applyCouponCode')->name('applyCouponCode');
    Route::post('/remove-coupon-code', 'FrontEnd\Cart\CartController@removeCouponCode')->name('removeCouponCode');
});

// Auth Routes
Route::post('/signup', [AuthController::class, 'signUp'])->name('signup');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/login', function () {
    return redirect()->route('front.index');
});
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::get('/email/resend', [AuthController::class, 'resendVerification'])->middleware('auth')->name('verification.resend');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::get('logout', [AuthController::class, 'logout1'])->name('logout');
Route::get('check-auth', [AuthController::class, 'checkAuth'])->name('auth.check');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');


// Authenticated Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/order', [OrderController::class, 'index'])->name('order');
    Route::post('/order-detail', [OrderController::class, 'orderDetail'])->name('order-detail');
    Route::get('current-draft',  [OrderController::class, 'currentDraft'])->name('current-draft');
    Route::get('/reorder/{unique_id}', [OrderController::class, 'reorder'])->name('front.reorder');

    Route::get('/change-password', [AuthController::class, 'changePassword'])->name('change-password');
    Route::post('/change-Password-Submit', [AuthController::class, 'changePasswordSubmit'])->name('changePasswordSubmit');

    Route::get('/profile', [AuthController::class, 'profilePage'])->name('profile');
    Route::post('profile-update', [AuthController::class, 'updateProfile'])->name('profileUpdate');

    Route::get('received-giftcards', [GiftCardController::class, 'receivedGiftcards'])->name('received-giftcards');



    Route::get('checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post("/paypal_create_order", [CartController::class, "paypal_create_order"])->name("paypal_create_order");
    Route::post("/paypal_capture_order", [CartController::class, "paypal_capture_order"])->name("paypal_capture_order");
    Route::post('/paypal_success_payment', [CartController::class, 'paypal_success_payment'])->name('paypal_success_payment');
    Route::get('/testPaypalPayment', [CartController::class, 'testPaypalPayment'])->name('testPaypalPayment');
    Route::post("/paypal_cancel_booking", [CartController::class, "paypal_cancel_booking",])->name("paypal_cancel_booking");

    Route::get("/paypal_check_cancel_booking/{refund_id}", [CartController::class, "paypal_check_cancel_booking",])->name("paypal_check_cancel_booking"); // only for developer checking use wheather running proper or not
});
















Route::prefix('admin-panel')->name('admin.')->controller(App\Http\Controllers\Admin\AdminController::class)->group(function () {
    Route::get('/', 'loginPage')->name('loginPage');
    Route::post('/', 'loginSubmit');
    Route::get('/forgot-password', 'forgotPasswordPage')->name('forgotPasswordPage');
    Route::post('/forgot-password-submit', 'forgotPasswordSubmit')->name('forgotPasswordSubmit');
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/logout', 'logout')->name('logout');
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/update-profile', 'updateProfilePage')->name('updateProfile');
        Route::post('/update-profile', 'updateProfileSubmit');
        Route::get('/change-password', 'changePasswordPage')->name('changePassword');
        Route::post('/change-password', 'changePasswordSubmit');

        Route::controller(App\Http\Controllers\Admin\FrameController::class)->group(function () {
            Route::get('/frames', 'framesPage')->name('framesPage');
            Route::get('/getall-frames', 'getAll')->name('getAllFrames');
            Route::get('/getsingle-frame/{id}', 'getSingle')->name('getSingleFrame');
            Route::post('/update-frame', 'update')->name('updateFrame');
        });

        Route::controller(App\Http\Controllers\Admin\TileController::class)->group(function () {
            Route::get('/tiles', 'tilesPage')->name('tilesPage');
            Route::get('/getall-tiles', 'getAll')->name('getAllTiles');
            Route::get('/getsingle-tile/{id}', 'getSingle')->name('getSingleTile');
            Route::post('/tiles-submit', 'submitTile')->name('tilesSubmit');
            Route::post('/update-tile', 'updateTile')->name('updateTile');
            Route::get('/delete-tile/{id}', 'deleteTile')->name('deleteTile');
        });

        Route::controller(App\Http\Controllers\Admin\UserController::class)->group(function () {
            Route::get('/users', 'allUsersPage')->name('allUsersPage');
            Route::get('/getall-users', 'getAll')->name('getAllUsers');
            Route::get('/user/{id}', 'usersPage')->name('userPage');
            Route::get('/getsingle-user/{id}', 'getSingle')->name('getSingleUser');
            Route::post('/update-user/{id}', 'update')->name('updateUser');
        });

        Route::controller(App\Http\Controllers\Admin\ShippingAmountController::class)->group(function () {
            Route::get('/shipping-amount-list', 'countryShippingAmountList')->name('countryShippingAmountList');
            Route::get('/get-country-shipping-amount', 'getCountryShippingAmount')->name('getCountryShippingAmount');
            Route::post('/add-shipping-amount', 'addShippingAmount')->name('addShippingAmount');
            Route::get('/get-single-shipping-amount/{id}', 'getSingleShippingAmount')->name('getSingleShippingAmount');
            Route::get('/delete-shipping-amount/{id}', 'deleteShippingAmount')->name('deleteShippingAmount');
        });

        Route::controller(App\Http\Controllers\Admin\OrderController::class)->group(function () {
            Route::get('/orders-list', 'index')->name('orderList');
            Route::get('/get-order-list', 'getOrderList')->name('getOrderList');
            Route::post('/update-order-status', 'updateOrderStatus')->name('update-order-status');
            Route::get('/order-details/{order_id}', 'orderDetails')->name('order-details');
            Route::get('/get-design-collage-images/{order_id}', 'getDesignCollageImages')->name('get-design-collage-images');
            Route::get('/giftcard', 'giftCardList')->name('giftcard');
            Route::get('/get-giftcard-list', 'getGiftcardList')->name('giftcardList');

        });

        Route::controller(App\Http\Controllers\Admin\GalleryController::class)->group(function () {
            Route::get('/gallery-list', 'index')->name('galleryList');
            Route::get('/upload-photos/{type?}', 'uploadPhotos')->name('uploadPhotos');
            Route::get('/get-gallery-list', 'getGalleryList')->name('getGalleryList');
        });
        Route::get('/view-design-collage/{unique_id}', 'App\Http\Controllers\CollageController@viewDesignCollage')
            ->name('view-design-collage');


        Route::controller(App\Http\Controllers\Admin\CouponController::class)->group(function () {
            Route::get('/coupon-list', 'index')->name('couponList');
            Route::get('/get-coupons', 'getCoupons')->name('getCoupons');
            Route::get('/add-coupon', 'addCoupon')->name('addCoupon');
            Route::get('/edit-coupons/{id}', 'editCoupons')->name('editCoupons');
            Route::post('/store-coupon', 'storeCoupon')->name('storeCoupon');
            Route::post('/update-coupon', 'updateCoupon')->name('updateCoupon');
            Route::post('/delete-coupon', 'deleteCoupon')->name('deleteCoupon');
            Route::post('/inactive-past-coupons', 'inactivePastCoupons')->name('inactivePastCoupons');
        });
        Route::controller(App\Http\Controllers\Admin\PriceController::class)->group(function () {
            Route::get('/Price', 'index')->name('price');
            Route::post('/submit-price', 'storePrice')->name('storePrice');
            Route::get('/calculateFinalPrice', 'calculateFinalPrice')->name('calculateFinalPrice');
        });
        Route::controller(App\Http\Controllers\Admin\DiscountController::class)->group(function () {
            Route::get('/Discount', 'index')->name('discount');
            Route::get('/getdiscount', 'getAll')->name('getdiscount');
            Route::post('/submit-discount', 'storeDiscount')->name('storeDiscount');
            Route::get('/getsingle-discount/{id}', 'getDiscount')->name('getSingleDiscount');
            Route::post('/update-discount', 'updateDiscount')->name('updateDiscount');
            Route::get('/delete-discount/{id}', 'deleteDiscount')->name('deleteDiscount');
        });
        Route::controller(App\Http\Controllers\Admin\CollectionController::class)->group(function () {
            Route::get('/collection', 'index')->name('collection');
            Route::get('/getcollection', 'getAll')->name('getcollection');
            Route::post('/submit-collection', 'storeCollection')->name('storeCollection');
            Route::get('/getsingle-collection/{id}', 'getCollection')->name('getSingleCollection');
            Route::post('/update-collection', 'updateCollection')->name('updateCollection');
            Route::post('/delete-collection/{id}', 'deleteCollection')->name('deleteCollection');
        });
        Route::controller(App\Http\Controllers\Admin\TagController::class)->group(function () {
            Route::get('/tag', 'index')->name('tag');
            Route::get('/gettag', 'getAll')->name('getTag');
            Route::post('/submit-tag', 'storeTag')->name('storeTag');
            Route::get('/getsingle-tag/{id}', 'getTag')->name('getSingleTag');
            Route::post('/update-tag', 'updateTag')->name('updateTag');
            Route::post('/delete-tag/{id}', 'deleteTag')->name('deleteTag');
        });

        Route::controller(App\Http\Controllers\Admin\GiftcardController::class)->group(function () {
            Route::get('/gift-card', 'index')->name('Giftcardlist');
            Route::get('/getgift-card', 'getAll')->name('getGiftcard');
            Route::post('/submit-gift-card', 'storeGiftcard')->name('storeGiftcard');
            Route::get('/getsingle-gift-card/{id}', 'getGiftcard')->name('getSingleGiftcard');
            Route::post('/update-gift-card', 'updateGiftcard')->name('updateGiftcard');
            Route::post('/delete-gift-card/{id}', 'deleteGiftcard')->name('deleteGiftcard');
        });
    });
});

Route::get('/run-command', function () {
    // Artisan::call('optimize:clear');
    // return 'optimize clear.';
    return 'Something went wrong';
});

Route::get('/view-log', function () {
    if (
        (auth()->check() && in_array(auth()->user()->email, ['behlah.webwiders@gmail.com'])) ||
        (auth()->guard('admins')->check())
    ) {
    } else {
        return 'You do not have permission to view logs.';
        die;
    }
    // $logFile = storage_path('logs/laravel.log');
    // if (!file_exists($logFile)) return 'Log file not found.';
    // return nl2br(e(file_get_contents($logFile)));
    $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) return 'Log file not found.';
        $logContent = e(file_get_contents($logFile));

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <title>Laravel Log Viewer</title>
            <style>
                body { font-family: monospace; background: #f8f9fa; padding: 20px; }
                #log-container { height: 90vh; overflow-y: scroll; white-space: pre-wrap; background: #fff; padding: 15px; border: 1px solid #ccc; }
            </style>
        </head>
        <body>
            <div id="log-container">$logContent</div>

            <script>
                const logContainer = document.getElementById('log-container');
                logContainer.scrollTop = logContainer.scrollHeight;
            </script>
        </body>
        </html>
        HTML;
    
});
