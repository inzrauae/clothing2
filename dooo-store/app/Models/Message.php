<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['name', 'phone', 'topic', 'order_ref', 'message', 'is_read', 'ip'];

    protected $casts = ['is_read' => 'boolean'];
}
