<?php
namespace App\Livewire;

use Livewire\Component;
use App\Services\FrontEnd\Cart\CartService;

class CartComponent extends Component
{
    public $cart = [];
    protected $cartService;

    public function boot(CartService $cartService) // Dependency Injection
    {
        $this->cartService = $cartService;
    }

    public function mount()
    {
        $this->cart = $this->cartService->getCartItems();
    }

    public function addToCart($id, $name, $price)
{
    logger('addToCart called', compact('id', 'name', 'price'));
    
    $this->cartService->addToCart($id, $name, $price, 1);
    $this->cart = $this->cartService->getCartItems();
    $this->dispatch('cartUpdated');
}

    public function removeFromCart($id)
    {
        $this->cartService->removeFromCart($id);
        $this->cart = $this->cartService->getCartItems();
        $this->dispatch('cartUpdated');
    }

    public function render()
    {
        // return view('livewire.cart-component');
        return view('livewire.cart-component');
    }
}
