<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'store.settings';

    /** All settings merged over the defaults from config/store.php. */
    public static function all_values(): array
    {
        $defaults = config('store.default_settings', []);

        try {
            $stored = Cache::rememberForever(self::CACHE_KEY, function () {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return static::query()->pluck('value', 'key')->all();
            });
        } catch (Throwable) {
            $stored = []; // database not configured yet (e.g. before install)
        }

        return array_merge($defaults, array_filter($stored, fn ($v) => $v !== null));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all_values()[$key] ?? $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
        static::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
