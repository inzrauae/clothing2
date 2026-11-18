<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Sample orders, customers and messages so the dashboard can be previewed.
 * Optional — only used locally or when "Add sample data" is ticked in the installer.
 */
class DemoOrdersSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(42);
        $products = Product::query()->active()->get();
        if ($products->isEmpty()) {
            return;
        }

        $people = [
            ['Fathima Azeez', '0771234501', 'Colombo', 'Colombo'], ['Nimeshi Wijesinghe', '0712345602', 'Kandy', 'Kandy'],
            ['Parami Dias', '0759876503', 'Galle', 'Galle'], ['Noor Sameem', '0768765404', 'Negombo', 'Gampaha'],
            ['Tharushi Perera', '0703456705', 'Kurunegala', 'Kurunegala'], ['Ishara Fernando', '0784567806', 'Matara', 'Matara'],
            ['Dilini Jayawardena', '0725678907', 'Battaramulla', 'Colombo'], ['Shenali Silva', '0746789008', 'Ratnapura', 'Ratnapura'],
            ['Kavindi Rathnayake', '0777890109', 'Anuradhapura', 'Anuradhapura'], ['Amaya Gunasekara', '0718901210', 'Kalutara', 'Kalutara'],
        ];
        $statuses = ['delivered', 'delivered', 'delivered', 'shipped', 'confirmed', 'processing', 'pending', 'pending', 'cancelled', 'returned'];

        for ($n = 0; $n < 46; $n++) {
            [$name, $phone, $city, $district] = $people[$n % count($people)];
            $customer = Customer::query()->firstOrCreate(['phone' => $phone], [
                'name' => $name, 'address' => (10 + $n).' Temple Road', 'city' => $city, 'district' => $district,
            ]);
            $date = Carbon::now()->subDays(mt_rand(0, 29))->setTime(mt_rand(8, 21), mt_rand(0, 59));
            $status = $n < 4 ? 'pending' : $statuses[mt_rand(0, count($statuses) - 1)];

            $order = Order::query()->create([
                'ref' => Order::newRef(), 'customer_id' => $customer->id, 'name' => $name, 'phone' => $phone,
                'address' => $customer->address, 'city' => $city, 'district' => $district,
                'status' => $status, 'payment_status' => $status === 'delivered' ? 'paid' : ($status === 'returned' ? 'refunded' : 'unpaid'),
                'delivery_fee' => 350, 'source' => 'website',
            ]);
            $order->forceFill(['created_at' => $date, 'updated_at' => $date])->save();

            foreach ($products->random(mt_rand(1, 3)) as $p) {
                $size = $p->sizes()[mt_rand(0, count($p->sizes()) - 1)];
                $unit = $p->priceFor($size) ?? 2490 + 100 * mt_rand(0, 15);
                $qty = mt_rand(1, 2);
                $order->items()->create([
                    'product_id' => $p->id, 'design_no' => $p->design_no, 'name' => $p->name, 'size' => $size,
                    'colour' => $p->colours ? $p->colours[mt_rand(0, count($p->colours) - 1)] : null,
                    'qty' => $qty, 'unit_price' => $unit, 'line_total' => $unit * $qty,
                ]);
            }
            $order->recalculate();
            $order->save();
        }

        $msgs = [
            ['Sachini', '0771112223', 'Size advice', 'Hi, I usually wear size 32 in jeans. Which size should I take for Design No 03?'],
            ['Ruwani', '0719998887', 'Wholesale', 'Do you offer wholesale prices for 20+ pieces of the office trousers?'],
            ['Hasini', '0765554443', 'Delivery', 'How many days does delivery take to Jaffna?'],
            ['Madhavi', '0703332221', 'Return or exchange', 'I received size 36 but need 34. How do I exchange?'],
        ];
        foreach ($msgs as $i => [$name, $phone, $topic, $text]) {
            Message::query()->create(['name' => $name, 'phone' => $phone, 'topic' => $topic, 'message' => $text, 'is_read' => $i > 1])
                ->forceFill(['created_at' => Carbon::now()->subHours($i * 7 + 1)])->save();
        }
    }
}
