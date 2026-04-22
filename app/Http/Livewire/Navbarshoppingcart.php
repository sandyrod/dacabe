<?php

namespace App\Http\Livewire;

use Livewire\Component;

use App\Models\Pedido;

class Navbarshoppingcart extends Component
{
	public $count_carts=0;
	public $carts=[];

	 protected $listeners = ['updateCarts' => 'updateCarts'];

    public function updateCarts()
    {
        $carts = null;
        $this->carts = (new Pedido)->getActiveOrderDetail();
        
        $this->count_carts = $this->carts ? sizeof($this->carts) : 0;
    }

    public function getLasstOrder()
    {
        $carts = null;
        $cart = (new Pedido)->getActiveOrder();
        
        $this->count_carts = $carts ? sizeof($carts) : 0;
    }

    public function render()
    {
    	$this->updateCarts();

        return view('livewire.navbarshoppingcart');
    }
}
