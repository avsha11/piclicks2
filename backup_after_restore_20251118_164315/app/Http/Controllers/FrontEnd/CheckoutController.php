<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FrontEnd\Cart\CartService;
use App\Models\Countries;
use Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;


class CheckoutController extends Controller
{
    protected $cartService;
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $result = $this->cartService->getCartItems();
        // dd($result);
        $countries = Countries::orderBy('name', 'asc')->get();
        $accessToken = generatePaypalAccessToken();
        // dd($result);
        return view('front.checkout.checkout', compact('result', 'accessToken', 'countries'));
    }


    // function paypal_create_order(Request $request)
    // {
    //     $shopping_fullname = $request->shopping_fullname;
    //     $total_quantity = $request->total_quantity;
    //     $internal_order_id = rand(10000, 99999);
    //     // Session::put('sessionbooking_id', $internal_order_id);
    //     Session::put('session_internal_order_id', $internal_order_id);
    //     $stripe_msg = 'Total ' . $total_quantity . ' collages, bought by ' . $shopping_fullname . '.';
    //     $accessToken = generatePaypalAccessToken();
    //     $url = env('PAYPAL_API_BASEURL') . "v2/checkout/orders";
    //     $response = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => "Bearer {$accessToken}",
    //     ])->post($url, [
    //         'intent' => 'CAPTURE',
    //         'purchase_units' => [
    //             [
    //                 'amount' => [
    //                     'currency_code' => 'USD',
    //                     'value' => $request->cart[0]['quantity'],
    //                 ],
    //                 "description" => $stripe_msg,
    //             ],
    //         ],
    //     ]);
    //     return $response->json();
    // }
    function paypal_create_order(Request $request)
    {
        $shopping_fullname = $request->shopping_fullname;
        $total_quantity = $request->total_quantity;
        $internal_order_id = rand(10000, 99999);
        Session::put('session_internal_order_id', $internal_order_id);
        $stripe_msg = 'Total ' . $total_quantity . ' items, bought by ' . $shopping_fullname . '.';
        $accessToken = generatePaypalAccessToken();
        $url = env('PAYPAL_API_BASEURL') . "v2/checkout/orders";
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$accessToken}",
        ])->post($url, [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => $request->cart[0]['quantity'], // Ensure this is the correct total price
                    ],
                    'description' => $stripe_msg,
                    'shipping' => [
                        'address' => [
                            'address_line_1' => $request->address_line_1 ?? '',
                            'address_line_2' => $request->address_line_2 ?? '',
                            'admin_area_2' => $request->city ?? '',
                            'admin_area_1' => $request->state ?? '',
                            'postal_code' => $request->postal_code ?? '',
                            'country_code' => $request->country_code,
                        ],
                    ],
                ],
            ],
            'payer' => [
                'name' => [
                    'given_name' => $request->first_name ?? '',
                    'surname' => $request->last_name ?? '',
                ],
                'email_address' => $request->email ?? '',
                'phone' => [
                    'phone_type' => 'MOBILE',
                    'phone_number' => [
                        'national_number' => $request->phone ?? '',
                    ],
                ],
                'address' => [
                    'address_line_1' => $request->address_line_1 ?? '',
                    'address_line_2' => $request->address_line_2 ?? '',
                    'admin_area_2' => $request->city ?? '',
                    'admin_area_1' => $request->state ?? '',
                    'postal_code' => $request->postal_code ?? '',
                    'country_code' =>  $request->country_code,
                ],
            ],
        ]);
        return $response->json();
    }

    function paypal_capture_order(Request $request)
    {
        try {
            $accessToken = generatePaypalAccessToken();
            $url = env('PAYPAL_API_BASEURL') . 'v2/checkout/orders/' . $request->orderID . '/capture';
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response, true);
            return $result;
        } catch (Exception $e) {
            return json_encode(['status' => 0, 'message' => 'Something went wrong in capture, contact administrator', 'data' => ['errormsg' => $e->getMessage(), 'errorlino' => $e->getLine()]]);
        }
    }

    public function testPaypalPayment()
    {
        $mockData = new \Illuminate\Http\Request([
            "shopping_fullname" => "webwidersas",
            "shopping_phone" => "32423",
            "shopping_email" => "behlah.webwiders@gmail.com",
            "shopping_company_name" => null,
            "shopping_address" => "Indore, Madhya Pradesh, India",
            "shopping_address_opt" => null,
            "shopping_city" => "Indore",
            "shopping_postalcode" => "53",
            "shopping_state" => "Madhya Pradesh",
            "shopping_country" => "IN",
            "billing_fullname" => null,
            "billing_phone" => null,
            "billing_email" => null,
            "billing_address" => null,
            "billing_address_opt" => null,
            "billing_city" => null,
            "billing_postal" => null,
            "billing_state" => null,
            "billing_country" => null,
            "hidden_final_amount" => "219",
            "hidden_total_quantity" => "1",
            "orderID" => "29494949A3458092M" . rand(1000, 9999),
            "status" => "COMPLETED"
        ]);
        session(['session_internal_order_id' => 123456]);
        return $this->paypal_success_payment($mockData);
    }

    public function paypal_success_payment(Request $request)
    {
        try {
            if ($request->status !== "COMPLETED") {
                return json_encode(['status' => 0, 'message' => 'Payment failed, status-' . $request->status . ', please try again.', 'data' => []]);
            }
            $internal_order_id = session()->get('session_internal_order_id');
            if (empty($internal_order_id)) {
                return json_encode(['status' => 0, 'message' => 'Order not found, please try again.', 'data' => []]);
            }
            return $this->cartService->successCheckout($request, $internal_order_id);
        } catch (Exception $e) {
            Log::error("Error in PaypalController.paypal_success_payment(): message-" . $e->getMessage() . 'in line' . $e->getLine());
            return json_encode(['status' => 0, 'message' => 'Something went wrong in success payment, contact administrator', 'data' => ['errormsg' => $e->getMessage(), 'errorlino' => $e->getLine()]]);
        }
    }
}
