<?php

return [

    // Redirect all http:// traffic to https:// — turn on once SSL (AutoSSL) is active.
    'force_https' => env('FORCE_HTTPS', false),

    // Admin login lockout: after this many wrong passwords for one email (from one IP)…
    'login_max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),

    // …that email is locked for this many minutes.
    'login_lockout_minutes' => (int) env('LOGIN_LOCKOUT_MINUTES', 15),

    // Upload limits for product photos (kilobytes / pixels)
    'upload_max_kb' => 5120,
    'upload_max_pixels' => 6000,
];
