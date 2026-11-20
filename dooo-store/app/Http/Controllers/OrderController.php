<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Receives orders from the storefront checkout. Prices are always taken from the database. */
class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Honeypot: the hidden "website" field is invisible to people, so only bots fill it in
        if (filled($request->input('website'))) {
            \App\Support\Audit::log('spam.order_blocked');

            return response()->json(['message' => 'Sorry, we could not place your order. Please order on WhatsApp.'], 422);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[\d\s\-]{9,16}$/'],
            'alt' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'city' => ['required', 'string', 'min:2', 'max:80'],
            'district' => ['required', Rule::in(config('store.districts'))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.id' => ['required', 'string', 'max:20'],
            'items.*.size' => ['required', 'string', 'max:10'],
            'items.*.colour' => ['nullable', 'string', 'max:40'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'phone.regex' => 'Please enter a valid phone number.',
            'items.required' => 'Your bag is empty.',
        ]);

        $digits = preg_replace('/\D/', '', $data['phone']);
        if (! preg_match('/^(0\d{9}|94\d{9})$/', $digits)) {
            throw ValidationException::withMessages(['phone' => 'Enter a valid Sri Lankan number, e.g. 0771234567']);
        }

        $products = Product::query()->active()
            ->whereIn('design_no', collect($data['items'])->pluck('id')->unique())
            ->get()->keyBy('design_no');

        $lines = [];
        foreach ($data['items'] as $i => $item) {
            $product = $products->get($item['id']);
            if (! $product) {
                throw ValidationException::withMessages(["items.$i.id" => "Design No {$item['id']} is no longer available. Please remove it from your bag."]);
            }
            if (! in_array($item['size'], $product->sizes(), true)) {
                throw ValidationException::withMessages(["items.$i.size" => "Size {$item['size']} is not available for Design No {$product->design_no}."]);
            }
            $colour = trim((string) ($item['colour'] ?? ''));
            if ($product->colours && $colour !== '' && ! in_array($colour, $product->colours, true)) {
                throw ValidationException::withMessages(["items.$i.colour" => "Colour {$colour} is not available for Design No {$product->design_no}."]);
            }
            $unit = $product->priceFor($item['size']);
            $lines[] = [
                'product_id' => $product->id,
                'design_no' => $product->design_no,
                'name' => $product->name,
                'size' => $item['size'],
                'colour' => $colour ?: null,
                'qty' => (int) $item['qty'],
                'unit_price' => $unit,
                'line_total' => $unit === null ? null : $unit * (int) $item['qty'],
            ];
        }

        $order = DB::transaction(function () use ($data, $lines, $request) {
            $phone = Customer::normalisePhone($data['phone']);
            $customer = Customer::query()->updateOrCreate(['phone' => $phone], [
                'name' => $data['name'],
                'alt_phone' => $data['alt'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'],
                'district' => $data['district'],
            ]);

            $order = Order::query()->create([
                'ref' => Order::newRef(),
                'customer_id' => $customer->id,
                'name' => $data['name'],
                'phone' => $phone,
                'alt_phone' => $data['alt'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'],
                'district' => $data['district'],
                'notes' => $data['notes'] ?? null,
                'delivery_fee' => max(0, (int) setting('delivery_fee', 0)),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'cod',
                'source' => 'website',
                'ip' => $request->ip(),
            ]);
            $order->items()->createMany($lines);
            $order->recalculate();
            $order->save();

            return $order;
        });

        return response()->json([
            'ref' => $order->ref,
            'subtotal' => $order->subtotal,
            'total' => $order->total,
            'items_tbc' => $order->items_tbc,
            'whatsapp_url' => wa_link($order->whatsappText()),
        ], 201);
    }
}
