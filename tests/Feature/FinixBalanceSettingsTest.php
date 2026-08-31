<?php

namespace Tests\Feature;

use App\Livewire\Settings\FinixBalanceSettings;
use App\Models\Setting;
use App\Models\User;
use App\Services\FinixBalanceAutoApplyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinixBalanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    public function test_defaults_are_auto_apply_enabled_with_three_allowed_types(): void
    {
        $service = app(FinixBalanceAutoApplyService::class);

        $this->assertTrue($service->isEnabled());
        $this->assertTrue($service->appliesToOldUnpaidOrders());
        $this->assertEqualsCanonicalizing(
            ['cashback_reward', 'refund', 'manual_adjustment'],
            $service->allowedTypes()
        );
    }

    public function test_admin_can_disable_auto_apply(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(FinixBalanceSettings::class)
            ->set('autoApplyEnabled', false)
            ->call('save');

        $this->assertFalse(app(FinixBalanceAutoApplyService::class)->isEnabled());
    }

    public function test_admin_can_change_allowed_credit_types(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(FinixBalanceSettings::class)
            ->set('allowedTypes.overpayment', true)
            ->set('allowedTypes.manual_adjustment', false)
            ->call('save');

        $types = app(FinixBalanceAutoApplyService::class)->allowedTypes();

        $this->assertContains('overpayment', $types);
        $this->assertNotContains('manual_adjustment', $types);
    }

    public function test_settings_page_requires_admin(): void
    {
        $response = $this->get(route('settings.finix-balance'));

        $response->assertRedirect(route('login'));
    }
}
