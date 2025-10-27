<div class="purchase-item">

    @livewire('cart-component')

    <div class="cart-items-updated">
        @include('front.partials.Cart.cart-item')
    </div>
    {{-- <div class="order-checkout">
        <div class="cart-shipping">
            @include('front.partials.Cart.cart-shipping')
        </div>
    </div> --}}

    <ul class="cart_pro mt-5">
        <li><i class="fa-solid fa-circle-check fa-fw"></i> Satisfaction guaranteed</li>
        <li><i class="fa-solid fa-circle-check fa-fw"></i> Easy to hang, no drilling</li>
        <li><i class="fa-solid fa-circle-check fa-fw"></i> 4.7/5 stars from 10.513 reviews</li>
    </ul>

</div>
