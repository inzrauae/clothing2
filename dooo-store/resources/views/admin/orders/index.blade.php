@extends('layouts.admin')
@section('title', 'Orders')

@section('content')
<div class="page-head">
  <div><div class="crumb">Sales</div><h1>Orders</h1><p>Every order placed on the website, newest first.</p></div>
  <div class="actions">
    <a class="btn btn-ghost" href="{{ route('admin.orders.export', request()->query()) }}"><x-i n="download"/>Export CSV</a>
  </div>
</div>

@php($cur = request('status'))
<nav class="tabs" aria-label="Filter by status">
  <a href="{{ route('admin.orders.index', request()->except('status', 'page')) }}" class="{{ !$cur ? 'on' : '' }}">All <em>{{ $total }}</em></a>
  @foreach(config('store.order_statuses') as $key => $label)
    <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => $key])) }}" class="{{ $cur === $key ? 'on' : '' }}">{{ $label }} <em>{{ $counts[$key] ?? 0 }}</em></a>
  @endforeach
</nav>

<section class="card">
  <form class="filters" method="get">
    @if($cur)<input type="hidden" name="status" value="{{ $cur }}">@endif
    <div class="search"><x-i n="search"/><input class="in" type="search" name="q" value="{{ request('q') }}" placeholder="Ref, name, phone or city" aria-label="Search orders"></div>
    <select class="in auto" name="payment" data-autosubmit aria-label="Payment status">
      <option value="">Any payment</option>
      @foreach(config('store.payment_statuses') as $k => $l)<option value="{{ $k }}" @selected(request('payment') === $k)>{{ $l }}</option>@endforeach
    </select>
    <input class="in auto" type="date" name="from" value="{{ request('from') }}" aria-label="From date">
    <input class="in auto" type="date" name="to" value="{{ request('to') }}" aria-label="To date">
    <button class="btn btn-primary sm" type="submit">Filter</button>
    @if(request()->hasAny(['q', 'payment', 'from', 'to']))<a class="link" href="{{ route('admin.orders.index', $cur ? ['status' => $cur] : []) }}">Clear</a>@endif
  </form>

  <div class="table-wrap">
    <table class="t">
      <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Status</th><th>Payment</th><th class="num">Total</th><th></th></tr></thead>
      <tbody>
      @forelse($orders as $o)
        <tr>
          <td><a class="row-link" href="{{ route('admin.orders.show', $o) }}">{{ $o->ref }}</a><div class="muted">{{ $o->items_count }} {{ Str::plural('line', $o->items_count) }}</div></td>
          <td><div class="who"><span class="avatar">{{ strtoupper(mb_substr($o->name, 0, 1)) }}</span><div><b>{{ $o->name }}</b><small>{{ $o->phone }} · {{ $o->city }}</small></div></div></td>
          <td class="muted" style="white-space:nowrap">{{ $o->created_at->format('M j, Y') }}<br>{{ $o->created_at->format('g:i A') }}</td>
          <td>@include('admin.partials.status', ['s' => $o->status])</td>
          <td>@include('admin.partials.status', ['p' => $o->payment_status])</td>
          <td class="num"><b>{{ money($o->total) }}</b>@if($o->items_tbc)<div><span class="badge s-tbc plain">{{ $o->items_tbc }} to price</span></div>@endif</td>
          <td class="num"><a class="btn btn-ghost xs" href="{{ route('admin.orders.show', $o) }}">Open <x-i n="right" style="width:14px;height:14px"/></a></td>
        </tr>
      @empty
        <tr><td colspan="7"><div class="empty"><x-i n="orders"/><b>No orders found</b>Try a different filter or search.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $orders->links() }}
</section>
@endsection
