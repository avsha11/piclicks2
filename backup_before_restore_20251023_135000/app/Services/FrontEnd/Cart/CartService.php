<?php

namespace App\Services\FrontEnd\Cart;

use App\Repository\FrontEnd\Cart\CartRepositoryInterface;
use Exception;
use App\Repository\Eloquent\{OrderRepository, TransactionRepository};
use App\Models\{DesignCollageMaster, Cart};
use App\Mail\{NewOrderPlace, NewGiftcardMail};
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Session;
use Carbon\Carbon;
use DB;
use App\Repository\Admin\CouponRepository;
use App\Services\CollageServices;


class CartService

{
    protected $cartRepository, $OrderRepository, $TransactionRepository, $CouponRepository, $CollageServices;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        OrderRepository $OrderRepository,
        TransactionRepository $TransactionRepository,
        CouponRepository $CouponRepository,
        CollageServices $CollageServices
    ) {
        $this->cartRepository = $cartRepository;
        $this->OrderRepository = $OrderRepository;
        $this->TransactionRepository = $TransactionRepository;
        $this->CouponRepository = $CouponRepository;
        $this->CollageServices = $CollageServices;
    }



    public function addToCart($id, $priceId, $name, $price, $quantity, $size_horiz, $size_vert, $frame)
    {
        return $this->cartRepository->addItem($id, $priceId, $name, $price, $quantity, $size_horiz, $size_vert, $frame);
    }



    public function getCartItems()
    {
        return $this->cartRepository->getCartItems();
    }


    public function removeFromCart($id)
    {
        return $this->cartRepository->removeItem($id);
    }


    public function clearCart()
    {
        return $this->cartRepository->clear();
    }



    // apply coupon code
    function applyCouponCode($request)
    {
        try {

            $checkCouponExist = $this->CouponRepository->getSingle(['coupon_code' => $request['couponValue']]);

            if (isset($checkCouponExist) && !empty($checkCouponExist)) {
                if ($checkCouponExist['limit_used'] < $checkCouponExist['usage_count']) {

                    session()->put('coupon_code', $checkCouponExist['coupon_code']);
                    session()->put('discount_amount', $checkCouponExist['discount']);
                    session()->put('disc_amt_type', $checkCouponExist['discount_type']);
                } else {
                    return response()->json(['status' => 0, 'message' => 'Invalid coupon']);
                }
            } else {

                return response()->json(['status' => 0, 'message' => 'Invalid coupon, try again.']);
            }

            $calculateCart = calculateCart();

            return response()->json([
                'status' => 1,
                'cartSummary' => $calculateCart,
                'message' => '',
            ]);
        } catch (Exception $e) {
            Log::error('Error in CartService/applyCouponCode :' . $e->getMessage() . 'in line ' . $e->getLine());
            return response()->json(['status' => 0, 'message' => __('message.statusZero') . "in catch" . '' . $e->getMessage() . 'in line ' . $e->getLine()]);
        }
    }

    function removeCouponCode($request)
    {
        try {
            session()->forget([
                'coupon_code',
                'discount_amount',
                'disc_amt_type',
            ]);

            $calculateCart = calculateCart();

            return response()->json([
                'status' => 1,
                'cartSummary' => $calculateCart,
                'message' => 'Coupon code removed successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Error in CartService/removeCouponCode :' . $e->getMessage() . 'in line ' . $e->getLine());
            return response()->json(['status' => 0, 'message' => __('message.statusZero') . "in cat"]);
        }
    }


    public function updateCart($data)
    {
        $cartItem = $this->cartRepository->findById($data['id']);
        // dd($data);
        if (!$cartItem) {
            return response()->json(['error' => 'Cart item not found'], 404);
        }

        if ($data['action'] == 'increase') {
            $cartItem->quantity += 1;
        } elseif ($data['action'] == 'decrease' && $cartItem->quantity > 1) {
            $cartItem->quantity -= 1;
        } elseif ($data['action'] == 'delete') {

            $result =  $this->cartRepository->delete($data['id']);
            if ($result) {
                return response()->json([
                    'status' => 1,
                    'success' => 'Item removed',
                    'cartSummary' => calculateCart()
                ]);
            } else {
                return response()->json(['status' => 0, 'success' => 'Not removed from cart, try again.']);
            }
        }

        $result =  $this->cartRepository->update($cartItem->id, ['quantity' => $cartItem->quantity]);

        if ($result) {
            return response()->json([
                'status' => 1,
                'success' => 'Cart updated successfully',
                'cartSummary' => calculateCart(),
                'updatedQuantity' => $cartItem->quantity
            ]);
        } else {
            return response()->json(['status' => 0, 'success' => 'Cart not updated, try again.']);
        }
    }

    function successCheckout($request, $internal_order_id)
    {
        try {

            $orderID = $request->orderID;
            // dd($request->all(), $internal_order_id, $orderID);



            // return json_encode(['status' => 1, 'message' => "Order placed successfully . Paypal order id:" . $orderID . ". Our Order id: " . $internal_order_id, 'data' => []]);
            // $booking_id = rand(10000, 99999);

            DB::beginTransaction();

            $countryData = \App\Models\Countries::where('code', getCountryCode())->first() ?? 0;
            $shippingAmount = $countryData['shipping_amount'] ?? 0;
            $saleTaxRate = $countryData['vat'] ?? 0;


            $trans['user_id'] = auth()->id();
            $trans['internal_order_id'] = $internal_order_id;
            $trans['transaction_id'] = $orderID;
            $trans['payment_gateway'] = 'paypal';
            $trans['total_amount'] = $request->hidden_final_amount;
            $trans['other'] = json_encode([]);

            $run_trans = $this->TransactionRepository->create($trans);



            $master['user_id'] = auth()->id();
            $master['internal_order_id'] = $internal_order_id;
            $master['order_status'] = 'Ordered';
            $master['total_amount'] = 0;
            $master['total_quantity'] = 0;
            // type, code, discount_amount
            $master['delivery_date'] = Carbon::now()->addDays(14)->format('Y-m-d');
            $master['order_tracking'] = json_encode([0 => [
                'status' => 'Ordered',
                'date' => Carbon::now()->format('d-m-Y H:i:s'),
            ]]);

            $master['shipping_fullname'] = $request->shopping_fullname;
            $master['shipping_phone'] = $request->shopping_phone;
            $master['shipping_email'] = $request->shopping_email;
            $master['shipping_company_name'] = $request->shopping_company_name;
            $master['shipping_address'] = $request->shopping_address;
            $master['shipping_address_opt'] = $request->shopping_address_opt;
            $master['shipping_city'] = $request->shopping_city;
            $master['shipping_postalcode'] = $request->shopping_postalcode;
            $master['shipping_state'] = $request->shopping_state;
            $master['shipping_country'] = $request->shopping_country;
            $master['billing_fullname'] = $request->billing_fullname;
            $master['billing_phone'] = $request->billing_phone;
            $master['billing_email'] = $request->billing_email;
            $master['billing_address'] = $request->billing_address;
            $master['billing_address_opt'] = $request->billing_address_opt;
            $master['billing_city'] = $request->billing_city;
            $master['billing_postal'] = $request->billing_postal;
            $master['billing_state'] = $request->billing_state;
            $master['billing_country'] = $request->billing_country;

            $run = $this->OrderRepository->create($master);

            if ($run && $run_trans) {


                $collageCount = 0;
                $amountItemTotal = 0;
                $total_quantity = 0;
                $total_vat = 0;
                $getCartItems = $this->cartRepository->getCartItems();

                foreach ($getCartItems as $item) {

                    $details = [];

                    $total_quantity += $item->quantity;

                    $itemPrice = 0;
                    if ($item->name == 'collage') {
                        $collage = DesignCollageMaster::where('unique_id', $item->product_id)->first();
                        $itemPrice = (getUserItemItems($collage->total_tiles, $item->price_id)->cost ?? 0);
                        $amountItem = $itemPrice * $item->quantity;

                        $details['collage_unique_id'] = $item->product_id;
                        $details['giftcard_id'] = 0;
                        $details['price_id'] = $collage->price_id;
                        $collageCount++;
                    } elseif ($item->name == 'giftcard') {

                        $itemPrice = $item->price;
                        $amountItem = $itemPrice * $item->quantity;

                        $details['collage_unique_id'] = 0;
                        $details['giftcard_id'] = $item->product_id;
                        $details['price_id'] = 0;
                    } elseif ($item->name == 'artgallery') {

                        $collage = DesignCollageMaster::where('unique_id', $item->product_id)->first();
                        $itemPrice = (getUserItemItems($collage->total_tiles, $item->price_id)->cost ?? 0);
                        $amountItem = $itemPrice * $item->quantity;
                        
                        $unique_id = '';
                        if ($collage->artgallery_unique_id == '') {
                            $master = DB::table('design_collage_master')->where('unique_id', $item->product_id)->first();
                            [$returnstatus, $message, $new_unique_id] = $this->CollageServices->artGalleryEdit($item->product_id, $master, 1);
                            if ($returnstatus === 1) {

                                $unique_id = $new_unique_id;
                                $collage = DesignCollageMaster::where('unique_id', $unique_id)->first();
                                Log::error('Error in CartService/successCheckout if (returnstatus === 1) {:');
                            } else {

                                $unique_id = $item->product_id;
                                Log::error('Error in CartService/successCheckout if (returnstatus === 1) else :' . $message);
                            }
                        } else {

                            $unique_id = $item->product_id;
                        }

                        $details['collage_unique_id'] = $unique_id;
                        $details['giftcard_id'] = 0;
                        $details['price_id'] = $collage->price_id;
                        $collageCount++;
                    }
                    // if (!empty($saleTaxRate)) {
                    //     $vat_amt = ($saleTaxRate * $amountItem) / 100;
                    //     $amountItem = $vat_amt + $amountItem;
                    //     $total_vat += $vat_amt;
                    // }
                    $amountItemTotal += $amountItem;


                    $details['master_id'] = $run->id;
                    $details['name'] = $item->name;
                    $details['quantity'] = $item->quantity;
                    $details['amount'] = $amountItem;
                    $this->OrderRepository->createDetail($details);

                    if (isset($collage) && $collage) {
                        $collage->status = 1;
                        $collage->save();
                    }

                    if ($item->name == 'giftcard') {
                        $giftcard_detail = [
                            'master_id' => $run->id,
                            'code' => \Str::random(20),
                            'giftcard_id' => $item->product_id,
                            'price' => $itemPrice,
                            'quantity' => $item->quantity,
                            'total_amt' => $itemPrice * $item->quantity,
                            'name' => $item->giftcard_name,
                            'email' => $item->email,
                            'message' => $item->message,
                            'status' => 0,
                            'used_on_order_id' => null,
                        ];
                        $this->OrderRepository->createGiftcard($giftcard_detail);
                    }
                }


                $subTotal = $amountItemTotal;
                if ($collageCount === 0) {
                    $shippingAmount = 0;
                }
                $amountItemTotal = $amountItemTotal + $shippingAmount;

                $giftcard_code = session('giftcard_code', '');
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



                $updmaster['total_quantity'] = $total_quantity;
                $updmaster['shipping_amount'] = $shippingAmount;
                $updmaster['vat_amount'] = $total_vat;
                $updmaster['total_amount'] = $finalAmount;
                $updmaster['sub_total'] = $subTotal;

                $updmaster['giftcard_amount'] = $getGiftcardAmount;
                $updmaster['giftcard_code'] = $giftcard_code;

                $updmaster['coupon_code'] = $couponCode;
                $updmaster['discount_amount'] = $getDiscountPriceAmount;
                $run2 = $this->OrderRepository->update(['id' => $run->id], $updmaster);

                if ($discountAmount != 0) {
                    $checkCouponExist = $this->CouponRepository->getSingle(['coupon_code' => $couponCode]);
                    $couponupdate['limit_used'] = $checkCouponExist['limit_used'] + 1;
                    if (isset($checkCouponExist) && !empty($checkCouponExist)) {
                        $this->CouponRepository->update(['coupon_code' => $couponCode], $couponupdate);
                    }
                }

                if ($getGiftcardAmount > 0) {
                    $giftupdate['status'] = 1;
                    $giftupdate['used_on_order_id'] = $run->id;
                    if (strpos($giftcard_code, ',') !== false) {
                        // Multiple codes
                        $codes = explode(',', $giftcard_code);
                        foreach ($codes as $code) {
                            $this->OrderRepository->updateGiftcard(['code' => trim($code)], $giftupdate);
                        }
                    } else {
                        // Single code
                        $this->OrderRepository->updateGiftcard(['code' => $giftcard_code], $giftupdate);
                    }
                }

                // DB::rollback();
                // dd($request->hidden_final_amount, ($finalAmount + $shippingAmount), $finalAmount, $shippingAmount, $total_vat);

                Session::forget('session_internal_order_id');
                Session::forget('discount_amount');
                Session::forget('disc_amt_type');
                Session::forget('coupon_code');
                Session::forget('giftcard_amount');
                Session::forget('giftcard_code');

                Cart::where('user_id', auth()->id())->delete();

                DB::commit();


                // new order confirm email
                try {
                    $mailData["order_data"] = $this->OrderRepository->getOne(['id' => $run->id]);
                    $mailData["user_data"] = auth()->user();
                    $mailData['subject'] = 'Order Confirmation';
                    Mail::to($request->shopping_email)->send(new NewOrderPlace($mailData));
                } catch (Exception $e) {
                    Log::error('Error in CartService/successCheckout NewOrderPlace Mail error:' . $e->getMessage() . 'in line ' . $e->getLine());
                }


                // send giftcard email

                try {
                    $giftcards_data = $this->OrderRepository->getByWhereGiftcard(['master_id' => $run->id]);
                    if ($giftcards_data->isNotEmpty()) {
                        foreach ($giftcards_data as $key => $value) {
                            $mailData["giftcard_data"] = $value;
                            $mailData["user_data"] = auth()->user();
                            $mailData['subject'] = 'Giftcard Received';
                            Mail::to($value->email)->send(new NewGiftcardMail($mailData));
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Error in CartService/successCheckout NewGiftcardMail Mail error:' . $e->getMessage() . 'in line ' . $e->getLine());
                }


                return response()->json(['status' => 1, 'message' => 'Order placed successfully.', 'data' => ['url' => route('order')]]);
            } else {
                DB::rollback();
                return response()->json(['status' => 0, 'message' => 'Payment successfull, but order purchase failed, contact administrator', 'data' => []]);
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error in CartService/successCheckout :' . $e->getMessage() . 'in line ' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero') . "in cart service catch" . '' . $e->getMessage() . 'in line ' . $e->getLine()]);
        }
    }
}
