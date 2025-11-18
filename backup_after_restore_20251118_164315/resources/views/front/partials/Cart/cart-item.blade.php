@php
    $cartItems = getUserCartItems();
    $amountItemTotal = 0;
    $collageCount = 0;

    $countryData = \App\Models\Countries::where('code', getCountryCode())->first() ?? 0;
    $shippingAmount = $countryData['shipping_amount'] ?? 0;
    $saleTaxRate = $countryData['vat'] ?? 0;

@endphp

@forelse($cartItems as $cartItemsData)
    @php

        if ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery') {
            $count = $cartItemsData->designCollageMaster->total_tiles;
            $amountItem = getUserItemItems($count, $cartItemsData->price_id)->cost * $cartItemsData->quantity;

            /*  if (!empty($saleTaxRate)) {
                $amountItem = ($saleTaxRate * $amountItem) / 100 + $amountItem;
            }  */
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
                {{-- <div class="minicart-giftcard">
                    @include('front.component.gift-card-component', [
                        'giftCardType' => $cartItemsData->product_id,
                        'giftCardValue' => $cartItemsData->price,
                    ])
                </div> --}}
            @endif

            <div class="w-100">
                <div class="d-flex-item">
                    @if ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery')
                        <span>{{ $count }} tiles, {{ $cartItemsData->size_horiz }} x
                            {{ $cartItemsData->size_vert }}
                        </span>
                    @elseif ($cartItemsData->name == 'giftcard')
                        <span>Giftcard</span>
                    @endif

                    <span class="fw-bold">
                        {{ config('app.default_currency') }}
                        <span class="item-amount{{ $cartItemsData->id }}">{{ $amountItem }}</span>
                    </span>

                </div>

                @if ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery')
                    <h6 class="product-quality">
                        <span
                            class="text-success">{{ $cartItemsData['price_id'] == 1 || $cartItemsData['price_id'] == 2 ? 'Including Wall fasteners' : 'Tiles only, no fasteners' }}</span>

                        <button type="button"
                            class="ms-3 {{ $cartItemsData['price_id'] == 1 || $cartItemsData['price_id'] == 2 ? 'btn-refresh' : 'btn-refresh-green' }}"
                            id="refresh_btn"
                            onclick="return refreshProductFunction({{ $cartItemsData['id'] }},{{ $cartItemsData['price_id'] }},'cart')">
                            <!-- <i class="fa-solid fa-arrows-rotate"></i> -->
                            <img src=" {{ asset('assets/images/refreshicon.png') }}" alt="" class="refreshimg">
                        </button>
                    </h6>
                    {{--  --}}
                @elseif ($cartItemsData->name == 'giftcard')
                    <p>To: {{ $cartItemsData->giftcard_name }}</p>
                @endif

                <div class="d-flex align-items-center gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary update-cart"
                        onclick="updateQuantity({{ $cartItemsData->id }}, 'decrease', {{ ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery') ? getUserItemItems($count, $cartItemsData->price_id)->cost : $cartItemsData->price }})"
                        data-id="{{ $cartItemsData->id }}" data-action="decrease">
                        <i class="fa fa-minus"></i>
                    </button>

                    <span class="px-2 item-quantity{{ $cartItemsData->id }}">{{ $cartItemsData->quantity }}</span>

                    <button type="button" class="btn btn-sm btn-outline-secondary update-cart"
                        onclick="updateQuantity({{ $cartItemsData->id }}, 'increase', {{ ($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery') ? getUserItemItems($count, $cartItemsData->price_id)->cost : $cartItemsData->price }})"
                        data-id="{{ $cartItemsData->id }}" data-action="increase">
                        <i class="fa fa-plus"></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-danger delete-cart"
                        onclick="deleteCartItem({{ $cartItemsData->id }})" data-id="{{ $cartItemsData->id }}">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>

            </div>

        </div>

    </div>

@empty

    <p>Your cart is empty.</p>
@endforelse

@if ($cartItems->isNotEmpty())
    @php
        $subTotal = $amountItemTotal;
        if ($collageCount === 0) {
            $shippingAmount = 0;
        }
        $amountItemTotal = $amountItemTotal + $shippingAmount;
    @endphp
    <div class="order-checkout">
        <div class="cart-shipping">
            <table class="table">
                <tr>
                    <td>Subtotal </td>
                    <td class="text-end">
                        {{ config('app.default_currency') }} <span class="">{{ $subTotal }}</span>
                    </td>
                </tr>
                <tr>
                    <td>Shipping </td>
                    <td class="text-end">
                        {{ config('app.default_currency') }} <span class="shipping-cost">{{ $shippingAmount }}</span>
                    </td>
                </tr>

                <tr>
                    <th>Total </th>
                    <th class="text-end">
                        {{ config('app.default_currency') }}
                        <span class="totalAmount">{{ number_format($amountItemTotal, 2) }}</span>
                    </th>
                </tr>
            </table>
            <div class="continue_btn mb-4">
                <button type="button" onclick="return checkoutItems()"
                    class="btn btn-primary btn-lg w-100">Checkout</button>


            </div>
        </div>
    </div>
@endif
