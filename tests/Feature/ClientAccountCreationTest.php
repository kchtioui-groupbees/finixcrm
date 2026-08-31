<?php

namespace Tests\Feature;

use App\Livewire\Clients\ClientForm;
use App\Models\Client;
use App\Models\User;
use App\Services\FinixEmailGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ClientAccountCreationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    public function test_creating_a_client_creates_a_portal_account(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientForm::class)
            ->set('name', 'Ahmed Chiheb')
            ->set('phone', '92871752')
            ->call('save');

        $client = Client::where('name', 'Ahmed Chiheb')->first();
        $this->assertNotNull($client);
        $this->assertNotNull($client->user_id);
    }

    public function test_initial_password_is_hashed_and_never_stored_in_plain_text(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientForm::class)
            ->set('name', 'Sara Ben Ali')
            ->call('save');

        $user = Client::where('name', 'Sara Ben Ali')->first()->user;

        $this->assertNotSame(config('finix.default_client_password'), $user->password);
        $this->assertTrue(Hash::check(config('finix.default_client_password'), $user->password));
        // bcrypt/argon hashes never contain the raw password as a substring.
        $this->assertStringNotContainsString('Finix@Tn', $user->password);
    }

    public function test_new_client_must_change_password_on_first_login(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientForm::class)
            ->set('name', 'Karim Trabelsi')
            ->call('save');

        $user = Client::where('name', 'Karim Trabelsi')->first()->user;

        $this->assertTrue($user->must_change_password);
    }

    public function test_forced_password_change_flow_clears_the_flag_and_lets_client_in(): void
    {
        $admin = $this->admin();
        Livewire::actingAs($admin)->test(ClientForm::class)->set('name', 'Amine Zayani')->call('save');
        $user = Client::where('name', 'Amine Zayani')->first()->user;

        $response = $this->actingAs($user)->post('/force-password-change', [
            'password' => 'A-Brand-New-Secret1',
            'password_confirmation' => 'A-Brand-New-Secret1',
        ]);

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('A-Brand-New-Secret1', $user->password));
        $response->assertRedirect(route('client.dashboard'));
    }

    public function test_client_with_pending_password_change_is_redirected_away_from_other_pages(): void
    {
        $admin = $this->admin();
        Livewire::actingAs($admin)->test(ClientForm::class)->set('name', 'Nour Gharbi')->call('save');
        $user = Client::where('name', 'Nour Gharbi')->first()->user;

        $response = $this->actingAs($user)->get('/portal');

        $response->assertRedirect(route('password.force-change'));
    }

    public function test_admin_can_generate_a_new_temporary_password(): void
    {
        $admin = $this->admin();
        Livewire::actingAs($admin)->test(ClientForm::class)->set('name', 'Yassine Belhaj')->call('save');
        $client = Client::where('name', 'Yassine Belhaj')->first();
        $client->user->update(['must_change_password' => false]);

        Livewire::actingAs($admin)
            ->test(ClientForm::class, ['client' => $client])
            ->call('generateTemporaryPassword');

        $client->user->refresh();
        $this->assertTrue($client->user->must_change_password);
        $this->assertTrue(Hash::check(config('finix.default_client_password'), $client->user->password));
    }

    public function test_finix_email_is_auto_generated_from_name(): void
    {
        $email = app(FinixEmailGeneratorService::class)->generate('Ahmed Chiheb');

        $this->assertSame('achiheb@finix.tn', $email);
    }

    public function test_finix_email_generation_handles_duplicates(): void
    {
        Client::create(['name' => 'Ahmed Chiheb', 'finix_email' => 'achiheb@finix.tn']);
        Client::create(['name' => 'Amel Chiheb', 'finix_email' => 'achiheb2@finix.tn']);

        $email = app(FinixEmailGeneratorService::class)->generate('Amine Chiheb');

        $this->assertSame('achiheb3@finix.tn', $email);
    }

    public function test_admin_can_override_the_generated_finix_email(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientForm::class)
            ->set('name', 'Test Client')
            ->set('finix_email', 'custom@finix.tn')
            ->call('save');

        $client = Client::where('name', 'Test Client')->first();
        $this->assertSame('custom@finix.tn', $client->finix_email);
    }

    public function test_real_email_and_finix_email_are_stored_separately(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientForm::class)
            ->set('name', 'Dual Email Client')
            ->set('email', 'client-real@gmail.com')
            ->call('save');

        $client = Client::where('name', 'Dual Email Client')->first();

        $this->assertSame('client-real@gmail.com', $client->email);
        $this->assertNotSame($client->email, $client->finix_email);
        $this->assertStringEndsWith('@finix.tn', $client->finix_email);
    }
}
