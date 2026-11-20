<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $this->filtered($request)->withCount('items')->latest()->paginate(15)->withQueryString();
        $counts = Order::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('admin.orders.index', [
            'orders' => $orders,
            'counts' => $counts,
            'total' => (int) $counts->sum(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load('items.product', 'customer');
        $history = $order->customer_id
            ? Order::query()->where('customer_id', $order->customer_id)->whereKeyNot($order->id)->latest()->limit(5)->get()
            : collect();

        return view('admin.orders.show', compact('order', 'history'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('store.order_statuses')))],
            'payment_status' => ['required', Rule::in(array_keys(config('store.payment_statuses')))],
            'delivery_fee' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'tracking_no' => ['nullable', 'string', 'max:80'],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $data['delivery_fee'] = (int) ($data['delivery_fee'] ?? 0);
        $data['discount'] = (int) ($data['discount'] ?? 0);

        $order->fill($data);
        $order->recalculate();
        $order->save();

        return back()->with('ok', "Order {$order->ref} updated.");
    }

    /** Set prices / quantities — used to price "price on request" items after confirming with the customer. */
    public function updateItems(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.unit_price' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'items.*.qty' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        foreach ($order->items as $item) {
            if (! isset($data['items'][$item->id])) {
                continue;
            }
            $row = $data['items'][$item->id];
            if ((int) $row['qty'] === 0) {
                $item->delete();
                continue;
            }
            $unit = $row['unit_price'] === null || $row['unit_price'] === '' ? null : (int) $row['unit_price'];
            $item->update([
                'qty' => (int) $row['qty'],
                'unit_price' => $unit,
                'line_total' => $unit === null ? null : $unit * (int) $row['qty'],
            ]);
        }

        $order->unsetRelation('items');
        $order->recalculate();
        $order->save();

        return back()->with('ok', 'Order items updated and totals recalculated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $ref = $order->ref;
        \App\Support\Audit::log('order.deleted', ['ref' => $ref, 'customer' => $order->name, 'total' => $order->total]);
        $order->delete();

        return redirect()->route('admin.orders.index')->with('ok', "Order {$ref} deleted.");
    }

    public function invoice(Order $order): View
    {
        $order->load('items');

        return view('admin.orders.invoice', compact('order'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->with('items')->latest();
        $name = 'dooo-orders-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['Ref', 'Date', 'Customer', 'Phone', 'City', 'District', 'Items', 'Subtotal', 'Delivery', 'Discount', 'Total', 'Items TBC', 'Status', 'Payment', 'Tracking']);
            $query->chunk(200, function ($orders) use ($out) {
                foreach ($orders as $o) {
                    fputcsv($out, [
                        $o->ref, $o->created_at->format('Y-m-d H:i'), $o->name, $o->phone, $o->city, $o->district,
                        $o->items->map(fn ($i) => "No {$i->design_no} {$i->size}".($i->colour ? " {$i->colour}" : '')." x{$i->qty}")->implode('; '),
                        $o->subtotal, $o->delivery_fee, $o->discount, $o->total, $o->items_tbc, $o->status_label, $o->payment_label, $o->tracking_no,
                    ]);
                }
            });
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request): Builder
    {
        return Order::query()
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = trim((string) $request->input('q'));
                $digits = preg_replace('/\D/', '', $term);
                $q->where(function (Builder $w) use ($term, $digits) {
                    $w->where('ref', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('city', 'like', "%{$term}%");
                    if (strlen($digits) >= 3) {
                        $w->orWhere('phone', 'like', "%{$digits}%");
                    }
                });
            })
            ->when($request->filled('status') && array_key_exists($request->input('status'), config('store.order_statuses')),
                fn (Builder $q) => $q->where('status', $request->input('status')))
            ->when($request->filled('payment') && array_key_exists($request->input('payment'), config('store.payment_statuses')),
                fn (Builder $q) => $q->where('payment_status', $request->input('payment')))
            ->when($this->date($request->input('from')), fn (Builder $q, $d) => $q->where('created_at', '>=', $d->startOfDay()))
            ->when($this->date($request->input('to')), fn (Builder $q, $d) => $q->where('created_at', '<=', $d->endOfDay()));
    }

    private function date(?string $value): ?\Illuminate\Support\Carbon
    {
        if (! $value || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        try {
            return \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
