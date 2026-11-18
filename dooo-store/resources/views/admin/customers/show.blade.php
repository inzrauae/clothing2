@extends('layouts.admin')
@section('title', $customer->name)

@section('content')
<div class="page-head">
  <div><div class="crumb"><a href="{{ route('admin.customers.index') }}">Customers</a> / {{ $customer->phone }}</div><h1>{{ $customer->name }}</h1><p>Customer since {{ $customer->created_at->format('j M Y') }}</p></div>
  <div class="actions">
    <a class="btn btn-wa" href="{{ wa_link('Hi '.$customer->name.'! This is '.setting('store_name').'.', wa_number($customer->phone)) }}" target="_blank" rel="noopener"><x-i n="wa"/>WhatsApp</a>
    <a class="btn btn-ghost" href="tel:{{ $customer->phone }}"><x-i n="phone"/>Call</a>
  </div>
</div>

<div class="grid g-3" style="margin-bottom:22px">
  <div class="card kpi"><div class="kpi-top"><span class="kpi-ico t-gold"><x-i n="orders"/></span></div><small>Orders</small><b>{{ $customer->orders->count() }}</b></div>
  <div class="card kpi"><div class="kpi-top"><span class="kpi-ico t-green"><x-i n="cash"/></span></div><small>Total spent</small><b>{{ money($spent) }}</b><div class="sub">Confirmed &amp; delivered orders</div></div>
  <div class="card kpi"><div class="kpi-top"><span class="kpi-ico t-blue"><x-i n="clock"/></span></div><small>Last order</small><b style="font-size:20px">{{ $customer->orders->first()?->created_at->format('j M Y') ?? '—' }}</b></div>
</div>

<div class="grid g-main">
  <section class="card">
    <div class="card-h"><h2>Order history</h2></div>
    <div class="table-wrap">
      <table class="t">
        <thead><tr><th>Order</th><th>Date</th><th>Status</th><th class="num">Total</th></tr></thead>
        <tbody>
        @forelse($customer->orders as $o)
          <tr>
            <td><a class="row-link" href="{{ route('admin.orders.show', $o) }}">{{ $o->ref }}</a><div class="muted">{{ $o->items_count }} {{ Str::plural('line', $o->items_count) }}</div></td>
            <td class="muted">{{ $o->created_at->format('M j, Y g:i A') }}</td>
            <td>@include('admin.partials.status', ['s' => $o->status])</td>
            <td class="num"><b>{{ money($o->total) }}</b></td>
          </tr>
        @empty
          <tr><td colspan="4"><div class="empty"><b>No orders</b></div></td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <section class="card">
    <div class="card-h"><h2>Details</h2></div>
    <form class="card-b" method="post" action="{{ route('admin.customers.update', $customer) }}" style="display:grid;gap:16px">
      @csrf @method('PUT')
      <div class="f"><label for="name">Name</label><input class="in" id="name" name="name" value="{{ old('name', $customer->name) }}" required></div>
      <div class="f"><label>Phone</label><input class="in" value="{{ $customer->phone }}" disabled><span class="hint">The phone number links orders to this customer.</span></div>
      <div class="f"><label for="alt_phone">Alternative phone</label><input class="in" id="alt_phone" name="alt_phone" value="{{ old('alt_phone', $customer->alt_phone) }}"></div>
      <div class="f"><label for="address">Address</label><input class="in" id="address" name="address" value="{{ old('address', $customer->address) }}"></div>
      <div class="form-grid">
        <div class="f"><label for="city">City</label><input class="in" id="city" name="city" value="{{ old('city', $customer->city) }}"></div>
        <div class="f"><label for="district">District</label><select class="in" id="district" name="district"><option value="">—</option>@foreach(config('store.districts') as $d)<option @selected(old('district', $customer->district) === $d)>{{ $d }}</option>@endforeach</select></div>
      </div>
      <div class="f"><label for="notes">Private notes</label><textarea class="in" id="notes" name="notes" rows="3" placeholder="Preferred sizes, styles…">{{ old('notes', $customer->notes) }}</textarea></div>
      <button class="btn btn-primary" type="submit"><x-i n="check"/>Save customer</button>
    </form>
  </section>
</div>
@endsection
