<?php

namespace App\Services\FrontEnd;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Repository\Eloquent\OrderRepository;

class GiftCardService
{
    protected $OrderRepository;
    public function __construct(OrderRepository $OrderRepository)
    {
        $this->OrderRepository = $OrderRepository;
    }

    /**
     * Get the gift card details.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGiftCard($request)
    {
        try {
            $giftCardType = $request->gitCardType;
            $giftCardValue = $request->gitCardCost;

            $data =  view('front.component.gift-card-component', compact('giftCardType', 'giftCardValue'))->render();

            return response()->json([
                'status' => '1',
                'message' => __('message.statusOne', ['parameter' => 'Gift Card']),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in GiftCardService/getGiftCard: ' . $e->getMessage() . ' in line ' . $e->getLine());

            return response()->json([
                'status' => '0',
                'message' => __('message.statusZero')
            ]);
        }
    }

    /**
     * Purchase a gift card.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseGiftCard($request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'status' => 0,
                    'message' => "Please login to continue."
                ]);
            }

            $userId = auth()->id();
            // $productId = $request->gitCardType;
            $productId = $request->giftcard_id;

            $cartData = [
                'name'       => "giftcard",
                'price'      => $request->gitCardCost,
                'size_vert'  => null,
                'frame'      => null,
                'size_horiz' => null,
                'quantity'   => 1,
                'giftcard_name' => $request->recipientName,
                'email'      => $request->recipientEmail,
                'message'    => $request->giftCardMessage,
                'product_id' => $productId,
                // 'user_id' => $userId,
            ];

            $cart = Cart::updateOrCreate(
                ['user_id' => $userId, 'product_id' => $productId, 'name' => 'giftcard'],
                $cartData
            );
            // $cart = Cart::create(
            //     ['user_id' => $userId, 'product_id' => $productId, 'name' => 'giftcard'],
            //     $cartData
            // );

            if ($cart) {
                return response()->json([
                    'status' => 1,
                    'message' => __('message.statusCart', ['parameter' => 'Gift Card'])
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => __('message.statusZero')
            ]);
        } catch (\Exception $e) {
            Log::error('Error in GiftCardService/purchaseGiftCard: ' . $e->getMessage() . ' in line ' . $e->getLine());

            return response()->json([
                'status' => 0,
                'message' => __('message.statusZero')
            ]);
        }
    }


    public function redeemGiftcard($code)
    {
        try {
            // dd($code);
            $giftCard = $this->OrderRepository->getOneGiftcard(['code' => $code]);
            if (!$giftCard) {
                return redirect()->route('front.index')->with('giftcard_error', __('message.statusFour', ['parameter' => 'Gift Card redeem link']));
            }

            session()->put('redeemGiftcard', $code);
            if (!Auth::check()) {
                return redirect()->route('front.index')->with([
                    'giftcard_action' => 'not_logged_in'
                ]);
            }

            $giftCard = $this->OrderRepository->getOneGiftcard(['code' => $code]);
            if (!$giftCard) {
                return redirect()->route('front.index')->with('giftcard_error', __('message.statusFour', ['parameter' => 'Gift Card redeem link']));
            }

            if ($giftCard->status == 1 || $giftCard->status == 2) {
                return redirect()->route('front.index')->with('giftcard_error', 'Gift Card already redeemed, to check visit your Received Gift cards.');
            }

            $update['status'] = 2;
            $update['redeem_by'] = auth()->user()->id;
            $run = $this->OrderRepository->updateGiftcard(['code' => $code], $update);


            if ($run) {
                return redirect()->route('received-giftcards')->with('giftcard_success', 'You have successfully redeemed the gift card.');
            }

            return response()->json([
                'status' => '0',
                'message' => __('message.statusZero')
            ]);
        } catch (\Exception $e) {
            Log::error('Error in GiftCardService/redeemGiftcard: ' . $e->getMessage() . ' in line ' . $e->getLine());

            return response()->json([
                'status' => '0',
                'message' => __('message.statusZero')
            ]);
        }
    }
}
