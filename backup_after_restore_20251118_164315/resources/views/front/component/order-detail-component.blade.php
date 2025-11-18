<div class="purchase-item">
    <div class="badge bg-secondary my-3">{{ $order->order_status ?? '' }}</div>
    <div class="timeline">
        <div class="">
            <div class="wrapper">
                @php
                    $statuses = json_decode($order->order_tracking, true);
                @endphp

                <ul class="sessions mt-0">
                    @if (isset($statuses) && count($statuses) > 0)
                        @foreach ($statuses as $status)
                            <li>
                                <div class="time">
                                    {{ \Carbon\Carbon::parse($status['date'])->format('M d') }}
                                </div>
                                <p>{{ $status['status'] }}</p>
                            </li>
                        @endforeach
                    @else
                        <li>
                            <div class="time">No Status</div>
                            <p>No status available</p>
                        </li>
                    @endif
                </ul>
            </div>

        </div>
    </div>

    <div class="order-checkout">
        <table class="table">
            <tbody>
                <tr>
                    <td>Address </td>
                    <td class="text-end">{{ $order->shipping_address ?? '' }}</td>
                </tr>
                <tr>
                    <td>Order Date</td>
                    <td class="text-end">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td>Delivery Date</td>
                    <td class="text-end">{{ \Carbon\Carbon::parse($order->delivery_date)->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <th>Subtotal </th>
                    <th class="text-end">{{ config('app.default_currency') }}{{ $order->sub_total ?? '' }} </th>
                </tr>
                <tr>
                    <td>Shipping </td>
                    <td class="text-end">{{ config('app.default_currency') }}{{ $order->shipping_amount ?? '' }} </td>
                </tr>
                @if($order->giftcard_amount > 0)
                    <tr>
                        <td>Giftcard @php echo (stripos($order->giftcard_code, ',') ? '(s)' : '') @endphp applied</td>
                        <td class="text-end text-danger">- {{ config('app.default_currency') }}{{ $order->giftcard_amount ?? '' }} </td>
                    </tr>
                @endif
                @if ($order->discount_amount > 0)
                    <tr>
                        <td>Coupon applied ({{ $order->coupon_code }})</td>
                        <td class="text-end text-danger">-{{ config('app.default_currency') }}{{ $order->discount_amount ?? '' }} </td>
                    </tr>
                @endif
                <tr>
                    <th>Total </th>
                    <th class="text-end">{{ config('app.default_currency') }}{{ $order->total_amount ?? '' }} </th>
                </tr>
            </tbody>
        </table>
    </div>
</div>
