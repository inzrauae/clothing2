<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;

/** Builds window.DOOO — the data the storefront JavaScript runs on. */
class StoreCatalog
{
    public static function build(): array
    {
        $products = Product::query()->active()->ordered()->with('categories')->get();

        return [
            'COLOURS' => config('store.colours'),
            'SIZES' => config('store.sizes'),
            'CATS' => Category::query()->active()->ordered()->pluck('name')->values()->all(),
            'products' => $products->map->toStorefront()->values()->all(),
            'settings' => collect(setting_all())->only([
                'store_name', 'tagline', 'whatsapp', 'whatsapp_display', 'phone', 'phone_display', 'email', 'address',
                'company', 'group', 'facebook', 'instagram', 'tiktok', 'youtube', 'announcement',
            ])->all(),
            'routes' => [
                'order' => route('orders.store'),
                'contact' => route('contact.store'),
            ],
        ];
    }
}
