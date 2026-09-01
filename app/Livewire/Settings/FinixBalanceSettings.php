<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Services\FinixBalanceAutoApplyService;
use Livewire\Component;

class FinixBalanceSettings extends Component
{
    public bool $autoApplyEnabled = true;
    public bool $applyToOldUnpaidOrders = true;

    /**
     * Mirrors the service's default allowed set, so a fresh install with no
     * saved setting yet shows exactly what the service is already doing.
     *
     * @var array<string,bool>
     */
    public array $allowedTypes = [
        'cashback_reward' => true,
        'refund' => true,
        'manual_adjustment' => true,
        'overpayment' => true,
    ];

    public function mount(FinixBalanceAutoApplyService $service)
    {
        $this->autoApplyEnabled = $service->isEnabled();
        $this->applyToOldUnpaidOrders = $service->appliesToOldUnpaidOrders();

        $enabledTypes = $service->allowedTypes();
        foreach (array_keys($this->allowedTypes) as $type) {
            $this->allowedTypes[$type] = in_array($type, $enabledTypes, true);
        }
    }

    public function save()
    {
        Setting::set('finix_balance.auto_apply_enabled', $this->autoApplyEnabled);
        Setting::set('finix_balance.auto_apply_to_old_orders', $this->applyToOldUnpaidOrders);
        Setting::set(
            'finix_balance.auto_apply_allowed_types',
            array_keys(array_filter($this->allowedTypes))
        );

        session()->flash('message', __('Finix balance settings saved.'));
    }

    public function render()
    {
        return view('livewire.settings.finix-balance-settings')->layout('layouts.app');
    }
}
