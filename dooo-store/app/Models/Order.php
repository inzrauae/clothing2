<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'ref', 'customer_id', 'name', 'phone', 'alt_phone', 'address', 'city', 'district', 'notes',
        'subtotal', 'delivery_fee', 'discount', 'total', 'items_tbc',
        'status', 'payment_status', 'payment_method', 'tracking_no', 'admin_notes', 'source', 'ip',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'delivery_fee' => 'integer',
        'discount' => 'integer',
        'total' => 'integer',
        'items_tbc' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeRevenue(Builder $q): Builder
    {
        return $q->whereIn('status', config('store.revenue_statuses'));
    }

    public static function newRef(): string
    {
        do {
            $ref = 'DO'.strtoupper(Str::random(6));
            $ref = preg_replace('/[^A-Z0-9]/', 'X', $ref);
        } while (static::where('ref', $ref)->exists());

        return $ref;
    }

    /** Recalculate subtotal / total from the order lines. */
    public function recalculate(): void
    {
        $this->loadMissing('items');
        $sub = 0;
        $tbc = 0;
        foreach ($this->items as $item) {
            if ($item->unit_price === null) {
                $tbc += $item->qty;
            } else {
                $sub += $item->line_total;
            }
        }
        $this->subtotal = $sub;
        $this->items_tbc = $tbc;
        $this->total = max(0, $sub + (int) $this->delivery_fee - (int) $this->discount);
    }

    public function getStatusLabelAttribute(): string
    {
        return config('store.order_statuses.'.$this->status, ucfirst($this->status));
    }

    public function getPaymentLabelAttribute(): string
    {
        return config('store.payment_statuses.'.$this->payment_status, ucfirst($this->payment_status));
    }

    public function getItemsCountAttribute(): int
    {
        return (int) $this->items->sum('qty');
    }

    /** Plain-text summary used for WhatsApp. */
    public function whatsappText(): string
    {
        $this->loadMissing('items');
        $lines = ["New order — ".setting('store_name', 'dooo')." (Ref {$this->ref})", ''];
        foreach ($this->items as $i => $item) {
            $lines[] = ($i + 1).". Design No {$item->design_no} — {$item->name}";
            $lines[] = "   Size {$item->size}".($item->colour ? " · {$item->colour}" : '')." · Qty {$item->qty} · "
                .($item->unit_price === null ? 'price to confirm' : money($item->line_total));
        }
        $lines[] = '';
        $lines[] = 'Subtotal: '.money($this->subtotal).($this->items_tbc ? " + {$this->items_tbc} item(s) priced on confirmation" : '');
        $lines[] = 'Delivery: islandwide (charge confirmed by dooo)';
        $lines[] = "Payment: Cash on delivery — I'll open & inspect before paying";
        $lines[] = '';
        $lines[] = "Name: {$this->name}";
        $lines[] = "Phone: {$this->phone}";
        if ($this->alt_phone) {
            $lines[] = "Alt. phone: {$this->alt_phone}";
        }
        $lines[] = "Address: {$this->address}";
        $lines[] = "City: {$this->city}";
        $lines[] = "District: {$this->district}";
        if ($this->notes) {
            $lines[] = "Notes: {$this->notes}";
        }

        return implode("\n", $lines);
    }
}
