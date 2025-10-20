@php
    $cartItems = getUserCartItems();
    $amountItemTotal = 0;
    $collageCount = 0;

    $countryData = \App\Models\Countries::where('code',getCountryCode())->first() ?? 0;
    $shippingAmount = $countryData['shipping_amount'] ?? 0;
    $saleTaxRate = $countryData['vat'] ?? 0;
@endphp

@forelse($cartItems as $cartItemsData)
    @php
    
        if($cartItemsData->name == 'collage' || $cartItemsData->name == 'artgallery') {
            $count = $cartItemsData->designCollageMaster->total_tiles;
            $amountItem = getUserItemItems($count,$cartItemsData->price_id)->cost * $cartItemsData->quantity;

            /* if (!empty($saleTaxRate)) {
                $amountItem = ($saleTaxRate * $amountItem) / 100 + $amountItem;
            } */
            $collageCount++;
            
        } elseif($cartItemsData->name == 'giftcard') {
            $amountItem = $cartItemsData->price * $cartItemsData->quantity;
        }

        
        $amountItemTotal += $amountItem;

    @endphp

@empty
@endforelse

@php
    $subTotal = $amountItemTotal;
    if($collageCount === 0) {
        $shippingAmount = 0;
    }
    $amountItemTotal = $amountItemTotal + $shippingAmount;
@endphp
{{ config('app.default_currency') . ($amountItemTotal > 0 ? $amountItemTotal : 0) }} <i class="fa-solid fa-cart-shopping fa-fw"></i>
