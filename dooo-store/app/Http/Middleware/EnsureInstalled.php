<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Until the store has been installed (tables + owner account), send every
 * visitor to the web installer instead of showing a database error.
 */
class EnsureInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        $lock = config('store.install_lock');
        if (File::exists($lock)) {
            return $next($request);
        }

        try {
            $installed = Schema::hasTable('users') && Schema::hasTable('products') && User::query()->exists();
        } catch (Throwable) {
            $installed = false;
        }

        if (! $installed) {
            return redirect()->route('install');
        }

        // Installed from the command line (php artisan migrate --seed): remember it
        File::ensureDirectoryExists(dirname($lock));
        File::put($lock, 'Detected '.now()->toDateTimeString());

        return $next($request);
    }
}
