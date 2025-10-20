<?php

namespace App\Repository\FrontEnd\Cart;



use App\Models\Cart;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Session;



class CartRepository implements CartRepositoryInterface

{

    public function addItem($id, $priceId,$name, $price, $quantity, $size_horiz ,$size_vert, $frame)

    {

        

    if (Auth::check()) {

        // Store in database if user is logged in

        $cartItem = Cart::updateOrCreate( 

                ['user_id' => Auth::id(), 'product_id' => $id],

                ['name' => $name, 'price_id' => $priceId, 'price' => $price, 'size_vert' => $size_vert, 'frame' => $frame, 'size_horiz' => $size_horiz,  'quantity' => \DB::raw("IFNULL(quantity, 0) + $quantity")]

            );

      

    } else {

        // Generate guest ID if not logged in

        $userId = getUserId();

        session()->put('guest_id', getUserId());
    
        $cartItem = Cart::updateOrCreate( 

                ['user_id' =>$userId, 'product_id' => $id],

                ['name' => $name, 'price_id' => $priceId,'price' => $price, 'size_vert' => $size_vert, 'frame' => $frame, 'size_horiz' => $size_horiz,  'quantity' =>  \DB::raw("IFNULL(quantity, 0) + $quantity")]

            );

    }

    

    }



    public function getCartItems()

    {

        return Auth::check() ? Cart::where('user_id', Auth::id())->get() : Session::get('cart', []);

    }



    public function removeItem($id)

    {

        if (Auth::check()) {

            Cart::where('user_id', Auth::id())->where('product_id', $id)->delete();

        } else {

            $cart = Session::get('cart', []);

            unset($cart[$id]);

            Session::put('cart', $cart);

        }

    }



    public function clear()

    {

        if (Auth::check()) {

            Cart::where('user_id', Auth::id())->delete();

        } else {

            Session::forget('cart');

        }

    }

    private function generateGuestId()

    {

        return 'guest_' . uniqid();

    }


    public function findById($id)
    {
        return Cart::find($id);
    }

    public function update($id, $data)
    {
        return Cart::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        if (Auth::check()) {
            $cartItem = Cart::where('user_id', Auth::id())->where('id', $id)->first();
            // $cartItem = Cart::where('id', $id)->first();
            if ($cartItem) {
                $cartItem->delete(); // Soft delete if enabled
                return true;
            }
            return false;
        }else{
            $guestId = session()->get('guest_id');
            // $cartItem = Cart::where('user_id', $guestId)->where('id', $id)->first();
            $cartItem = Cart::where('user_id', $guestId)->where('id', $id)->first();
            if ($cartItem) {
                $cartItem->delete(); // Soft delete if enabled
                return true;
            }
            return false;
        }
    
        $cart = Session::get('cart', []);
        unset($cart[$id]);
        Session::put('cart', $cart);
        return true;
    }




}

