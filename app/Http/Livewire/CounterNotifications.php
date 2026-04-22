<?php

namespace App\Http\Livewire;

use Livewire\Component;

use App\Models\Notification;

class CounterNotifications extends Component
{
	public $count_notifications=0;
	public $notifications=[];

	 protected $listeners = ['updateNotifications' => 'updateNotifications'];

    public function updateNotifications()
    {
    	$notification = new Notification();
    	$notifications = $notification->getData();

    	$this->notifications = $notifications;
    	$this->count_notifications = $notifications ? sizeof($notifications) : 0;
    }

    public function render()
    {
    	$this->updateNotifications();

        return view('livewire.counter-notifications');
    }
}
