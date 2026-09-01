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

    public function test_defaults_are_auto_apply_enabled_with_every_credit_type_allowed(): void
    {
        $service = app(FinixBalanceAutoApplyService::class);

        $this->assertTrue($service->isEnabled());
        $this->assertTrue($service->appliesToOldUnpaidOrders());
        // Every credit source the ledger can hold — 'overpayment' included.
        $this->assertEqualsCanonicalizing(
            ['cashback_reward', 'refund', 'manual_adjustment', 'overpayment'],
            $service->allowedTypes()
        );
    }

    /**
     * The settings screen can only ever narrow the list, never widen it:
     * it renders one checkbox per AUTO_APPLIABLE_TYPES entry, so that constant
     * and the default allowed set must stay the same four types.
     */
    public function test_the_offered_credit_types_are_exactly_the_default_allowed_types(): void
    {
        $this->assertEqualsCanonicalizing(
            FinixBalanceAutoApplyService::AUTO_APPLIABLE_TYPES,
            app(FinixBalanceAutoApplyService::class)->allowedTypes()
        );
    }

    /**
     * Pending cashback is not a credit type — it has no ledger row until its
     * order is fully paid — so it must never be offerable as one.
     */
    public function test_pending_cashback_is_not_an_offerable_credit_type(): void
    {
        $this->assertNotContains('cashback_pending', FinixBalanceAutoApplyService::AUTO_APPLIABLE_TYPES);
        $this->assertNotContains('cashback_pending', app(FinixBalanceAutoApplyService::class)->allowedTypes());

        $component = Livewire::actingAs($this->admin())->test(FinixBalanceSettings::class);

        $this->assertArrayNotHasKey('cashback_pending', $component->get('allowedTypes'));
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

    public function test_admin_can_narrow_the_allowed_credit_types(): void
    {
        $admin = $this->admin();

        // Everything starts ticked, so the only change an admin can make is
        // to untick — here dropping overpayment and manual adjustment.
        Livewire::actingAs($admin)
            ->test(FinixBalanceSettings::class)
            ->assertSet('allowedTypes.overpayment', true)
            ->set('allowedTypes.overpayment', false)
            ->set('allowedTypes.manual_adjustment', false)
            ->call('save');

        $types = app(FinixBalanceAutoApplyService::class)->allowedTypes();

        $this->assertNotContains('overpayment', $types);
        $this->assertNotContains('manual_adjustment', $types);
        $this->assertContains('cashback_reward', $types);
        $this->assertContains('refund', $types);
    }

    public function test_a_narrowed_selection_is_reflected_back_when_the_page_is_reopened(): void
    {
        Setting::set('finix_balance.auto_apply_allowed_types', ['cashback_reward']);

        Livewire::actingAs($this->admin())
            ->test(FinixBalanceSettings::class)
            ->assertSet('allowedTypes.cashback_reward', true)
            ->assertSet('allowedTypes.overpayment', false)
            ->assertSet('allowedTypes.refund', false)
            ->assertSet('allowedTypes.manual_adjustment', false);
    }

    public function test_settings_page_requires_admin(): void
    {
        $response = $this->get(route('settings.finix-balance'));

        $response->assertRedirect(route('login'));
    }
}
