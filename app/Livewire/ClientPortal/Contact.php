<?php

namespace App\Livewire\ClientPortal;

use Livewire\Component;

class Contact extends Component
{
    public function render()
    {
        return view('livewire.client-portal.contact')
            ->layout('layouts.app');
    }
}
