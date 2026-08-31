<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\User;
use App\Services\FinixEmailGeneratorService;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ClientForm extends Component
{
    public $clientId = null;
    public $name = '';
    public $email = '';
    public $finix_email = '';
    public $finixEmailManuallyEdited = false;
    public $phone = '';
    public $notes = '';
    public $tags = []; // e.g., VIP, Late Payer
    public $tagInput = '';
    public $currency = 'USD';
    public $status = 'active';

    public $temporaryPasswordJustGenerated = false;

    public function mount(?Client $client = null)
    {
        if ($client && $client->exists) {
            $this->clientId = $client->id;
            $this->name = $client->name;
            $this->email = $client->email;
            $this->finix_email = $client->finix_email;
            $this->finixEmailManuallyEdited = true; // never auto-regenerate over an existing value
            $this->phone = $client->phone;
            $this->notes = $client->notes;
            $this->tags = is_array($client->tags) ? $client->tags : [];
            $this->currency = $client->currency ?: 'USD';
            $this->status = $client->status ?: 'active';
        }
    }

    public function updatedName($value)
    {
        if (is_null($this->clientId) && !$this->finixEmailManuallyEdited && $value) {
            $this->finix_email = app(FinixEmailGeneratorService::class)->generate($value);
        }
    }

    public function updatedFinixEmail()
    {
        $this->finixEmailManuallyEdited = true;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:clients,email,' . $this->clientId,
            'finix_email' => 'nullable|email|max:255|unique:clients,finix_email,' . $this->clientId,
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'currency' => 'required|in:USD,EUR,TND',
            'status' => 'required|in:active,inactive',
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

        $isNew = is_null($this->clientId);

        if ($isNew && !$this->finix_email) {
            $this->finix_email = app(FinixEmailGeneratorService::class)->generate($this->name);
        }

        $clientRecord = Client::updateOrCreate(
            ['id' => $this->clientId],
            [
                'name' => $this->name,
                'email' => $this->email,
                'finix_email' => $this->finix_email ?: null,
                'phone' => $this->phone,
                'notes' => $this->notes,
                'tags' => $this->tags,
                'currency' => $this->currency,
                'status' => $this->status,
            ]
        );

        // A client account logs in with its Finix system email — create the
        // portal login the first time, with the shared default temporary
        // password, never displayed or logged anywhere.
        if ($isNew && $this->finix_email && !$clientRecord->user_id) {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->finix_email,
                'password' => config('finix.default_client_password'),
                'role' => 'client',
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]);

            $clientRecord->update(['user_id' => $user->id]);
        }

        session()->flash('message', $this->clientId ? __('Client updated successfully.') : __('Client created successfully.'));

        return redirect()->route('clients.show', $clientRecord->id);
    }

    /**
     * Reset an existing client's portal login back to the shared default
     * temporary password and force them to change it on next login.
     */
    public function generateTemporaryPassword()
    {
        if (!$this->clientId) {
            return;
        }

        $client = Client::find($this->clientId);
        if (!$client || !$client->user_id) {
            session()->flash('error', __('This client has no portal account yet.'));
            return;
        }

        $client->user->update([
            'password' => config('finix.default_client_password'),
            'must_change_password' => true,
        ]);

        $this->temporaryPasswordJustGenerated = true;
        session()->flash('message', __('A new temporary password has been set. The client must change it on next login.'));
    }

    public function render()
    {
        return view('livewire.clients.client-form')->layout('layouts.app');
    }
}
