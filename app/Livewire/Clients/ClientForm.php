<?php

namespace App\Livewire\Clients;

use Livewire\Component;

class ClientForm extends Component
{
    public $clientId = null;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $notes = '';
    public $tags = []; // e.g., VIP, Late Payer
    public $tagInput = '';
    public $password = '';
    public $currency = 'USD';

    public function mount(?\App\Models\Client $client = null)
    {
        if ($client && $client->exists) {
            $this->clientId = $client->id;
            $this->name = $client->name;
            $this->email = $client->email;
            $this->phone = $client->phone;
            $this->notes = $client->notes;
            $this->tags = is_array($client->tags) ? $client->tags : [];
            $this->currency = $client->currency ?: 'USD';
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:clients,email,' . $this->clientId,
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'password' => $this->clientId ? 'nullable|min:8' : 'required|min:8',
            'currency' => 'required|in:USD,EUR,TND',
        ];
    }

    public function addTag()
    {
        $this->tagInput = trim($this->tagInput);
        if ($this->tagInput !== '' && !in_array($this->tagInput, $this->tags)) {
            $this->tags[] = $this->tagInput;
        }
        $this->tagInput = '';
    }

    public function removeTag($tag)
    {
        $this->tags = array_values(array_diff($this->tags, [$tag]));
    }

    public function save()
    {
        $this->validate();

        $clientRecord = \App\Models\Client::updateOrCreate(
            ['id' => $this->clientId],
            [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'notes' => $this->notes,
                'tags' => $this->tags,
                'currency' => $this->currency,
            ]
        );

        // Optional: Ensure a user record exists so they can log in
        if ($this->email) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $this->email],
                [
                    'name' => $this->name,
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    'role' => 'client',
                ]
            );
            
            // Link if not already linked
            if (!$clientRecord->user_id) {
                $clientRecord->update(['user_id' => $user->id]);
            }

            // If a custom password was provided, update it
            if ($this->password) {
                $user->update(['password' => \Illuminate\Support\Facades\Hash::make($this->password)]);
            }
        }

        session()->flash('message', $this->clientId ? 'Client updated successfully.' : 'Client created successfully.');

        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.client-form')->layout('layouts.app');
    }
}
