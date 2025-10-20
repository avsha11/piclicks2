
     @php $cartItems = getUserCartItems(); $amountFinal = 0;@endphp

@php 
$dataShipping = getUserAdminItems(); 
$sales_tax = $dataShipping->sale_tax;
$showBellow =false;
@endphp
@forelse($cartItems as $cartItemsData)
@php 
$count = 0; $count = count($cartItemsData->designCollage) ;
$amountFinal += getUserItemItems($count)->cost + getUserFramesItems($cartItemsData->frame);
$showBellow =true;
@endphp

                
@empty

@endforelse
@if($showBellow)
 <div class="order-checkout">
 <table class="table">
                        <tr>
                            <td>Shipping </td>
                            <td class="text-end">@if($dataShipping->shipping==0)Free @else {{ config('app.default_currency') }}{{ $dataShipping->shipping }} @endif </td>
                        </tr>
                        @if(getUserSalesTaxItems($amountFinal)['saleAmount']!=0)
                        <tr>
                            <td>Sales Tax </td> 
                            <td class="text-end">{{ config('app.default_currency') }}{{ getUserSalesTaxItems($amountFinal)['saleAmount'] }} </td>
                        </tr>
                        @endif
                        <tr>
                            <th>Total </th>
                            <th class="text-end">{{ config('app.default_currency') }}{{ getUserSalesTaxItems($amountFinal)['total'] }} </th>
                        </tr>
                    </table>
                    <div class="continue_btn mb-4">
                    <a href="javascript:void();"  onclick="showComingSoon()"class="btn btn-primary btn-lg w-100">Checkout</a>
                </div>
                 </div>
@endif
                   
               