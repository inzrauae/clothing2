<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.install_lock' => sys_get_temp_dir().'/dooo-sec-'.uniqid().'.lock']);
        $this->seed(CatalogSeeder::class);
        $this->owner = User::create(['name' => 'Owner', 'email' => 'owner@test.lk', 'password' => 'Secret12345', 'role' => 'owner']);
        RateLimiter::clear('admin-login:owner@test.lk|127.0.0.1');
    }

    protected function tearDown(): void
    {
        @unlink(config('store.install_lock'));
        parent::tearDown();
    }

    public function test_pages_send_security_headers_and_a_matching_script_nonce(): void
    {
        $res = $this->get('/shop')->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = $res->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringNotContainsString("'unsafe-inline' 'self' 'nonce", $csp);
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([^']+)'/", $csp);
        preg_match("/'nonce-([^']+)'/", $csp, $m);
        // every inline script on the page carries this request's nonce
        $html = $res->getContent();
        $this->assertSame(substr_count($html, '<script>'), 0, 'An inline <script> without a nonce would be blocked');
        $this->assertStringContainsString('nonce="'.$m[1].'"', $html);

        $this->actingAs($this->owner)->get('/admin')->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_admin_account_locks_after_repeated_wrong_passwords(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'owner@test.lk', 'password' => 'wrong-pass'])->assertSessionHasErrors('email');
        }
        // even the correct password is refused while locked
        $this->post('/admin/login', ['email' => 'owner@test.lk', 'password' => 'Secret12345'])
            ->assertSessionHasErrors(['email' => 'Too many failed sign-in attempts. Please try again in 15 minutes.']);
        $this->assertGuest();
    }

    public function test_successful_login_works_and_clears_failures(): void
    {
        $this->post('/admin/login', ['email' => 'owner@test.lk', 'password' => 'wrong-pass']);
        $this->post('/admin/login', ['email' => 'owner@test.lk', 'password' => 'Secret12345'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->owner);
        $this->assertSame(0, RateLimiter::attempts('admin-login:owner@test.lk|127.0.0.1'));
    }

    public function test_weak_passwords_are_rejected(): void
    {
        $this->actingAs($this->owner);
        foreach (['short1', 'onlyletterslong', '12345678901'] as $weak) {
            $this->post('/admin/users', ['name' => 'Staff', 'email' => 'staff@test.lk', 'role' => 'staff', 'password' => $weak])
                ->assertSessionHasErrors('password');
        }
        $this->post('/admin/users', ['name' => 'Staff', 'email' => 'staff@test.lk', 'role' => 'staff', 'password' => 'GoodPass2026'])
            ->assertSessionHasNoErrors();
    }

    public function test_honeypot_blocks_bot_orders_and_messages(): void
    {
        $this->postJson('/orders', [
            'name' => 'Bot', 'phone' => '0771234567', 'address' => '1 Bot Street', 'city' => 'Colombo', 'district' => 'Colombo',
            'items' => [['id' => '02', 'size' => '32', 'colour' => 'Navy', 'qty' => 1]], 'website' => 'http://spam.example',
        ])->assertStatus(422);
        $this->postJson('/contact', ['name' => 'Bot', 'message' => 'Buy now', 'website' => 'spam'])->assertCreated();

        $this->assertSame(0, Order::count());
        $this->assertSame(0, Message::count());
    }

    public function test_disguised_script_uploads_are_rejected(): void
    {
        $this->actingAs($this->owner);
        $fake = UploadedFile::fake()->createWithContent('photo.jpg', "<?php echo 'hacked'; ?>");
        $this->post('/admin/products', [
            'design_no' => '90', 'name' => 'Upload test', 'size_type' => 'alpha', 'is_active' => '1', 'image' => $fake,
        ])->assertSessionHasErrors('image');

        $real = UploadedFile::fake()->image('photo.png', 800, 1000);
        $this->post('/admin/products', [
            'design_no' => '91', 'name' => 'Upload ok', 'size_type' => 'alpha', 'is_active' => '1', 'image' => $real,
        ])->assertSessionHasNoErrors();
        $path = \App\Models\Product::where('design_no', '91')->value('image');
        $this->assertMatchesRegularExpression('#^uploads/products/[a-z0-9\-]+\.png$#', $path);
        @unlink(public_path($path));
    }

    public function test_settings_reject_javascript_links(): void
    {
        $this->actingAs($this->owner)->put('/admin/settings', array_merge(setting_all(), [
            'whatsapp' => '94761400696', 'facebook' => 'javascript:alert(1)',
        ]))->assertSessionHasErrors('facebook');
    }

    public function test_errors_show_branded_pages_without_internals(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('Page not found')->assertDontSee('Symfony');
        $this->actingAs(User::create(['name' => 'S', 'email' => 's@test.lk', 'password' => 'Secret12345', 'role' => 'staff']))
            ->get('/admin/users')->assertForbidden()->assertSee('Access denied');
    }
}
