<?php

namespace App\Http\Livewire;

use Livewire\Component;

use App\Permission;

class Search extends Component
{
    public $query;
    public $permissions;
    public $highlightIndex;

    public function mount()
    {
        $this->query = '';
        $this->permissions = [];
        $this->highlightIndex = 0;
    }

    public function incrementHighlight()
    {
        if ($this->highlightIndex === count($this->permissions) - 1) {
            $this->highlightIndex = 0;
            return;
        }
        $this->highlightIndex++;
    }

    public function decrementHighlight()
    {
        if ($this->highlightIndex === 0) {
            $this->highlightIndex = count($this->permissions) - 1;
            return;
        }
        $this->highlightIndex--;
    }

    public function selectPermission()
    {
        $permission = $this->permissions[$this->highlightIndex] ?? null;
        if ($permission) {
            $this->redirect(route('show-contact', $contact['id']));
        }
    }

    public function updatedQuery()
    {
        $this->permissions = ($this->query) 
                ? Permission::select('display_name', 'url')
                        ->where('keywords', 'like', '%'.$this->query.'%')
                        ->get()
                :[];
    }

	public function render()
    {
        return view('livewire.search');
    }
}
