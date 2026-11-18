<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'alt_phone', 'address', 'city', 'district', 'notes'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Normalise Sri Lankan numbers to 07XXXXXXXX so the same person is matched. */
    public static function normalisePhone(string $phone): string
    {
        $d = preg_replace('/\D/', '', $phone);
        if (str_starts_with($d, '94') && strlen($d) === 11) {
            $d = '0'.substr($d, 2);
        }

        return $d;
    }
}
