<?php

namespace App\Repository\FrontEnd\Cart;



interface CartRepositoryInterface

{

    public function addItem($id, $priceId, $name, $price, $quantity, $size_horiz ,$size_vert, $frame);

    public function getCartItems();

    public function removeItem($id);

    public function clear();

}

