<?php

namespace App\Livewire\ClientPortal;

use Livewire\Component;

class About extends Component
{
    public function render()
    {
        return view('livewire.client-portal.about')
            ->layout('layouts.app');
    }
}
