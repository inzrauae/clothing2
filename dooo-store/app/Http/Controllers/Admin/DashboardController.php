<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $revenueStatuses = config('store.revenue_statuses');
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = $monthStart->copy()->subSecond();

        $revenueMonth = (int) Order::revenue()->where('created_at', '>=', $monthStart)->sum('total');
        $revenueLast = (int) Order::revenue()->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total');
        $ordersMonth = Order::query()->where('created_at', '>=', $monthStart)->count();
        $ordersLast = Order::query()->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $revenueOrders = Order::revenue()->where('created_at', '>=', $monthStart)->count();

        $kpi = [
            'revenue' => $revenueMonth,
            'revenue_change' => $this->change($revenueMonth, $revenueLast),
            'orders' => $ordersMonth,
            'orders_change' => $this->change($ordersMonth, $ordersLast),
            'today' => Order::query()->where('created_at', '>=', now()->startOfDay())->count(),
            'pending' => Order::query()->where('status', 'pending')->count(),
            'aov' => $revenueOrders ? intdiv($revenueMonth, $revenueOrders) : 0,
            'customers' => Customer::query()->count(),
            'new_customers' => Customer::query()->where('created_at', '>=', $monthStart)->count(),
            'products' => Product::query()->active()->count(),
            'hidden' => Product::query()->where('is_active', false)->count(),
            'unread' => Message::query()->where('is_read', false)->count(),
        ];

        // Last 30 days — grouped in PHP so it works on MySQL and SQLite alike
        $from = now()->subDays(29)->startOfDay();
        $rows = Order::query()->where('created_at', '>=', $from)->get(['created_at', 'total', 'status']);
        $days = [];
        for ($d = $from->copy(); $d <= now(); $d->addDay()) {
            $days[$d->format('Y-m-d')] = ['label' => $d->format('M j'), 'orders' => 0, 'revenue' => 0];
        }
        foreach ($rows as $o) {
            $key = Carbon::parse($o->created_at)->format('Y-m-d');
            if (! isset($days[$key])) {
                continue;
            }
            $days[$key]['orders']++;
            if (in_array($o->status, $revenueStatuses, true)) {
                $days[$key]['revenue'] += $o->total;
            }
        }
        $chart = [
            'labels' => array_column($days, 'label'),
            'orders' => array_column($days, 'orders'),
            'revenue' => array_column($days, 'revenue'),
        ];

        $statusCounts = Order::query()->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');
        $statuses = collect(config('store.order_statuses'))->map(fn ($label, $key) => ['label' => $label, 'count' => (int) ($statusCounts[$key] ?? 0)]);

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', $revenueStatuses)
            ->select('order_items.design_no', 'order_items.name', 'order_items.product_id',
                DB::raw('sum(order_items.qty) as qty'), DB::raw('sum(coalesce(order_items.line_total,0)) as revenue'))
            ->groupBy('order_items.design_no', 'order_items.name', 'order_items.product_id')
            ->orderByDesc('qty')->limit(5)->get();
        $productImages = Product::query()->whereIn('id', $topProducts->pluck('product_id')->filter())->get()->keyBy('id');

        $topDistricts = Order::revenue()->select('district', DB::raw('count(*) as c'))
            ->groupBy('district')->orderByDesc('c')->limit(5)->get();

        return view('admin.dashboard', [
            'kpi' => $kpi,
            'chart' => $chart,
            'statuses' => $statuses,
            'recentOrders' => Order::query()->withCount('items')->latest()->limit(7)->get(),
            'topProducts' => $topProducts,
            'productImages' => $productImages,
            'topDistricts' => $topDistricts,
            'messages' => Message::query()->latest()->limit(4)->get(),
        ]);
    }

    private function change(int $now, int $before): ?float
    {
        if ($before === 0) {
            return $now > 0 ? 100.0 : null;
        }

        return round(($now - $before) / $before * 100, 1);
    }
}
