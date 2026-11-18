<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Never touch the real lock file from tests
        config(['store.install_lock' => sys_get_temp_dir().'/dooo-test-'.uniqid().'.lock']);
        $this->seed(CatalogSeeder::class);
        User::create(['name' => 'Seed Owner', 'email' => 'seed@test.lk', 'password' => 'password', 'role' => 'owner']);
    }

    protected function tearDown(): void
    {
        @unlink(config('store.install_lock'));
        parent::tearDown();
    }

    public function test_fresh_site_sends_visitors_to_the_installer(): void
    {
        User::query()->delete();
        $this->get('/')->assertRedirect(route('install'));
        $this->get('/admin/login')->assertRedirect(route('install'));
        $this->get('/install')->assertOk()->assertSee('Store installer');
    }

    private function orderPayload(array $items, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nadeesha Perera', 'phone' => '077 123 4567', 'address' => '12 Temple Road',
            'city' => 'Maharagama', 'district' => 'Colombo', 'items' => $items,
        ], $overrides);
    }

    public function test_every_storefront_page_loads(): void
    {
        foreach (['/', '/shop', '/product?id=02', '/cart', '/checkout', '/wishlist', '/about', '/shipping', '/contact'] as $url) {
            $this->get($url)->assertOk()->assertSee('window.DOOO', false);
        }
        $this->get('/shop.html?cat=Linen')->assertRedirect(url('shop').'?cat=Linen');
    }

    public function test_catalogue_is_served_from_the_database(): void
    {
        Product::where('design_no', '02')->update(['name' => 'Renamed Trouser']);
        Product::where('design_no', '03')->update(['is_active' => false]);

        $html = $this->get('/shop')->assertOk()->getContent();
        $this->assertStringContainsString('Renamed Trouser', $html);
        $this->assertStringNotContainsString('The Executive High Waist', $html);
    }

    public function test_order_is_priced_on_the_server_not_the_browser(): void
    {
        $res = $this->postJson('/orders', $this->orderPayload([
            ['id' => '02', 'size' => '32', 'colour' => 'Navy', 'qty' => 2, 'price' => 1],   // forged price is ignored
            ['id' => '04', 'size' => '40', 'colour' => 'Black', 'qty' => 1],                // large size price
            ['id' => '35', 'size' => 'L', 'qty' => 1],                                      // price on request
        ]))->assertCreated()->assertJsonStructure(['ref', 'whatsapp_url', 'total']);

        $order = Order::with('items')->where('ref', $res->json('ref'))->firstOrFail();
        $this->assertSame(1840 * 2 + 2040, $order->subtotal);
        $this->assertSame(1, $order->items_tbc);
        $this->assertSame('pending', $order->status);
        $this->assertSame('0771234567', $order->phone);
        $this->assertSame(1, Customer::count());
        $this->assertStringStartsWith('https://wa.me/94761400696?text=', $res->json('whatsapp_url'));
    }

    public function test_order_validation_rejects_bad_input(): void
    {
        $this->postJson('/orders', $this->orderPayload([['id' => '02', 'size' => '32', 'colour' => 'Navy', 'qty' => 1]], ['phone' => '12345']))
            ->assertStatus(422)->assertJsonValidationErrors('phone');
        $this->postJson('/orders', $this->orderPayload([['id' => '02', 'size' => 'XL', 'colour' => 'Navy', 'qty' => 1]]))
            ->assertStatus(422);
        $this->postJson('/orders', $this->orderPayload([['id' => '999', 'size' => 'M', 'qty' => 1]]))
            ->assertStatus(422);
        $this->postJson('/orders', $this->orderPayload([], ['district' => 'Atlantis']))
            ->assertStatus(422)->assertJsonValidationErrors(['district', 'items']);
        $this->assertSame(0, Order::count());
    }

    public function test_contact_message_is_saved(): void
    {
        $this->postJson('/contact', ['name' => 'Ruwani', 'phone' => '0711234567', 'topic' => 'Wholesale', 'message' => 'Wholesale prices please'])
            ->assertCreated()->assertJsonPath('ok', true);
        $this->assertSame(1, Message::where('is_read', false)->count());
    }

    public function test_admin_requires_login_and_staff_cannot_open_settings(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/orders')->assertRedirect(route('admin.login'));

        $staff = User::create(['name' => 'Staff', 'email' => 'staff@test.lk', 'password' => 'password', 'role' => 'staff']);
        $this->actingAs($staff)->get('/admin/settings')->assertForbidden();
        $this->actingAs($staff)->get('/admin/orders')->assertOk();
    }

    public function test_every_admin_page_renders_for_an_owner(): void
    {
        $owner = User::create(['name' => 'Owner', 'email' => 'owner@test.lk', 'password' => 'password', 'role' => 'owner']);
        $this->postJson('/orders', $this->orderPayload([['id' => '02', 'size' => '32', 'colour' => 'Navy', 'qty' => 1]]));
        $this->postJson('/contact', ['name' => 'Amaya', 'message' => 'Hello there'])->assertCreated();
        $order = Order::first();
        $this->actingAs($owner);

        foreach (['/admin', '/admin/orders', '/admin/orders/'.$order->id, '/admin/orders/'.$order->id.'/invoice',
            '/admin/products', '/admin/products/create', '/admin/products/'.Product::first()->id.'/edit', '/admin/categories',
            '/admin/customers', '/admin/customers/'.Customer::first()->id, '/admin/messages', '/admin/messages/'.Message::first()->id,
            '/admin/settings', '/admin/users', '/admin/profile'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/orders/export')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_price_items_and_update_an_order(): void
    {
        $owner = User::create(['name' => 'Owner', 'email' => 'owner@test.lk', 'password' => 'password', 'role' => 'owner']);
        $ref = $this->postJson('/orders', $this->orderPayload([['id' => '35', 'size' => 'L', 'qty' => 2]]))->json('ref');
        $order = Order::with('items')->where('ref', $ref)->first();
        $item = $order->items->first();

        $this->actingAs($owner)->put(route('admin.orders.items', $order), ['items' => [$item->id => ['unit_price' => 3290, 'qty' => 2]]])->assertRedirect();
        $this->actingAs($owner)->put(route('admin.orders.update', $order), ['status' => 'confirmed', 'payment_status' => 'unpaid', 'delivery_fee' => 400, 'discount' => 100])->assertRedirect();

        $order->refresh();
        $this->assertSame(0, $order->items_tbc);
        $this->assertSame(6580, $order->subtotal);
        $this->assertSame(6580 + 400 - 100, $order->total);
        $this->assertSame('confirmed', $order->status);
    }

    public function test_installer_is_locked_once_an_admin_exists(): void
    {
        // setUp() already created an owner, so the installer must refuse to run
        $this->get('/install')->assertRedirect(route('admin.login'));
        $this->post('/install', ['name' => 'X', 'email' => 'x@x.lk', 'password' => 'password1', 'password_confirmation' => 'password1'])
            ->assertRedirect(route('admin.login'));
        $this->assertSame(1, User::count());
    }
}
