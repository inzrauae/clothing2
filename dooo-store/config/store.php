<?php

/*
|--------------------------------------------------------------------------
| dooo store configuration
|--------------------------------------------------------------------------
| Fixed lists used by the storefront and the admin panel. Values that the
| shop owner edits day to day (phone numbers, social links, announcement
| text …) live in the `settings` table and are edited under Admin › Settings.
*/

return [

    'currency' => 'Rs.',

    // Created by the web installer; while it is missing, visitors are sent to /install
    'install_lock' => storage_path('app/installed.lock'),

    // Colour palette offered for products (name => swatch hex)
    'colours' => [
        'Navy' => '#1f2a4a',
        'Ivory' => '#efe8d6',
        'Black' => '#111111',
        'Honey Beige' => '#c9a77c',
        'Dark Brown' => '#4a3326',
        'Grey' => '#7d7f82',
        'Olive Green' => '#5b6440',
        'Dark Burgundy' => '#5a1d2a',
        'White' => '#ffffff',
        'Sky Blue' => '#9cc3e4',
        'Sand' => '#d8c3a0',
        'Plum' => '#6b2d4f',
        'Coral' => '#f07c6c',
        'Maroon' => '#7a1f2b',
        'Pink' => '#eab3c2',
        'Mustard' => '#d6a12a',
    ],

    'sizes' => [
        'waist' => ['28', '30', '32', '34', '36', '38', '40', '42', '44', '46'],
        'alpha' => ['M', 'L', 'XL', '2XL', '3XL'],
    ],

    // Waist size from which the "large" trouser price applies
    'large_from' => 38,

    'order_statuses' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
    ],

    'payment_statuses' => [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'refunded' => 'Refunded',
    ],

    // Orders in these states count towards revenue
    'revenue_statuses' => ['confirmed', 'processing', 'shipped', 'delivered'],

    'districts' => [
        'Colombo', 'Gampaha', 'Kalutara', 'Kandy', 'Matale', 'Nuwara Eliya', 'Galle', 'Matara',
        'Hambantota', 'Jaffna', 'Kilinochchi', 'Mannar', 'Vavuniya', 'Mullaitivu', 'Batticaloa',
        'Ampara', 'Trincomalee', 'Kurunegala', 'Puttalam', 'Anuradhapura', 'Polonnaruwa',
        'Badulla', 'Monaragala', 'Ratnapura', 'Kegalle',
    ],

    'message_topics' => [
        'Order enquiry', 'Size advice', 'Delivery', 'Return or exchange', 'Product details', 'Wholesale', 'Other',
    ],

    // Default values for the settings table (used by the installer & seeder)
    'default_settings' => [
        'store_name' => 'dooo',
        'tagline' => 'Fashionably Elegant',
        'whatsapp' => '94761400696',
        'whatsapp_display' => '+94 76 140 0696',
        'phone' => '+94342121312',
        'phone_display' => '+94 342 121 312',
        'email' => 'info@deemindcmb.com',
        'address' => 'No. 10/A, Kumaragewatta Road, Pelawatta, Battaramulla 10120',
        'company' => 'DEEMIND VENTURES (PVT) LTD',
        'group' => 'Convergence Group of Companies',
        'facebook' => 'https://www.facebook.com/dooofashions',
        'instagram' => 'https://www.instagram.com/dooo_fashions',
        'tiktok' => 'https://www.tiktok.com/@dooo_fashion',
        'youtube' => 'https://www.youtube.com/results?search_query=dooo+Fashions',
        'announcement' => "Fast islandwide delivery across Sri Lanka\nOpen & inspect before you pay\nEasy returns & exchanges\nMoney-back guarantee\n24/7 online support\nSizes 28\" to 46\"",
        'delivery_fee' => '0',
        'low_stock_note' => '',
        'meta_description' => 'dooo — premium ladies trousers, linen frocks, tops and sets from Sri Lanka. Perfect fit, premium quality, affordable prices. Sizes 28 to 46. Islandwide delivery.',
    ],
];
