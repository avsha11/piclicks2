<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Services\FrontEnd\GiftCardService;
use App\Repository\Eloquent\OrderRepository;
use App\Repository\Admin\GiftcardRepository;

class GiftCardController extends Controller
{
    protected $giftCardService, $OrderRepository, $GiftcardRepository;
    public function __construct(GiftCardService $giftCardService, OrderRepository $OrderRepository, GiftcardRepository $GiftcardRepository)
    {
        $this->giftCardService = $giftCardService;
        $this->OrderRepository = $OrderRepository;
        $this->GiftcardRepository = $GiftcardRepository;
    }

    
    // gift card page
    public function giftcard()
    {
        try {
            return view('front.gift-card');
        } catch (\Exception $e) {
            Log::error('Error in GiftCardController/giftcard :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function giftcardsList()
    {
        try {
            $giftcards = $this->GiftcardRepository->getAll(['amount', 'asc']);
            return view('front.giftcard-list', compact('giftcards'));
        } catch (\Exception $e) {
            Log::error('Error in GiftCardController/giftcardsList :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function getGiftCard(Request $request)
    {
        try {
            return  $this->giftCardService->getGiftCard($request);
        } catch (\Exception $e) {
            Log::error('Error in GiftCardController/getGiftCard :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function purchaseGiftCard(Request $request)
    {
        try {
            return  $this->giftCardService->purchaseGiftCard($request);
        } catch (\Exception $e) {
            Log::error('Error in GiftCardController/purchaseGiftCard :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    
    public function redeemGiftcard($code = '')
    {
        try {
            return  $this->giftCardService->redeemGiftcard($code);
        } catch (\Exception $e) {
            Log::error('Error in GiftCardController/redeemGiftcard :' . $e->getMessage() . 'in line' . $e->getLine());
            return redirect()->route('front.index')->with('giftcard_error', __('message.statusZero') . ' in giftcard redeem');
        }
    }

    public function receivedGiftcards()
    {
        try {
            $giftCards = $this->OrderRepository->getByWhereGiftcard(['redeem_by' => auth()->user()->id, 'status' => ['IN', 1, 2]]);
            
            return view('front.profile.received-giftcards', compact('giftCards'));
        } catch (\Exception $e) {
            Log::error('Error in GiftCardController/receivedGiftcards :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
}
