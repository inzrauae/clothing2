<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /** Read a store setting (Admin › Settings), falling back to config defaults. */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('setting_all')) {
    /** All store settings as key => value. */
    function setting_all(): array
    {
        return Setting::all_values();
    }
}

if (! function_exists('money')) {
    /** Format an amount in LKR, e.g. 1840 → "Rs. 1,840". */
    function money(?int $amount): string
    {
        return $amount === null ? '—' : config('store.currency', 'Rs.').' '.number_format($amount);
    }
}

if (! function_exists('wa_link')) {
    /** WhatsApp click-to-chat link to the store number (or another number). */
    function wa_link(?string $text = null, ?string $number = null): string
    {
        $number = preg_replace('/\D/', '', $number ?? (string) setting('whatsapp'));

        return 'https://wa.me/'.$number.($text ? '?text='.rawurlencode($text) : '');
    }
}

if (! function_exists('wa_number')) {
    /** Convert a local Sri Lankan number (07X…) to international format for WhatsApp. */
    function wa_number(?string $phone): string
    {
        $d = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($d, '0')) {
            $d = '94'.substr($d, 1);
        }

        return $d;
    }
}
