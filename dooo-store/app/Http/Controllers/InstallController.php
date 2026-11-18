<?php

namespace App\Http\Controllers;

use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\DemoOrdersSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

/**
 * One-time web installer for shared hosting (cPanel) where SSH may not be available.
 * Creates the database tables, loads the dooo catalogue and the owner account,
 * then locks itself so it can never run again.
 */
class InstallController extends Controller
{
    private function lockFile(): string
    {
        return config('store.install_lock');
    }

    private function installed(): bool
    {
        if (File::exists($this->lockFile())) {
            return true;
        }
        try {
            return Schema::hasTable('users') && User::query()->exists();
        } catch (Throwable) {
            return false;
        }
    }

    public function show(): View|RedirectResponse
    {
        if ($this->installed()) {
            return redirect()->route('admin.login')->with('ok', 'The store is already installed. Please sign in.');
        }

        $db = ['ok' => false, 'message' => ''];
        try {
            DB::connection()->getPdo();
            $db = ['ok' => true, 'message' => 'Connected to database “'.DB::connection()->getDatabaseName().'”'];
        } catch (Throwable $e) {
            $db['message'] = 'Cannot connect to the database. Check DB_HOST, DB_DATABASE, DB_USERNAME and DB_PASSWORD in your .env file. ('.str($e->getMessage())->limit(160).')';
        }

        $checks = [
            ['PHP 8.2 or newer', version_compare(PHP_VERSION, '8.2.0', '>='), 'You have PHP '.PHP_VERSION],
            ['PDO MySQL extension', extension_loaded('pdo_mysql') || extension_loaded('pdo_sqlite'), ''],
            ['Mbstring extension', extension_loaded('mbstring'), ''],
            ['OpenSSL extension', extension_loaded('openssl'), ''],
            ['Fileinfo extension (image uploads)', extension_loaded('fileinfo'), ''],
            ['storage/ is writable', is_writable(storage_path()), storage_path()],
            ['bootstrap/cache/ is writable', is_writable(base_path('bootstrap/cache')), ''],
            ['public/uploads/ is writable', is_writable(public_path('uploads')) || ! File::exists(public_path('uploads')), ''],
            ['APP_KEY is set', (bool) config('app.key'), 'Run the key step in the README if this fails'],
        ];

        return view('install', compact('db', 'checks'));
    }

    public function run(Request $request): RedirectResponse
    {
        if ($this->installed()) {
            return redirect()->route('admin.login');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        try {
            Artisan::call('migrate', ['--force' => true]);
            (new CatalogSeeder())->run();
            if ($request->boolean('demo')) {
                (new DemoOrdersSeeder())->run();
            }
            $user = User::query()->updateOrCreate(['email' => $data['email']], [
                'name' => $data['name'], 'password' => $data['password'], 'role' => 'owner',
            ]);
            File::ensureDirectoryExists(dirname($this->lockFile()));
            File::put($this->lockFile(), 'Installed '.now()->toDateTimeString());
            File::ensureDirectoryExists(public_path('uploads/products'));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['install' => 'Installation failed: '.str($e->getMessage())->limit(300)]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('ok', 'Welcome! Your store is installed and ready.');
    }
}
