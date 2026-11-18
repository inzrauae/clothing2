<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'design_no', 'name', 'slug', 'description', 'image', 'size_type', 'colours',
        'price', 'price_large', 'compare_price', 'price_from', 'tag',
        'is_signature', 'is_active', 'in_stock', 'sort_order',
    ];

    protected $casts = [
        'colours' => 'array',
        'price' => 'integer',
        'price_large' => 'integer',
        'compare_price' => 'integer',
        'price_from' => 'boolean',
        'is_signature' => 'boolean',
        'is_active' => 'boolean',
        'in_stock' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderByDesc('id');
    }

    /** Public URL of the product image (bundled image or admin upload). */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return str_starts_with($this->image, 'http') ? $this->image : asset($this->image);
    }

    /** Sizes offered for this product. */
    public function sizes(): array
    {
        return config('store.sizes.'.$this->size_type, config('store.sizes.alpha'));
    }

    /** Server-side price for a size (mirrors the storefront logic). */
    public function priceFor(?string $size): ?int
    {
        if ($this->price === null) {
            return null;
        }
        if ($this->price_large && is_numeric($size) && (int) $size >= (int) config('store.large_from', 38)) {
            return $this->price_large;
        }

        return $this->price;
    }

    /** Shape used by the storefront JavaScript (window.DOOO.products). */
    public function toStorefront(): array
    {
        return [
            'id' => $this->design_no,
            'name' => $this->name,
            'cats' => $this->categories->pluck('name')->values()->all(),
            'img' => $this->image_url ?? '',
            'tag' => $this->tag ?: null,
            'price' => $this->price,
            'priceLarge' => $this->price_large,
            'compare' => $this->compare_price,
            'from' => $this->price_from,
            'sizes' => $this->size_type,
            'colours' => $this->colours ?: null,
            'desc' => (string) $this->description,
            'signature' => $this->is_signature,
            'inStock' => $this->in_stock,
        ];
    }
}
