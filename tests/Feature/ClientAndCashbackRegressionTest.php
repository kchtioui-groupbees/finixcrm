<?php

namespace Tests\Feature;

use App\Livewire\Clients\ClientForm;
use App\Livewire\Orders\OrderForm;
use App\Livewire\Products\ProductForm;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Two HTTP 500s reported on test.finixtools.com, reproduced here first:
 *
 *  1. Creating a second client with no real email address.
 *     clients.email is UNIQUE and nullable, but the form saved '' rather
 *     than null, so the second empty one collided:
 *       SQLSTATE[23000] 1062 Duplicate entry '' for key 'clients_email_unique'
 *
 *  2. Editing the cashback percentage on the order form.
 *     Order casts cashback_value_snapshot to decimal:3, and the live
 *     estimated-cashback preview assigns the raw input to it on every
 *     keystroke. Clearing the field (or typing anything non-numeric) sent
 *     '' into the cast and BrickMath threw:
 *       Unable to cast value to a decimal.
 *     Validation could not save anyone from it — the preview renders long
 *     before save() ever runs.
 */
class ClientAndCashbackRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    private function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'ChatGPT Plus',
            'slug' => 'cgpt-' . uniqid(),
            'is_active' => true,
            // Explicit: these columns are nullable with no DB default, and
            // ProductForm validates them as booleans when the row is edited.
            'warranty_enabled' => false,
            'renewable' => false,
        ], $overrides));
    }

    // ── 1. Creating clients with no real email ──────────────────────────

    public function test_two_clients_can_be_created_without_a_real_email(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(ClientForm::class)
            ->set('name', 'Ahmed Chiheb')->call('save');

        // This is the one that used to blow up with a 1062 on the UNIQUE
        // index, because the first client had already taken email = ''.
        Livewire::actingAs($admin)->test(ClientForm::class)
            ->set('name', 'Sara Ben Ali')->call('save');

        $this->assertSame(2, Client::count());

        // Absent means NULL, never '': the unique index tolerates many nulls
        // and exactly one empty string.
        $this->assertNull(Client::where('name', 'Ahmed Chiheb')->first()->email);
        $this->assertNull(Client::where('name', 'Sara Ben Ali')->first()->email);
        $this->assertSame(0, Client::where('email', '')->count());
    }

    /**
     * The reported 500 needed only two email-less clients to appear. This
     * pushes well past that, because MySQL tolerates exactly one '' in a
     * unique index but unlimited NULLs — if anything ever regresses to
     * saving '', client number two dies here.
     */
    public function test_many_clients_can_be_created_with_no_email_at_all(): void
    {
        $admin = $this->admin();

        $names = [
            'Ahmed Chiheb', 'Sara Ben Ali', 'Youssef Mansour',
            'Ines Trabelsi', 'Karim Jelassi', 'Leila Bouazizi',
        ];

        foreach ($names as $name) {
            Livewire::actingAs($admin)->test(ClientForm::class)
                ->set('name', $name)
                ->call('save')
                ->assertHasNoErrors();
        }

        $this->assertSame(count($names), Client::count());
        $this->assertSame(count($names), Client::whereNull('email')->count());
        $this->assertSame(0, Client::where('email', '')->count());

        // Every one still got its own distinct portal login.
        $this->assertSame(count($names), Client::whereNotNull('user_id')->count());
        $this->assertSame(count($names), Client::distinct()->count('finix_email'));
    }

    /**
     * Mirrors the live data fix: a legacy row holding '' must not block the
     * next email-less client, and must not be silently rewritten either.
     */
    public function test_a_legacy_empty_string_email_does_not_block_new_clients(): void
    {
        $legacy = Client::create([
            'name' => 'Aymen Lourimi',
            'email' => '',
            'finix_email' => 'alourimi@finix.tn',
            'currency' => 'TND',
            'credit_balance' => 0,
        ]);

        Livewire::actingAs($this->admin())->test(ClientForm::class)
            ->set('name', 'Ahmed Chiheb')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(Client::where('name', 'Ahmed Chiheb')->first()->email);
        // The legacy row is left exactly as it was by the form flow itself.
        $this->assertSame('alourimi@finix.tn', $legacy->fresh()->finix_email);
    }

    public function test_a_whitespace_only_email_is_stored_as_null_too(): void
    {
        Livewire::actingAs($this->admin())->test(ClientForm::class)
            ->set('name', 'Mohamed Trabelsi')
            ->set('email', '   ')
            ->call('save');

        $this->assertNull(Client::where('name', 'Mohamed Trabelsi')->first()->email);
    }

    public function test_a_real_email_is_validated_and_kept(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(ClientForm::class)
            ->set('name', 'Nadia Gharbi')
            ->set('email', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['email' => 'email']);

        Livewire::actingAs($admin)->test(ClientForm::class)
            ->set('name', 'Nadia Gharbi')
            ->set('email', 'nadia@example.com')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('nadia@example.com', Client::where('name', 'Nadia Gharbi')->first()->email);
    }

    public function test_a_duplicate_real_email_is_a_validation_error_not_a_500(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(ClientForm::class)
            ->set('name', 'First Person')->set('email', 'shared@example.com')->call('save');

        Livewire::actingAs($admin)->test(ClientForm::class)
            ->set('name', 'Second Person')->set('email', 'shared@example.com')->call('save')
            ->assertHasErrors(['email' => 'unique']);

        $this->assertSame(1, Client::where('email', 'shared@example.com')->count());
    }

    // ── Generated system email ──────────────────────────────────────────

    /** @return list<array{0:string,1:string}> */
    public static function nameToGeneratedEmail(): array
    {
        return [
            'first + last name'   => ['Ahmed Chiheb', 'achiheb@finix.tn'],
            'single name'         => ['Madonna', 'madonna@finix.tn'],
            'accents'             => ['Émilie Benoît', 'ebenoit@finix.tn'],
            'special characters'  => ["Jean-Luc  O'Brien", 'jobrien@finix.tn'],
            'extra whitespace'    => ['   Sami   Khelifi  ', 'skhelifi@finix.tn'],
            'uppercase'           => ['YOUSSEF MANSOUR', 'ymansour@finix.tn'],
            'three names'         => ['Mohamed Ali Ben Salah', 'msalah@finix.tn'],
        ];
    }

    #[DataProvider('nameToGeneratedEmail')]
    public function test_the_system_email_is_generated_from_the_name(string $name, string $expected): void
    {
        Livewire::actingAs($this->admin())->test(ClientForm::class)
            ->set('name', $name)->call('save');

        $this->assertSame($expected, Client::where('name', trim(preg_replace('/\s+/', ' ', $name)))->first()?->finix_email
            ?? Client::latest('id')->first()->finix_email);
    }

    public function test_generated_emails_are_deduplicated_with_a_numeric_suffix(): void
    {
        $admin = $this->admin();

        foreach (['Ahmed Chiheb', 'Ali Chiheb', 'Amira Chiheb'] as $name) {
            Livewire::actingAs($admin)->test(ClientForm::class)
                ->set('name', $name)->call('save')->assertHasNoErrors();
        }

        $this->assertSame(
            ['achiheb@finix.tn', 'achiheb2@finix.tn', 'achiheb3@finix.tn'],
            Client::orderBy('id')->pluck('finix_email')->all()
        );
    }

    public function test_a_generated_email_never_collides_with_an_existing_portal_login(): void
    {
        // A user row already holds the address the generator would pick —
        // creating the client must not fail on users.email's unique index.
        User::factory()->create(['email' => 'achiheb@finix.tn', 'role' => User::ROLE_CLIENT]);

        Livewire::actingAs($this->admin())->test(ClientForm::class)
            ->set('name', 'Ahmed Chiheb')->call('save')->assertHasNoErrors();

        $client = Client::where('name', 'Ahmed Chiheb')->first();

        $this->assertNotNull($client->user_id);
        $this->assertNotSame('achiheb@finix.tn', $client->finix_email);
    }

    public function test_the_temporary_password_is_hashed_and_must_be_changed(): void
    {
        Livewire::actingAs($this->admin())->test(ClientForm::class)
            ->set('name', 'Ahmed Chiheb')->call('save');

        $user = Client::where('name', 'Ahmed Chiheb')->first()->user;

        $this->assertNotSame('Finix@Tn', $user->password);
        $this->assertTrue(Hash::check('Finix@Tn', $user->password));
        $this->assertTrue((bool) $user->must_change_password);
    }

    public function test_the_admin_is_told_a_system_email_was_generated(): void
    {
        Livewire::actingAs($this->admin())->test(ClientForm::class)
            ->set('name', 'Ahmed Chiheb')->call('save');

        $this->assertSame(
            __('No real email address was provided. A system email has been generated for this client.'),
            session('message')
        );
    }

    public function test_providing_a_real_email_does_not_show_the_generated_email_notice(): void
    {
        Livewire::actingAs($this->admin())->test(ClientForm::class)
            ->set('name', 'Nadia Gharbi')->set('email', 'nadia@example.com')->call('save');

        $this->assertSame(__('Client created successfully.'), session('message'));
    }

    // ── 2. Cashback percentage ──────────────────────────────────────────

    /** @return list<array{0:mixed}> */
    public static function nonNumericCashbackInput(): array
    {
        return [
            'empty string'   => [''],
            'letters'        => ['abc'],
            'lone minus'     => ['-'],
            'lone dot'       => ['.'],
            'comma decimal'  => ['10,5'],
            'null'           => [null],
        ];
    }

    /**
     * Every one of these used to render a 500 the moment it was typed,
     * because the live preview cast the raw input to decimal:3.
     */
    #[DataProvider('nonNumericCashbackInput')]
    public function test_a_non_numeric_cashback_value_never_crashes_the_order_form(mixed $input): void
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $product = $this->product();

        Livewire::actingAs($this->admin())->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 100)
            ->set('cashback_enabled', true)
            ->set('cashback_type', 'percentage')
            ->set('cashback_value', $input)
            ->assertOk();
    }

    /** @return list<array{0:mixed,1:float}> */
    public static function validCashbackPercentages(): array
    {
        return [
            'zero'          => [0, 0.0],
            'one'           => [1, 1.0],
            'one decimal'   => [10.5, 10.5],
            'three decimal' => [2.125, 2.125],
            'hundred'       => [100, 100.0],
            'numeric string'=> ['7.5', 7.5],
        ];
    }

    #[DataProvider('validCashbackPercentages')]
    public function test_a_valid_cashback_percentage_is_accepted(mixed $input, float $expected): void
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $product = $this->product();

        Livewire::actingAs($this->admin())->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 100)
            ->set('purchase_date', '2026-09-01')
            ->set('expiry_date', '2027-09-01')
            ->set('cashback_enabled', true)
            ->set('cashback_type', 'percentage')
            ->set('cashback_value', $input)
            ->call('save')
            ->assertHasNoErrors(['cashback_value']);

        $order = Order::latest('id')->first();

        $this->assertSame($expected, (float) $order->cashback_value_snapshot);
        // TND keeps 3 decimals: 10.5% of 100 is 10.500, never 10.50.
        $this->assertSame(round(100 * $expected / 100, 3), (float) $order->cashback_amount);
    }

    /** @return list<array{0:mixed}> */
    public static function invalidCashbackPercentages(): array
    {
        return [
            'negative'      => [-1],
            'over hundred'  => [101],
            'far over'      => [1000],
            'text'          => ['abc'],
        ];
    }

    #[DataProvider('invalidCashbackPercentages')]
    public function test_an_out_of_range_cashback_percentage_is_a_validation_error(mixed $input): void
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $product = $this->product();

        Livewire::actingAs($this->admin())->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 100)
            ->set('purchase_date', '2026-09-01')
            ->set('expiry_date', '2027-09-01')
            ->set('cashback_enabled', true)
            ->set('cashback_type', 'percentage')
            ->set('cashback_value', $input)
            ->call('save')
            ->assertHasErrors(['cashback_value']);

        $this->assertSame(0, Order::count());
    }

    public function test_a_fixed_cashback_may_exceed_one_hundred_but_never_the_order_price(): void
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $product = $this->product();

        // 100 is a percentage ceiling, not a money ceiling — a fixed reward
        // of 150 on a 500 order is perfectly legitimate.
        Livewire::actingAs($this->admin())->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 500)
            ->set('purchase_date', '2026-09-01')
            ->set('expiry_date', '2027-09-01')
            ->set('cashback_enabled', true)
            ->set('cashback_type', 'fixed')
            ->set('cashback_value', 150)
            ->call('save')
            ->assertHasNoErrors(['cashback_value']);

        $this->assertSame(150.0, (float) Order::latest('id')->first()->cashback_amount);
    }

    public function test_an_empty_cashback_value_falls_back_to_zero_rather_than_erroring(): void
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $product = $this->product();

        Livewire::actingAs($this->admin())->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 100)
            ->set('purchase_date', '2026-09-01')
            ->set('expiry_date', '2027-09-01')
            ->set('cashback_enabled', false)
            ->set('cashback_value', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(0.0, (float) Order::latest('id')->first()->cashback_amount);
    }

    // ── The percentage applies forward only ─────────────────────────────

    public function test_changing_a_products_percentage_leaves_existing_orders_untouched(): void
    {
        $admin = $this->admin();
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);

        $product = $this->product([
            'cashback_enabled' => true,
            'cashback_type' => 'percentage',
            'cashback_value' => 5,
        ]);

        $existing = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 100,
            'purchase_date' => '2026-08-01', 'expiry_date' => '2027-08-01',
            'status' => 'active', 'currency' => 'TND',
            'cashback_enabled_snapshot' => true, 'cashback_type_snapshot' => 'percentage',
            'cashback_value_snapshot' => 5, 'cashback_amount' => 5.000,
        ]);

        Livewire::actingAs($admin)->test(ProductForm::class, ['product' => $product])
            ->set('cashback_value', 20)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(20.0, (float) $product->fresh()->cashback_value);

        // The old order keeps the rate frozen onto it at purchase time.
        $existing->refresh();
        $this->assertSame(5.0, (float) $existing->cashback_value_snapshot);
        $this->assertSame(5.0, (float) $existing->cashback_amount);
    }

    public function test_the_new_percentage_applies_to_the_next_order(): void
    {
        $admin = $this->admin();
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);

        $product = $this->product([
            'cashback_enabled' => true, 'cashback_type' => 'percentage', 'cashback_value' => 5,
        ]);

        Livewire::actingAs($admin)->test(ProductForm::class, ['product' => $product])
            ->set('cashback_value', 20)->call('save');

        Livewire::actingAs($admin)->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 100)
            ->set('purchase_date', '2026-09-01')
            ->set('expiry_date', '2027-09-01')
            ->call('save')
            ->assertHasNoErrors();

        $new = Order::latest('id')->first();

        $this->assertSame(20.0, (float) $new->cashback_value_snapshot);
        $this->assertSame(20.0, (float) $new->cashback_amount);
    }

    /** @return list<array{0:mixed}> */
    public static function invalidProductCashback(): array
    {
        return [['-5'], ['101'], ['abc']];
    }

    #[DataProvider('invalidProductCashback')]
    public function test_the_product_form_rejects_an_out_of_range_percentage(mixed $input): void
    {
        $product = $this->product([
            'cashback_enabled' => true, 'cashback_type' => 'percentage', 'cashback_value' => 5,
        ]);

        Livewire::actingAs($this->admin())->test(ProductForm::class, ['product' => $product])
            ->set('cashback_value', $input)
            ->call('save')
            ->assertHasErrors(['cashback_value']);

        $this->assertSame(5.0, (float) $product->fresh()->cashback_value);
    }

    public function test_a_non_numeric_percentage_never_crashes_the_product_form(): void
    {
        $product = $this->product([
            'cashback_enabled' => true, 'cashback_type' => 'percentage', 'cashback_value' => 5,
        ]);

        Livewire::actingAs($this->admin())->test(ProductForm::class, ['product' => $product])
            ->set('cashback_value', '')
            ->assertOk()
            ->set('cashback_value', 'abc')
            ->assertOk();
    }
}
