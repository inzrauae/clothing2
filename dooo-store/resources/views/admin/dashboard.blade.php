@extends('layouts.admin')
@section('title', 'Dashboard')

@php
  $statusColors = ['pending' => '#d69e2e', 'confirmed' => '#2f6db5', 'processing' => '#5b4bc4', 'shipped' => '#13808a', 'delivered' => '#1d6b4a', 'cancelled' => '#c2413b', 'returned' => '#8a909b'];
  $hour = (int) now()->format('G');
  $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
  $chg = function ($v) {
      if ($v === null) return '<span class="chg flat">—</span>';
      $cls = $v > 0 ? 'up' : ($v < 0 ? 'down' : 'flat');
      return '<span class="chg '.$cls.'">'.($v > 0 ? '▲' : ($v < 0 ? '▼' : '')).' '.abs($v).'%</span>';
  };
  $statusTotal = max(1, $statuses->sum('count'));
@endphp

@section('content')
<div class="grid" style="gap:22px">

  <section class="card hero-card">
    <div class="card-b">
      <div>
        <h2>{{ $greet }}, <em>{{ strtok(auth()->user()->name, ' ') }}</em></h2>
        <p>{{ now()->format('l, j F Y') }} · Here's how your store is doing.</p>
      </div>
      <div class="hero-stats">
        <div><b>{{ $kpi['today'] }}</b><small>Orders today</small></div>
        <div><b>{{ $kpi['pending'] }}</b><small>Awaiting action</small></div>
        <div><b>{{ $kpi['unread'] }}</b><small>New messages</small></div>
      </div>
    </div>
  </section>

  <div class="grid g-4">
    <div class="card kpi">
      <div class="kpi-top"><span class="kpi-ico t-green"><x-i n="cash"/></span>{!! $chg($kpi['revenue_change']) !!}</div>
      <small>Revenue this month</small><b>{{ money($kpi['revenue']) }}</b>
      <div class="sub">vs last month · confirmed &amp; delivered</div>
    </div>
    <div class="card kpi">
      <div class="kpi-top"><span class="kpi-ico t-gold"><x-i n="orders"/></span>{!! $chg($kpi['orders_change']) !!}</div>
      <small>Orders this month</small><b>{{ number_format($kpi['orders']) }}</b>
      <div class="sub"><a class="link" href="{{ route('admin.orders.index', ['status' => 'pending']) }}">{{ $kpi['pending'] }} pending <x-i n="arrow" style="width:14px;height:14px"/></a></div>
    </div>
    <div class="card kpi">
      <div class="kpi-top"><span class="kpi-ico t-blue"><x-i n="receipt"/></span></div>
      <small>Average order value</small><b>{{ money($kpi['aov']) }}</b>
      <div class="sub">This month</div>
    </div>
    <div class="card kpi">
      <div class="kpi-top"><span class="kpi-ico t-indigo"><x-i n="customers"/></span>@if($kpi['new_customers'])<span class="chg up">+{{ $kpi['new_customers'] }} new</span>@endif</div>
      <small>Customers</small><b>{{ number_format($kpi['customers']) }}</b>
      <div class="sub">{{ $kpi['products'] }} products live{{ $kpi['hidden'] ? ' · '.$kpi['hidden'].' hidden' : '' }}</div>
    </div>
  </div>

  <div class="grid g-main">
    <section class="card">
      <div class="card-h">
        <div><h2>Sales overview</h2><small>Revenue and orders per day</small></div>
        <div class="actions">
          <div class="legend"><span><i style="background:#0f4a32"></i>Revenue</span><span><i style="background:#c6a35a"></i>Orders</span></div>
          <div class="seg"><button type="button" data-range="7">7D</button><button type="button" data-range="14">14D</button><button type="button" data-range="30" class="on">30D</button></div>
        </div>
      </div>
      <div class="card-b"><div class="chart-box"><canvas id="salesChart" aria-label="Sales chart" role="img"></canvas></div></div>
    </section>

    <section class="card">
      <div class="card-h"><div><h2>Order status</h2><small>All time</small></div></div>
      <div class="card-b">
        <div class="chart-box sm" style="margin-bottom:18px"><canvas id="statusChart" aria-label="Orders by status" role="img"></canvas></div>
        <div class="bars">
          @foreach($statuses as $key => $st)
            <a class="bar-row" href="{{ route('admin.orders.index', ['status' => $key]) }}">
              <span>{{ $st['label'] }}</span>
              <span class="bar"><i style="width:{{ round($st['count'] / $statusTotal * 100, 1) }}%;background:{{ $statusColors[$key] ?? '#999' }}"></i></span>
              <b style="text-align:right">{{ $st['count'] }}</b>
            </a>
          @endforeach
        </div>
      </div>
    </section>
  </div>

  <div class="grid g-main">
    <section class="card">
      <div class="card-h"><div><h2>Recent orders</h2><small>Latest activity from the website</small></div><a class="link" href="{{ route('admin.orders.index') }}">View all <x-i n="arrow"/></a></div>
      <div class="table-wrap">
        <table class="t">
          <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th class="num">Total</th></tr></thead>
          <tbody>
          @forelse($recentOrders as $o)
            <tr>
              <td><a class="row-link" href="{{ route('admin.orders.show', $o) }}">{{ $o->ref }}</a><div class="muted">{{ $o->created_at->diffForHumans() }} · {{ $o->items_count }} {{ Str::plural('item', $o->items_count) }}</div></td>
              <td><div class="who"><span class="avatar">{{ strtoupper(mb_substr($o->name, 0, 1)) }}</span><div><b>{{ $o->name }}</b><small>{{ $o->city }}</small></div></div></td>
              <td>@include('admin.partials.status', ['s' => $o->status])</td>
              <td class="num"><b>{{ money($o->total) }}</b>@if($o->items_tbc)<div class="muted">+{{ $o->items_tbc }} TBC</div>@endif</td>
            </tr>
          @empty
            <tr><td colspan="4"><div class="empty"><x-i n="orders"/><b>No orders yet</b>New orders from the website will appear here.</div></td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <div class="stack">
      <section class="card">
        <div class="card-h"><div><h2>Best sellers</h2><small>By units sold</small></div><a class="link" href="{{ route('admin.products.index') }}">Products <x-i n="arrow"/></a></div>
        <div class="card-b tight">
          <div class="mini-list">
            @forelse($topProducts as $i => $p)
              @php $img = $productImages[$p->product_id] ?? null; @endphp
              <a href="{{ $img ? route('admin.products.edit', $img) : route('admin.products.index', ['q' => $p->design_no]) }}">
                <span class="rank">{{ $i + 1 }}</span>
                @if($img?->image_url)<img class="thumb sm" src="{{ $img->image_url }}" alt="">@endif
                <div style="min-width:0"><b style="display:block;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $p->name }}</b><small style="color:var(--muted)">Design No {{ $p->design_no }}</small></div>
                <div class="r"><b>{{ $p->qty }} sold</b><small>{{ money((int) $p->revenue) }}</small></div>
              </a>
            @empty
              <div class="empty" style="padding:24px"><b>No sales yet</b></div>
            @endforelse
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-h"><div><h2>Latest messages</h2></div><a class="link" href="{{ route('admin.messages.index') }}">Inbox <x-i n="arrow"/></a></div>
        <div class="card-b tight">
          <div class="mini-list">
            @forelse($messages as $m)
              <a href="{{ route('admin.messages.show', $m) }}">
                <span class="avatar" style="width:34px;height:34px">{{ strtoupper(mb_substr($m->name, 0, 1)) }}</span>
                <div style="min-width:0;flex:1"><b style="display:block;font-weight:{{ $m->is_read ? 500 : 600 }}">{{ $m->name }} @unless($m->is_read)<span class="badge s-pending plain" style="margin-left:4px">New</span>@endunless</b>
                  <small style="color:var(--muted);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ Str::limit($m->message, 60) }}</small></div>
                <div class="r"><small>{{ $m->created_at->diffForHumans(null, true) }}</small></div>
              </a>
            @empty
              <div class="empty" style="padding:24px"><b>No messages yet</b></div>
            @endforelse
          </div>
        </div>
      </section>

      @if($topDistricts->isNotEmpty())
      <section class="card">
        <div class="card-h"><div><h2>Top districts</h2><small>Where your orders go</small></div></div>
        <div class="card-b">
          @php $maxD = max(1, $topDistricts->max('c')); @endphp
          <div class="bars">
            @foreach($topDistricts as $d)
              <div class="bar-row"><span>{{ $d->district }}</span><span class="bar"><i style="width:{{ round($d->c / $maxD * 100) }}%;background:var(--gold)"></i></span><b style="text-align:right">{{ $d->c }}</b></div>
            @endforeach
          </div>
        </div>
      </section>
      @endif
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@php
  $dash = $chart + ['status' => [
      'labels' => $statuses->pluck('label')->values(),
      'values' => $statuses->pluck('count')->values(),
      'colors' => $statuses->keys()->map(fn ($k) => $statusColors[$k] ?? '#999')->values(),
  ]];
@endphp
<script>window.DASH = @json($dash);</script>
@endpush
