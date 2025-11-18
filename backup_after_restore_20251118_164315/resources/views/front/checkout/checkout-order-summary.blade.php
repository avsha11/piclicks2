@php
    $cartItems = getUserCartItems();
    $amountItemTotal = 0;
    $collageCount = 0;
    $showBellow = false;

    $countryData = \App\Models\Countries::where('code', getCountryCode())->first() ?? 0;
    $shippingAmount = $countryData['shipping_amount'] ?? 0;
    $saleTaxRate = $countryData['vat'] ?? 0;
@endphp


@forelse($cartItems as $cartItemsData)
    @php
        $showBellow = true;

        if ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery') {
            $count = $cartItemsData->designCollageMaster->total_tiles;
            $amountItem = getUserItemItems($count, $cartItemsData->price_id)->cost * $cartItemsData->quantity;

            /*  if (!empty($saleTaxRate)) {
                $amountItem = ($saleTaxRate * $amountItem) / 100 + $amountItem;
            } */
            $collageCount++;
        } elseif ($cartItemsData->name == 'giftcard') {
            $amountItem = $cartItemsData->price * $cartItemsData->quantity;
        }

        $amountItemTotal += $amountItem;

        // Use PreviewRenderer to generate preview image (same as Preview page)
        $uniqueId = $cartItemsData->designCollageMaster->unique_id ?? $cartItemsData->product_id ?? null;
        $fallbackPath = $cartItemsData->designCollageMaster->image_path ?? null;
        $imageSrc = getCollagePreviewImagePath($uniqueId, $fallbackPath);
    @endphp

    <div class="items-pur">
        <div class="check-flex">
            @if ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery')
                <div class="checkout-img">
                    <img src="{{ $imageSrc }}" alt="">
                </div>
                @elseif ($cartItemsData->name == 'giftcard')
                <div class="checkout-img">
                    <img src="{{ asset('storage/' . $cartItemsData->giftcard->image) }}" alt="">
                </div>
            @endif
            <div class="w-100">
                <div class="d-flex-item">
                    @if ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery')
                        <span>{{ $count }} Tile, {{ $cartItemsData->size_horiz }} x
                            {{ $cartItemsData->size_vert }} </span>
                    @elseif ($cartItemsData->name == 'giftcard')
                        <span>Giftcard</span>
                    @endif
                    <span class="fw-bold vatTdContainer{{ $cartItemsData->product_id }}">
                        {{ config('app.default_currency') }} {{ $amountItem }}
                    </span>
                </div>

                @if ($cartItemsData->name == 'giftcard')
                    <p>To: {{ $cartItemsData->giftcard_name }}</p>
                @endif
            </div>
        </div>
    </div>
@empty
    <p>Your cart is empty.</p>
@endforelse


@if ($showBellow)
    @php

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
                $getDiscountPriceAmount = round(($amountItemTotal * $discountAmount) / 100,2);
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
    <div class="cart-shipping">
        <div class="order-checkout">
            <table class="table">

                <tr>
                    <td>Subtotal </td>
                    <td class="text-end shppingCostDiv">
                        {{ config('app.default_currency') }} {{ $subTotal }}
                    </td>
                </tr>
                <tr class="mb-3">
                    <td>Shipping </td>
                    <td class="text-end shppingCostDiv">
                        {{ config('app.default_currency') }} {{ $shippingAmount }}
                    </td>
                </tr>
                @if ($getGiftcardAmount > 0)
                    <tr>
                        <td><span>Giftcard @php echo (stripos(session('giftcard_code', ''), ',') ? '(s)' : '') @endphp applied</span></td>

                        <td class="text-end">
                            <span class="text-danger"><strong>-US$
                                    {{ $getGiftcardAmount }}</strong></span>
                        </td>
                    </tr>
                @endif
                @if (session('discount_amount', 0) != 0)
                    <tr>
                        <td><span>Coupon<strong>
                                    ({{ $couponCode }})</strong></span>
                        </td>
                        <td class="text-end">
                            <span class="text-danger"><strong>-US$
                                    {{ $getDiscountPriceAmount }}</strong></span>
                        </td>
                    </tr>
                @endif
                <tr class="mt-3">
                    <th>Total </th>
                    <th class="text-end totalByCountryCondition">
                        {{ config('app.default_currency') }}
                        {{ number_format($finalAmount, 2) }}
                    </th>
                </tr>
            </table>
        </div>
    </div>
@endif
