@extends('layouts.admin')
@section('title', 'Customers')

@section('content')
<div class="page-head">
  <div><div class="crumb">Sales</div><h1>Customers</h1><p>Created automatically from website orders, matched by phone number.</p></div>
</div>

<section class="card">
  <form class="filters" method="get">
    <div class="search"><x-i n="search"/><input class="in" type="search" name="q" value="{{ request('q') }}" placeholder="Name, phone or city" aria-label="Search customers"></div>
    <select class="in auto" name="district" data-autosubmit aria-label="District">
      <option value="">All districts</option>
      @foreach(config('store.districts') as $d)<option @selected(request('district') === $d)>{{ $d }}</option>@endforeach
    </select>
    <select class="in auto" name="sort" data-autosubmit aria-label="Sort">
      <option value="latest" @selected($sort === 'latest')>Newest first</option>
      <option value="spent" @selected($sort === 'spent')>Top spenders</option>
      <option value="orders" @selected($sort === 'orders')>Most orders</option>
      <option value="name" @selected($sort === 'name')>Name A–Z</option>
    </select>
    <button class="btn btn-primary sm" type="submit">Search</button>
    @if(request()->hasAny(['q', 'district']))<a class="link" href="{{ route('admin.customers.index') }}">Clear</a>@endif
  </form>
  <div class="table-wrap">
    <table class="t">
      <thead><tr><th>Customer</th><th>Location</th><th class="num">Orders</th><th class="num">Spent</th><th>Last order</th><th></th></tr></thead>
      <tbody>
      @forelse($customers as $c)
        <tr>
          <td><a class="who" href="{{ route('admin.customers.show', $c) }}"><span class="avatar">{{ strtoupper(mb_substr($c->name, 0, 1)) }}</span><div><b>{{ $c->name }}</b><small>{{ $c->phone }}</small></div></a></td>
          <td class="muted">{{ $c->city ?: '—' }}{{ $c->district ? ', '.$c->district : '' }}</td>
          <td class="num">{{ $c->orders_count }}</td>
          <td class="num"><b>{{ money((int) $c->spent) }}</b></td>
          <td class="muted">{{ $c->last_order_at ? \Illuminate\Support\Carbon::parse($c->last_order_at)->diffForHumans() : '—' }}</td>
          <td class="num"><div class="actions" style="justify-content:flex-end;flex-wrap:nowrap">
            <a class="btn btn-wa xs icon-only" href="{{ wa_link(null, wa_number($c->phone)) }}" target="_blank" rel="noopener" aria-label="WhatsApp {{ $c->name }}"><x-i n="wa"/></a>
            <a class="btn btn-ghost xs" href="{{ route('admin.customers.show', $c) }}">View</a></div></td>
        </tr>
      @empty
        <tr><td colspan="6"><div class="empty"><x-i n="customers"/><b>No customers yet</b>Customers appear here after their first order.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $customers->links() }}
</section>
@endsection
