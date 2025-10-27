@php
    $cartItems = getUserCartItems();
    $amountItemTotal = 0;
    $collageCount = 0;
    $giftcardWarning = '';

    $countryData = \App\Models\Countries::where('code', getCountryCode())->first() ?? 0;
    $shippingAmount = $countryData['shipping_amount'] ?? 0;
    $saleTaxRate = $countryData['vat'] ?? 0;
@endphp

<div class="col-sm-8">
    @if ($cartItems->isNotEmpty())
        @foreach ($cartItems as $cartItemsData)
            @php
                if ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery') {
                    $count = $cartItemsData->designCollageMaster->total_tiles;
                    $amountItem = getUserItemItems($count, $cartItemsData->price_id)->cost * $cartItemsData->quantity;

                    /* if (!empty($saleTaxRate)) {
                        $amountItem = ($saleTaxRate * $amountItem) / 100 + $amountItem;
                    }  */
                    $collageCount++;
                } elseif ($cartItemsData->name == 'giftcard') {
                    $amountItem = $cartItemsData->price * $cartItemsData->quantity;
                }

                $amountItemTotal += $amountItem;
                $imagePath = $cartItemsData->designCollageMaster->image_path ?? null;
                $imageSrc = $imagePath ? asset('storage/' . $imagePath) : asset('assets/images/collage-image.png');
            @endphp

            <div class="d-flex cart-product-card mb-5">
                @if ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery')
                    <div class="img-box">
                        <img src="{{ $imageSrc }}" alt="">
                    </div>
                @elseif ($cartItemsData->name == 'giftcard')
                    <div class="img-box">
                        <img src="{{ asset('storage/' . $cartItemsData->giftcard->image) }}" alt="">
                    </div>
                @endif

                <div class="items-pur text-start px-4">
                    <div class="check-flex">
                        <div class="w-100">
                            @if ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery')
                                <div class="d-flex-item ">
                                    <span class="fw-bold">Collage {{ $cartItemsData->size_horiz }} x
                                        {{ $cartItemsData->size_vert }}

                                    </span>

                                    <span class="fw-bold">{{ config('app.default_currency') }} <span
                                            class="checkout-cart_item-price">{{ $amountItem }}</span>
                                    </span>
                                </div>
                                <div class="product-quality">
                                    <span
                                        class="text-success">{{ $cartItemsData['price_id'] == 1 || $cartItemsData['price_id'] == 2 ? 'Including Wall fasteners' : 'Tiles only, no fasteners' }}</span>

                                    <button type="button"
                                        class="ms-3 {{ $cartItemsData['price_id'] == 1 || $cartItemsData['price_id'] == 2 ? 'btn-refresh' : 'btn-refresh-green' }} "
                                        id="refresh_btn"
                                        onclick="return refreshProductFunction({{ $cartItemsData['id'] }},{{ $cartItemsData['price_id'] }},'cart')">
                                        <!-- <i class="fa-solid fa-arrows-rotate"></i> -->
                                        <img src=" {{ asset('assets/images/refreshicon.png') }}" alt=""
                                            class="refreshimg">


                                    </button>
                                </div>
                                <ul>
                                    <li>{{ $count }} tiles</li>
                                </ul>
                            @elseif ($cartItemsData->name == 'giftcard')
                                <div class="d-flex-item">
                                    <span class="fw-bold">Giftcard</span>
                                    <span class="fw-bold">{{ config('app.default_currency') }} <span
                                            class="checkout-cart_item-price">{{ $amountItem }}</span>
                                    </span>
                                </div>

                                <ul>
                                    <li class="fw-thin mb-1">
                                        To: {{ $cartItemsData->giftcard_name }}
                                    </li>
                                </ul>
                            @endif

                            <div class="d-flex align-items-center gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary update-cart"
                                    onclick="updateQuantity({{ $cartItemsData->id }}, 'decrease')"
                                    data-id="{{ $cartItemsData->id }}" data-action="decrease">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <span class="px-2">{{ $cartItemsData->quantity }}</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary update-cart"
                                    onclick="updateQuantity({{ $cartItemsData->id }}, 'increase')"
                                    data-id="{{ $cartItemsData->id }}" data-action="increase">
                                    <i class="fa fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-cart"
                                    onclick="deleteCartItem({{ $cartItemsData->id }})"
                                    data-id="{{ $cartItemsData->id }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <p>Your cart is empty.</p>
    @endif
</div>
<div class="col-sm-4">
    <div class="order-detail-shipping sticky-top">
        @if ($cartItems->isNotEmpty())
            @php

                $subTotal = $amountItemTotal;
                if ($collageCount === 0) {
                    $shippingAmount = 0;
                }
                $amountItemTotal = $amountItemTotal + $shippingAmount;

                [$getGiftcardAmount, $amountItemTotal, $giftcardWarning] = getGiftcardCalculation($amountItemTotal);

                $discountAmount = session('discount_amount', 0);
                $disc_amt_type = session('disc_amt_type', null);
                $couponCode = session('coupon_code', null);

                if ($discountAmount !== 0) {
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

            @endphp
            <div class="purchase-item">
                <h3>Overview</h3>
                <div class="checkout-overview">
                    <div>
                        <div class="check-flex">
                            <div class="w-100">

                                <div class="d-flex-item" style="color: #000;">
                                    <span>Subtotal</span>
                                    <span class="fw-bold">
                                        {{ config('app.default_currency') }} {{ $subTotal }}
                                    </span>
                                </div>
                                <div class="d-flex-item mb-3" style="color: #000;">
                                    <span>Shipping</span>
                                    <span class="fw-bold">
                                        {{ config('app.default_currency') }} {{ $shippingAmount }}
                                    </span>
                                </div>

                                @if ($getGiftcardAmount > 0)
                                    <div class="d-flex-item" style="color: #000;">
                                        <span>Giftcard @php echo (stripos(session('giftcard_code', ''), ',') ? '(s)' : '') @endphp applied</span>

                                        <span class="text-danger"><strong>-{{ config('app.default_currency') }}
                                                {{ $getGiftcardAmount }}</strong>
                                        </span>
                                    </div>
                                @endif

                                @if (session('discount_amount', 0) == 0)
                                    <div class="text-start mb-4" style="opacity: 0.7;">
                                        <div class="order-checkout my-3" style="border-bottom: 2px solid lightgrey;">
                                            <span id="toggleSpan" onclick="return openAddGiftCard()"
                                                style="cursor: pointer;"><i class="fa-solid fa-gift pe-2"></i>Insert
                                                coupon</span>
                                        </div>
                                        <div class="d-flex" id="demoDiv">
                                            <div class="form-group mb-0">
                                                <input type="Text" class="form-control" id="couponCode"
                                                    placeholder="Code">
                                            </div>
                                            <button type="button" onclick="return checkDiscountCoupon()"
                                                class="btn btn-secondary h-100 ms-2 ">Apply</button>
                                        </div>
                                        <span class="text-danger" id="couponCodeError"></span>
                                    </div>
                                @else
                                    <div class="d-flex-item" style="color: #000;">
                                        <span>Coupon
                                            <strong>({{ $couponCode }})</strong>
                                            <br>
                                            <a type="button" onclick="return removeDiscountCoupon()"
                                                class="btn btn-danger btn-sm">Remove</a>
                                        </span>
                                        <span class="text-danger"><strong>-{{ config('app.default_currency') }}
                                                {{ $getDiscountPriceAmount }}</strong></span>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="order-checkout mt-4" style="border-top: 2px solid lightgrey;">
                        <div class="d-flex-item items-pur pt-2 mb-0 pb-0">
                            <span>Total</span>
                            <span class="fw-bold">{{ config('app.default_currency') }}<span class="cartTotals">
                                    {{ number_format($finalAmount, 2) }}
                                </span>
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        @endif
    </div>

    <div class="row mt-3">

        @if ($giftcardWarning)
            <div class="alert alert-warning mt-2">
                {!! $giftcardWarning !!}
            </div>
        @endif
    </div>
</div>
