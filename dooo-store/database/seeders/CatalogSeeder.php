<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Loads the dooo catalogue (categories, 26 products) and default settings.
 * Safe to run more than once: existing records are updated, not duplicated.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('store.default_settings') as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
        Setting::flush();

        $cats = [
            'Trousers' => 'Tailored to sculpt, made to move. Office, casual, high-waist and wide-leg fits in sizes 28 to 46.',
            'Linen' => 'Breathable, light and effortlessly elegant. Linen frocks, tops and sets for warm days.',
            'Frocks' => 'Easy elegance for every day, from casual checks to modern sleeveless linen.',
            'Sets' => 'Coordinated kurtha, waistcoat and linen sets. One easy choice, beautifully put together.',
            'Tops' => "Versatile tops and blouses that pair perfectly with your favourite dooo trousers.",
        ];
        $catIds = [];
        $i = 0;
        foreach ($cats as $name => $desc) {
            $catIds[$name] = Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $desc, 'sort_order' => ++$i, 'is_active' => true]
            )->id;
        }

        $sigColours = ['Navy', 'Ivory', 'Black', 'Honey Beige', 'Dark Brown', 'Grey', 'Olive Green', 'Dark Burgundy'];
        $sig = ['price' => 1840, 'price_large' => 2040, 'compare_price' => 2270, 'size_type' => 'waist', 'colours' => $sigColours, 'is_signature' => true];

        $products = [
            ['02', 'The Boot Cut Luxe', ['Trousers'], 'd02-bootcut.webp', 'Bestseller', $sig, 'Our bestselling boot-cut formal trouser with slant pockets — tailored to sculpt and made to move, from the office to evening plans.'],
            ['03', 'The Executive High Waist', ['Trousers'], 'd03-highwaist.webp', 'Office', $sig, 'A high-waist office pant with side pockets that lengthens the leg and keeps its shape all day.'],
            ['04', 'The Tailored Pin Tuck', ['Trousers'], 'd04-pintuck.webp', 'Tailored', $sig, 'A high-waist boot cut finished with a sharp pin-tuck crease for a polished, tailored line.'],
            ['05', 'The Wide Leg Statement', ['Trousers'], 'd05-wideleg.webp', 'New', $sig, 'A high-waist wide-leg trouser with side pockets — fluid, elegant and effortlessly comfortable.'],
            ['38', 'Linen Top — Black & White', ['Tops', 'Linen'], 'd38-linen-top.webp', 'New', [], 'A modern split-colour linen top in black and white. Breathable, light and easy to style with ivory or black trousers.'],
            ['37', 'Super Cutlon Top', ['Tops'], 'd37-cutlon-top.webp', 'New', ['price' => 1490, 'price_from' => true], 'A soft, easy-care cutlon top that keeps its shape — a versatile everyday essential.'],
            ['36', 'Linen Top with Wide Leg Pants', ['Sets', 'Linen'], 'd36-linen-wideleg.webp', 'New', [], 'A coordinated linen top and wide-leg pant set. Breezy, relaxed and put-together in one easy choice.'],
            ['35', 'Sleeveless Modern Linen Frock', ['Frocks', 'Linen'], 'd35-linen-frock.webp', null, [], 'A clean, sleeveless linen frock with a modern silhouette — cool and comfortable for warm days.'],
            ['34', 'Cutlon Kurtha Top with Wide Leg Pants', ['Sets'], 'd34-kurtha-set.webp', null, ['price' => 4690, 'price_from' => true], 'A cutlon kurtha top paired with wide-leg pants — graceful, modest and ready for any occasion.'],
            ['33', 'Waistcoat with Wide Leg Pants', ['Sets', 'Trousers'], 'd33-waistcoat-set.webp', null, [], 'A tailored waistcoat with matching wide-leg pants. A confident, polished set for work and events.'],
            ['31', 'Linen Long Kurtha with Wide Leg Pants', ['Sets', 'Linen'], 'd31-linen-kurtha.webp', null, [], 'A flowing long linen kurtha with wide-leg pants — light, elegant and beautifully breathable.'],
            ['30', 'Linen Shirt Blouse with Linen Pant', ['Sets', 'Linen'], 'd30-linen-set.webp', null, [], 'A linen shirt blouse with a matching linen pant. Relaxed tailoring for easy, elegant days.'],
            ['29', 'Blue & Red Check Casual Frock', ['Frocks'], 'd29-check-frock.webp', null, ['price' => 1990, 'price_from' => true], 'A belted casual frock in a navy, white and red check — easy elegance for everyday wear.'],
            ['28', 'Casual Linen Frock — Sky Blue', ['Frocks', 'Linen'], 'd28-blue-frock.webp', null, [], 'A fresh sky-blue linen frock with a relaxed, flattering fit.'],
            ['26', 'Linen Crop Top with Pants', ['Sets', 'Linen'], 'd26-linen-crop.webp', null, [], 'A linen crop top and pant set — modern, breezy and effortlessly coordinated.'],
            ['25', 'Casual Linen Frock — Sand', ['Frocks', 'Linen'], 'd25-linen-frock.webp', null, [], 'A soft sand-toned linen frock — a timeless neutral that pairs with everything.'],
            ['24', 'High Waist Bandless Pin Tuck Pant', ['Trousers'], 'd24-pintuck-pant.webp', null, ['size_type' => 'waist'], 'A high-waist bandless trouser with a pin-tuck crease for a smooth, streamlined finish.'],
            ['22', 'Casual Frock — Plum', ['Frocks'], 'd22-casual-frock.webp', null, [], 'A rich plum casual frock with an easy, flattering shape.'],
            ['21', 'Casual Frock — Coral', ['Frocks'], 'd21-casual-frock.webp', null, [], 'A bright coral casual frock — cheerful, comfortable and easy to wear.'],
            ['19', 'Sleeveless Linen Frock', ['Frocks', 'Linen'], 'd19-sleeveless-frock.webp', null, [], 'A sleeveless linen frock cut for comfort and cool, breathable wear.'],
            ['18', 'Three Quarter Sleeve Linen Frock', ['Frocks', 'Linen'], 'd18-linen-frock.webp', null, [], 'A graceful linen frock with three-quarter sleeves — elegant coverage with breathable comfort.'],
            ['17', 'Mid Waist Casual Short', ['Trousers'], 'd17-shorts.webp', null, ['size_type' => 'waist'], 'A mid-waist casual short for relaxed weekends and warm days.'],
            ['15', 'High Waist Super Wide Leg Pant', ['Trousers'], 'd15-wideleg.webp', null, ['size_type' => 'waist'], 'A dramatic high-waist super wide-leg pant with beautiful movement.'],
            ['14', 'High Waist Slant Pocket Office Pant', ['Trousers'], 'd14-office-pant.webp', null, ['size_type' => 'waist'], 'A high-waist office pant with slant pockets — sharp, structured and comfortable all day.'],
            ['13', 'Mid Waist Casual Pant with Belt', ['Trousers'], 'd13-casual-pant.webp', null, ['size_type' => 'waist'], 'A mid-waist casual pant finished with a belt for an easy, smart-casual look.'],
            ['12', 'Mid Waist Pencil Cut Office Pant', ['Trousers'], 'd12-pencil-pant.webp', null, ['size_type' => 'waist'], 'A mid-waist pencil-cut office pant with a slim, polished line.'],
        ];

        foreach ($products as $order => [$no, $name, $productCats, $img, $tag, $extra, $desc]) {
            $product = Product::query()->updateOrCreate(['design_no' => $no], array_merge([
                'name' => $name,
                'slug' => Str::slug($name.'-'.$no),
                'description' => $desc,
                'image' => 'images/premium/'.$img,
                'size_type' => 'alpha',
                'colours' => null,
                'price' => null,
                'price_large' => null,
                'compare_price' => null,
                'price_from' => false,
                'tag' => $tag,
                'is_signature' => false,
                'is_active' => true,
                'in_stock' => true,
                'sort_order' => $order + 1,
            ], $extra));
            $product->categories()->sync(array_map(fn ($c) => $catIds[$c], $productCats));
        }
    }
}
