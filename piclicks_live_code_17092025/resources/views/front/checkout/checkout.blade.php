@extends('front.layout.front-layout')
@push('title', 'Checkout')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="text-center p-0 mt-3 mb-2">
                <div class="card px-0 pt-md-4 pb-0 mt-3 mb-3">
                    <form id="msform">
                        <!-- progressbar -->
                        <ul id="progressbar">
                            <li class="active" id="account"><strong>Shopping cart</strong></li>
                            <li id="personal"><strong>Shipping</strong></li>
                            <li id="payment"><strong>Payment</strong></li>
                        </ul>
                        <br>
                        <fieldset>
                            <div class="section_shipping pt-0">
                                <div class="container px-md-0">
                                    <div class="page-title">
                                        <h2 class="mb-2">Cart</h2>
                                    </div>

                                    <div class="row checkout-shopping-cart">
                                        @include('front.checkout.checkout-shopping-cart')
                                    </div>
                                </div>
                            </div>
                            <div class="section_shipping section-shipping-2" style="display: none !important;">
                                <div class="container">
                                    <div class="row">
                                        <div class="text-start mb-2">
                                            <h5 class="mb-2 mt-4 mt-md-5">Want something extra?<span>
                                        </div>
                                        <div class="card-inner d-flex flex-wrap flex-md-nowrap">
                                            <img src="https://images.prismic.io/ixxiproduction/a3b0bfe3-e8a4-43df-8626-9fc6f97aac87_16.Sfeer_MB_20x20_Laydown+%281%29.jpg?auto=compress,format&rect=0,928,3712,3712&w=200&h=200"
                                                alt="">
                                            <div class="col-8 ps-0 ps-md-3">
                                                <h4 class="mb-2">Photobook 10x10cm</h4>
                                                <p class="mb-3">Store all your 10x10cm photo cards in a convenient storage
                                                    folder. Can also be used as a photo display!</p>
                                                <a href="#" class="btn btn-primary"><i
                                                        class="fa-solid fa-plus pe-2"></i>Add</a>
                                            </div>
                                            <div class="col-2 text-end">
                                                <h4><strong>€14.95</strong></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($result->isNotEmpty())
                                <input type="button" name="next"
                                    class="next action-button btn btn-primary btn-lg d-md-none" value="Checkout" />
                                <input type="button" name="next"
                                    class="next action-button btn btn-primary btn-lg d-none d-md-block" value="Checkout" />
                            @endif
                            <!-- <input type="button" name="previous" class="previous action-button-previous btn btn-primary btn-lg" value="Previous" />  -->
                        </fieldset>
                        <!-- fieldsets -->
                        <fieldset>
                            <div class="section_shipping">
                                <div class="container">
                                    <div class="page-title">
                                        <h2 class="mb-2">Checkout</h2>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-8">
                                            <div class="shipping-form">
                                                <h4 class="mb-2">Shipping Address</h4>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="form-label mb-0">Full Name</label>
                                                            <input type="text" id="shopping_fullname"
                                                                name="shopping_fullname" class="form-control"
                                                                value="{{ Auth::user()->name }}" placeholder="Full Name">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="form-label mb-0">Phone Number</label>
                                                            <input type="number" id="shopping_phone" name="shopping_phone"
                                                                class="form-control" placeholder="Enter Phone Number">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="form-label mb-0">Email address</label>
                                                            <input type="email" id="shopping_email" name="shopping_email"
                                                                class="form-control" value="{{ Auth::user()->email }}"
                                                                placeholder="Enter Email">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="form-label mb-0">Company Name</label>
                                                            <input type="text" id="shopping_company_name"
                                                                name="shopping_company_name" class="form-control"
                                                                placeholder="Enter Company Name">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label class="form-label mb-0">Address</label>
                                                            <input type="text" id="shopping_address"
                                                                name="shopping_address" class="form-control"
                                                                placeholder="Address">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label class="form-label mb-0">Address line 2
                                                                (Optional)</label>
                                                            <input type="text" id="shopping_address_opt"
                                                                name="shopping_address_opt" class="form-control"
                                                                placeholder="Address">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="form-label mb-0">City</label>
                                                            <input type="text" id="shopping_city" name="shopping_city"
                                                                class="form-control" placeholder="City">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="form-label mb-0">Postal Code</label>
                                                            <input type="text" id="shopping_postalcode"
                                                                name="shopping_postalcode" class="form-control"
                                                                placeholder="Postal Code">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="form-label mb-0">State</label>
                                                            <input type="text" id="shopping_state"
                                                                name="shopping_state" class="form-control"
                                                                placeholder="State">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="form-label mb-0">Country</label>
                                                            <select name="shopping_country" id="shopping_country"
                                                                class="form-control">
                                                                <option value="">Select Country</option>
                                                                @foreach ($countries as $item)
                                                                    <option value="{{ $item->code }}"
                                                                        {{ $item->code == session('user_countryCode', null) ? 'selected' : '' }}>
                                                                        {{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-check mb-3">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="same_as_shipping" checked>
                                                            <label class="form-check-label" for="same_as_shipping">
                                                                Billing address same as shipping
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion billing_address" id="accordionExample">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                                            aria-expanded="false" aria-controls="collapseOne">
                                                            <span>Add different billing address? </span>
                                                        </button>
                                                    </h2>
                                                    <div id="collapseOne" class="accordion-collapse collapse"
                                                        data-bs-parent="#accordionExample" style="">
                                                        <div class="accordion-body">
                                                            <div class="shipping-form">
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label class="form-label mb-0">Full
                                                                                Name</label>
                                                                            <input type="text" id="billing_fullname"
                                                                                name="billing_fullname"
                                                                                class="form-control"
                                                                                placeholder="Full Name">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label class="form-label mb-0">Phone
                                                                                Number</label>
                                                                            <input type="number" id="billing_phone"
                                                                                name="billing_phone" class="form-control"
                                                                                placeholder="Enter Phone Number">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-12">
                                                                        <div class="form-group">
                                                                            <label class="form-label mb-0">Email
                                                                                address</label>
                                                                            <input type="email" id="billing_email"
                                                                                name="billing_email" class="form-control"
                                                                                placeholder="Enter Email">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-12">
                                                                        <div class="form-group">
                                                                            <label class="form-label mb-0">Address</label>
                                                                            <input type="text" id="billing_address"
                                                                                name="billing_address"
                                                                                class="form-control"
                                                                                placeholder="Address">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-12">
                                                                        <div class="form-group">
                                                                            <label class="form-label mb-0">Address line 2
                                                                                (Optional)</label>
                                                                            <input type="text" id="billing_address_opt"
                                                                                name="billing_address_opt"
                                                                                class="form-control"
                                                                                placeholder="Address">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label class="form-label mb-0">City</label>
                                                                            <input type="text" id="billing_city"
                                                                                name="billing_city" class="form-control"
                                                                                placeholder="City">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label class="form-label mb-0">Postal
                                                                                Code</label>
                                                                            <input type="text" id="billing_postal"
                                                                                name="billing_postal" class="form-control"
                                                                                placeholder="Postal Code">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label class="form-label mb-0">State</label>
                                                                            <input type="text" id="billing_state"
                                                                                name="billing_state" class="form-control"
                                                                                placeholder="State">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="form-group">
                                                                            <label class="form-label mb-0">Country</label>
                                                                            <select name="billing_country"
                                                                                id="billing_country" class="form-control">
                                                                                <option value="">Select Country
                                                                                </option>
                                                                                @foreach ($countries as $item)
                                                                                    <option value="{{ $item->id }}">
                                                                                        {{ $item->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="order-detail-shipping sticky-top">
                                                <div class="purchase-item">
                                                    <h3>Order Summary</h3>
                                                    <div class="checkout-order-summary">
                                                        @include('front.checkout.checkout-order-summary')

                                                        {{-- @php
                                                            $finalAmounts = [];
                                                        @endphp
                                                        @forelse($cartItems as $cartItemsData)
                                                            @php
                                                                $count = count(
                                                                    $cartItemsData->designCollage
                                                                        ->where('empty', 0)
                                                                        ->where('is_deleted', 0),
                                                                );
                                                                $framecost =
                                                                    getUserFramesItems($cartItemsData->frame) *
                                                                    $cartItemsData->quantity;
                                                                $amountFinal =
                                                                    getUserItemItems($count,$cartItemsData->price_id)->cost *
                                                                        $cartItemsData->quantity +
                                                                    $framecost;
                                                                // Store amountFinal using product_id as key
                                                                $finalAmounts[
                                                                    $cartItemsData->product_id
                                                                ] = $amountFinal;
                                                            @endphp
                                                            <div class="items-pur">
                                                                <div class="check-flex">
                                                                    <div class="checkout-img">
                                                                        @if ($cartItemsData->designCollageMaster->image_path)
                                                                            <img
                                                                                src="{{ asset('storage/' . $cartItemsData->designCollageMaster->image_path) }}">
                                                                        @else
                                                                            <img
                                                                                src="{{ asset('assets/images/collage-image.png') }}">
                                                                        @endif
                                                                    </div>
                                                                    <div class="w-100">
                                                                        <div class="d-flex-item">
                                                                            {{ $count }} Tile,
                                                                            <span> {{ $cartItemsData->size_horiz }} x
                                                                                {{ $cartItemsData->size_vert }} </span>
                                                                            <span
                                                                                class="fw-bold vatTdContainer{{ $cartItemsData->product_id }}">
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <p>Your cart is empty.</p>
                                                        @endforelse
                                                        <div class="order-checkout">
                                                            <table class="table">
                                                                @if ($totalframecost > 0)
                                                                    <tr>
                                                                        <td>Frame Cost</td>
                                                                        <td class="text-end">
                                                                            {{ config('app.default_currency') }}{{ $totalframecost }}
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                                <tr>
                                                                    <td>Shipping </td>
                                                                    <td class="text-end shppingCostDiv">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Total </th>
                                                                    <th class="text-end totalByCountryCondition">
                                                                    </th>
                                                                </tr>
                                                            </table>
                                                        </div> --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-danger">Customs duties and taxes may be applied by your local
                                            authorities. These are not included in our prices and are the buyer's
                                            responsibility.</span>
                                    </div>
                                </div>
                            </div>
                            <input type="button" name="next" class="next action-button btn btn-primary btn-lg "
                                data-page="shipping" value="Continue to payment" />
                            <input type="button" name="previous"
                                class="previous action-button-previous btn btn-primary btn-lg"
                                value="Back to shopping cart" />
                        </fieldset>
                        <!-- fieldsets -->
                        <fieldset>
                            <div class="section_shipping pt-0">
                                <div class="container  px-md-0">
                                    <div class="page-title">
                                        <h2 class="mb-2">Payment</h2>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="accordion" id="accordionExample">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                            aria-expanded="true" aria-controls="collapseTwo">
                                                            PayPal
                                                        </button>
                                                    </h2>
                                                    <div id="collapseTwo" class="accordion-collapse collapse show"
                                                        data-bs-parent="#accordionExample">
                                                        <div class="accordion-body text-start">
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <div id="paypal-button-container"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="order-detail-shipping sticky-top">
                                                <div class="purchase-item">
                                                    <h3>Review your order</h3>
                                                    <div class="checkout-review">
                                                        @include('front.checkout.checkout-order-summary')
                                                    </div>
                                                    {{-- @forelse($cartItems as $cartItemsData)
                                                        @php
                                                            $count = count(
                                                                $cartItemsData->designCollage
                                                                    ->where('empty', 0)
                                                                    ->where('is_deleted', 0),
                                                            );
                                                        @endphp
                                                        <div class="items-pur">
                                                            <div class="check-flex">
                                                                <div class="checkout-img">
                                                                    @if ($cartItemsData->designCollageMaster->image_path)
                                                                        <img
                                                                            src="{{ asset('storage/' . $cartItemsData->designCollageMaster->image_path) }}">
                                                                    @else
                                                                        <img
                                                                            src="{{ asset('assets/images/collage-image.png') }}">
                                                                    @endif
                                                                </div>
                                                                <div class="w-100">
                                                                    <div class="d-flex-item">{{ $count }} Tile,
                                                                        {{ $cartItemsData->size_horiz }} x
                                                                        {{ $cartItemsData->size_vert }} </span><span
                                                                            class="fw-bold vatTdContainer{{ $cartItemsData->product_id }}"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p>Your cart is empty.</p>
                                                    @endforelse
                                                    <div class="items-pur ">
                                                        <div class="check-flex">
                                                            <div class="w-100">
                                                                @if ($totalframecost > 0)
                                                                    <div class="d-flex-item"><span>Frame Cost</span>
                                                                        <span class="fw-bold">
                                                                            {{ config('app.default_currency') }}{{ $totalframecost }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                                <div class="d-flex-item"><span>Shipping</span>
                                                                    <span class="fw-bold shppingCostDiv">
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex-item mt-4"><span>Coupon<strong> (NEW10)</strong></span><span class=""><strong>-US$19</strong> (-10%) </span></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="order-checkout" style="border-top: 1px solid lightgrey;">
                                                        <div class="d-flex-item items-pur pt-2 mb-0 pb-0">
                                                            <span>Total</span><span
                                                                class="fw-bold totalByCountryCondition"></span>
                                                            
                                                        </div>
                                                    </div> --}}
                                                    <div class="purchase-item mt-5">
                                                        <h3>Shipped to</h3>
                                                        <input type="hidden" name="hidden_final_amount"
                                                            id="hidden_final_amount" value="">
                                                        <input type="hidden" name="hidden_total_quantity"
                                                            id="hidden_total_quantity" value="">
                                                        <p id="p_shipping_address"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="button" name="previous"
                                class="previous action-button-previous btn btn-primary btn-lg" value="Back to shipping" />
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="payment_popup" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <form role="form">
                                        <div class="form-group">
                                            <label for="cardNumber">
                                                CARD NUMBER</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="cardNumber"
                                                    placeholder="Valid Card Number" required autofocus />
                                                <span class="input-group-addon"><span
                                                        class="glyphicon glyphicon-lock"></span></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-7 col-md-7">
                                                <div class="form-group">
                                                    <label for="expityMonth">
                                                        EXPIRY DATE</label>
                                                    <div class="row">
                                                        <div class="col-xs-6 col-lg-6 pl-ziro">
                                                            <input type="text" class="form-control" id="expityMonth"
                                                                placeholder="MM" required />
                                                        </div>
                                                        <div class="col-xs-6 col-lg-6 pl-ziro">
                                                            <input type="text" class="form-control" id="expityYear"
                                                                placeholder="YY" required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-5 col-md-5 pull-right">
                                                <div class="form-group">
                                                    <label for="cvCode">
                                                        CV CODE</label>
                                                    <input type="password" class="form-control" id="cvCode"
                                                        placeholder="CV" required />
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success">Pay US$158.69</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&libraries=places"></script>
    <script>
        $(document).ready(function() {
            var current_fs, next_fs, previous_fs; //fieldsets
            var opacity;
            var current = 1;
            var steps = $("fieldset").length;
            setProgressBar(current);
            $(".next").click(function() {
                let page = $(this).data('page');
                if (page === 'shipping') {
                    let isValid = shippingTabValidation();
                    if (!isValid) {
                        return false;
                    }
                }
                current_fs = $(this).parent();
                next_fs = $(this).parent().next();
                //Add Class Active
                $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
                //show the next fieldset
                next_fs.show();
                //hide the current fieldset with style
                current_fs.animate({
                    opacity: 0
                }, {
                    step: function(now) {
                        // for making fielset appear animation
                        opacity = 1 - now;
                        current_fs.css({
                            'display': 'none',
                            'position': 'relative'
                        });
                        next_fs.css({
                            'opacity': opacity
                        });
                    },
                    duration: 500
                });
                setProgressBar(++current);



                // Call AJAX when country changes
                $('#shopping_country').change(function() {
                    updateAmounts();
                });

                // Call AJAX when page loads
                updateAmounts();

            });


            $(".previous").click(function() {
                current_fs = $(this).parent();
                previous_fs = $(this).parent().prev();
                //Remove class active
                $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
                //show the previous fieldset
                previous_fs.show();
                //hide the current fieldset with style
                current_fs.animate({
                    opacity: 0
                }, {
                    step: function(now) {
                        // for making fielset appear animation
                        opacity = 1 - now;
                        current_fs.css({
                            'display': 'none',
                            'position': 'relative'
                        });
                        previous_fs.css({
                            'opacity': opacity
                        });
                    },
                    duration: 500
                });
                setProgressBar(--current);
            });

            function setProgressBar(curStep) {
                var percent = parseFloat(100 / steps) * curStep;
                percent = percent.toFixed();
                $(".progress-bar")
                    .css("width", percent + "%")
            }
            $(".submit").click(function() {
                return false;
            })
        });


        if ($('#same_as_shipping').prop("checked")) {
            $(".billing_address").hide(); // Hide the billing accordion
        } else {
            $(".billing_address").show(); // Show the billing accordion
        }

        $("#same_as_shipping").change(function() {
            if ($(this).prop("checked")) {
                $(".billing_address").hide(); // Hide the billing accordion
            } else {
                $(".billing_address").show(); // Show the billing accordion
            }
        });

        $("#shopping_address").change(function() {
            $("#p_shipping_address").html($(this).val());
        });


        function shippingTabValidation() {
            let isValid = true;
            // Array of required fields with their respective error messages
            let requiredFields = [{
                    id: "shopping_email",
                    message: "Email address is required"
                },
                {
                    id: "shopping_fullname",
                    message: "Full Name is required"
                },
                {
                    id: "shopping_phone",
                    message: "Phone Number is required"
                },
                {
                    id: "shopping_address",
                    message: "Address is required"
                },
                {
                    id: "shopping_city",
                    message: "City is required"
                },
                {
                    id: "shopping_postalcode",
                    message: "Postal Code is required"
                },
                {
                    id: "shopping_state",
                    message: "State is required"
                },
                {
                    id: "shopping_country",
                    message: "Country is required"
                },
            ];

            requiredFields.forEach(field => {
                let input = $("#" + field.id);
                if ($.trim(input.val()) === "") {
                    isValid = false;
                    input.addClass("is-invalid");
                    // toastr.error(field.message);
                } else {
                    input.removeClass("is-invalid");
                }
            });
            if ($("#same_as_shipping").prop("checked")) {
                return isValid;
            }

            let requiredBillingFields = [{
                    id: "billing_email",
                    message: "Billing Email is required"
                },
                {
                    id: "billing_fullname",
                    message: "Billing First Name is required"
                },
                {
                    id: "billing_phone",
                    message: "Billing Phone Number is required"
                },
                {
                    id: "billing_address",
                    message: "Billing Address is required"
                },
                {
                    id: "billing_city",
                    message: "Billing City is required"
                },
                {
                    id: "billing_postal",
                    message: "Billing Postal Code is required"
                },
                {
                    id: "billing_state",
                    message: "Billing State is required"
                },
                {
                    id: "billing_country",
                    message: "Billing Country is required"
                },
            ];

            requiredBillingFields.forEach(field => {
                let input = $("#" + field.id);
                if ($.trim(input.val()) === "") {
                    isValid = false;
                    input.addClass("is-invalid");
                    // toastr.error(field.message);
                } else {
                    input.removeClass("is-invalid");
                }
            });
            return isValid;
        }

        function initAutocomplete() {
            var input = document.getElementById("shopping_address");
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.addListener("place_changed", function() {
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    return;
                }
                let addressComponents = place.address_components;
                let city = "",
                    state = "",
                    country = "",
                    postal_code = "";
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
                // Fill other fields automatically
                document.getElementById("shopping_city").value = city;
                document.getElementById("shopping_state").value = state;
                // document.getElementById("shopping_country").value = country;
                document.getElementById("shopping_postalcode").value = postal_code;
                let countryDropdown = document.getElementById("shopping_country");
                for (let i = 0; i < countryDropdown.options.length; i++) {
                    if (countryDropdown.options[i].text.trim().toLowerCase() === country.trim().toLowerCase()) {
                        countryDropdown.value = countryDropdown.options[i].value;
                        break;
                    }
                }
                updateAmounts();
            });
        }
        // Initialize autocomplete when the page loads
        // google.maps.event.addDomListener(window, "load", initAutocomplete);
        window.addEventListener("load", initAutocomplete);
    </script>

    <!-- <script>
        function openAddGiftCard() {
            const demoDiv = document.getElementById("demoDiv");
            if (demoDiv.style.display === "none" || demoDiv.style.display === "") {
                demoDiv.style.display = "flex";
            } else {
                demoDiv.style.display = "none";
            }
        }
    </script> -->

    <script>
        function checkDiscountCoupon() {
            var couponValue = $('#couponCode').val().trim();
            if (couponValue == '') {
                $('#couponCodeError').text('Enter Coupon Code First.');
                return false;
            }
            $('#couponCodeError').text('');
            $.ajax({
                url: "{{ route('front.applyCouponCode') }}", // Replace with your route
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    couponValue: couponValue
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === 1) {
                        $("#hidden_final_amount").val(response.cartSummary.total);
                        $("#hidden_total_quantity").val(response.cartSummary.cartTotalQuantity);
                        $(".checkout-shopping-cart").html(response.cartSummary.checkoutshoppingCart);
                        $(".checkout-order-summary,.checkout-review").html(response.cartSummary
                            .checkoutOrderSummary);
                    } else {
                        $('#couponCodeError').text('');
                        $('#couponCodeError').text(response.message);
                    }
                },
                error: function(res) {
                    console.log(res); // Log the error for debugging
                    $('#couponCodeError').text('There was an error applying the coupon.');
                }
            });

            return false; // Prevent the form from submitting
        }

        function removeDiscountCoupon() {
            $('#couponCodeError').text('');
            $.ajax({
                url: "{{ route('front.removeCouponCode') }}", // Replace with your route
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === 1) {
                        $("#hidden_final_amount").val(response.cartSummary.total);
                        $("#hidden_total_quantity").val(response.cartSummary.cartTotalQuantity);
                        $(".checkout-shopping-cart").html(response.cartSummary.checkoutshoppingCart);
                        $(".checkout-order-summary,.checkout-review").html(response.cartSummary
                            .checkoutOrderSummary);

                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(res) {
                    console.log(res); // Log the error for debugging
                    toastr.error('There was an error removing the coupon.');
                }
            });

            return false;
        }
    </script>

@endpush
@include('front.checkout.checkout-paypal')
