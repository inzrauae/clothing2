<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $revenue = config('store.revenue_statuses');
        $sort = in_array($request->input('sort'), ['spent', 'orders', 'name'], true) ? $request->input('sort') : 'latest';

        $customers = Customer::query()
            ->withCount('orders')
            ->withSum(['orders as spent' => fn (Builder $q) => $q->whereIn('status', $revenue)], 'total')
            ->withMax('orders as last_order_at', 'created_at')
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $t = trim((string) $request->input('q'));
                $digits = preg_replace('/\D/', '', $t);
                $q->where(function (Builder $w) use ($t, $digits) {
                    $w->where('name', 'like', "%{$t}%")->orWhere('city', 'like', "%{$t}%");
                    if (strlen($digits) >= 3) {
                        $w->orWhere('phone', 'like', "%{$digits}%");
                    }
                });
            })
            ->when($request->filled('district'), fn (Builder $q) => $q->where('district', $request->input('district')))
            ->when($sort === 'spent', fn (Builder $q) => $q->orderByDesc('spent'))
            ->when($sort === 'orders', fn (Builder $q) => $q->orderByDesc('orders_count'))
            ->when($sort === 'name', fn (Builder $q) => $q->orderBy('name'))
            ->when($sort === 'latest', fn (Builder $q) => $q->latest())
            ->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers', 'sort'));
    }

    public function show(Customer $customer): View
    {
        $customer->load(['orders' => fn ($q) => $q->withCount('items')->latest()]);
        $spent = (int) $customer->orders->whereIn('status', config('store.revenue_statuses'))->sum('total');

        return view('admin.customers.show', compact('customer', 'spent'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'alt_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:80'],
            'district' => ['nullable', Rule::in(config('store.districts'))],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]));

        return back()->with('ok', 'Customer details saved.');
    }
}
