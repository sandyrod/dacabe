<?php

namespace App\Http\Livewire;

use Livewire\Component;

use App\Models\{Pedido, OrderInven, OrderGrupo};

class Shoppingcart extends Component
{
	public $cgrupo = null;
    public $groups = null;
	public $order_invens = null;

	//protected $listeners = ['updateCarts' => 'updateCarts'];


    public function refreshProducts()
    {
        $this->order_invens = (new OrderInven)->getGroupProducts($this->cgrupo);
    }

    private function loadData()
    {
        $this->order_invens = (new OrderInven)->getData();
        $this->groups = (new OrderGrupo)->getData();
    }

    public function render()
    {
    	$this->loadData();

        return view('livewire.shoppingcart');
    }
}
