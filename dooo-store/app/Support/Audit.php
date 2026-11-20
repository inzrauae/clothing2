<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/** Writes security-relevant events to storage/logs/security-*.log */
class Audit
{
    public static function log(string $event, array $context = []): void
    {
        try {
            $request = request();
            $user = $request->user();
            Log::channel('security')->info($event, $context + [
                'by' => $user ? "{$user->email} (#{$user->id})" : 'guest',
                'ip' => $request->ip(),
                'agent' => substr((string) $request->userAgent(), 0, 160),
            ]);
        } catch (Throwable) {
            // Logging must never break the request
        }
    }
}
