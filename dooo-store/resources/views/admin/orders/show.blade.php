@extends('layouts.admin')
@section('title', 'Order '.$order->ref)

@php
  $flow = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
  $idx = array_search($order->status, $flow, true);
  $waCustomer = wa_link("Hi {$order->name}! This is ".setting('store_name')." about your order {$order->ref}.", wa_number($order->phone));
@endphp

@section('content')
<div class="page-head">
  <div>
    <div class="crumb"><a href="{{ route('admin.orders.index') }}">Orders</a> / {{ $order->ref }}</div>
    <h1>Order {{ $order->ref }}</h1>
    <p>Placed {{ $order->created_at->format('l, j M Y \a\t g:i A') }} · {{ $order->created_at->diffForHumans() }}</p>
  </div>
  <div class="actions">
    @include('admin.partials.status', ['s' => $order->status])
    @include('admin.partials.status', ['p' => $order->payment_status])
    <a class="btn btn-wa" href="{{ $waCustomer }}" target="_blank" rel="noopener"><x-i n="wa"/>WhatsApp customer</a>
    <a class="btn btn-ghost" href="{{ route('admin.orders.invoice', $order) }}" target="_blank"><x-i n="print"/>Invoice</a>
  </div>
</div>

@if($order->items_tbc)
  <div class="note" style="margin-bottom:22px"><x-i n="alert"/><span><b>{{ $order->items_tbc }} {{ Str::plural('item', $order->items_tbc) }} need a price.</b> These were “price on request” products. Enter the agreed price below, then save to update the order total.</span></div>
@endif

<div class="grid g-main">
  <div class="stack">
    <section class="card">
      <div class="card-h"><h2>Items</h2><small>{{ $order->items_count }} {{ Str::plural('piece', $order->items_count) }}</small></div>
      <form method="post" action="{{ route('admin.orders.items', $order) }}">
        @csrf @method('PUT')
        <div class="table-wrap">
          <table class="t">
            <thead><tr><th>Product</th><th>Options</th><th style="width:150px">Unit price</th><th style="width:96px">Qty</th><th class="num">Line total</th></tr></thead>
            <tbody>
            @foreach($order->items as $item)
              <tr data-line>
                <td><div class="p-cell" style="min-width:200px">
                  @if($item->product?->image_url)<img class="thumb sm" src="{{ $item->product->image_url }}" alt="">@endif
                  <div><b>{{ $item->name }}</b><small>Design No {{ $item->design_no }}</small>
                    @if($item->product)<div><a class="link" style="font-size:12px" href="{{ route('admin.products.edit', $item->product) }}">View product</a></div>@endif</div>
                </div></td>
                <td>Size <b>{{ $item->size }}</b>@if($item->colour)<br><span class="muted">{{ $item->colour }}</span>@endif</td>
                <td><div class="prefix"><span>Rs.</span><input class="in sm" type="number" min="0" step="1" name="items[{{ $item->id }}][unit_price]" value="{{ $item->unit_price }}" placeholder="TBC" data-price aria-label="Unit price"></div></td>
                <td><input class="in sm" type="number" min="0" max="100" name="items[{{ $item->id }}][qty]" value="{{ $item->qty }}" data-qty aria-label="Quantity"></td>
                <td class="num"><b data-total>{{ $item->unit_price === null ? 'To confirm' : money($item->line_total) }}</b></td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        <div class="card-b" style="display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;align-items:flex-start;border-top:1px solid var(--line-2)">
          <div>
            <button class="btn btn-ghost sm" type="submit"><x-i n="check"/>Save prices &amp; quantities</button>
            <p class="muted" style="font-size:12px;margin-top:8px;max-width:300px">Set a quantity to 0 to remove a line.</p>
          </div>
          <div class="totals" style="flex:1">
            <div><span>Subtotal</span><b>{{ money($order->subtotal) }}</b></div>
            @if($order->items_tbc)<div><span>Items to price</span><b style="color:var(--amber)">{{ $order->items_tbc }}</b></div>@endif
            <div><span>Delivery</span><b>{{ money($order->delivery_fee) }}</b></div>
            @if($order->discount)<div><span>Discount</span><b>− {{ money($order->discount) }}</b></div>@endif
            <div class="grand"><span>Total (COD)</span><span>{{ money($order->total) }}</span></div>
          </div>
        </div>
      </form>
    </section>

    <section class="card">
      <div class="card-h"><h2>Update order</h2></div>
      <form class="card-b" method="post" action="{{ route('admin.orders.update', $order) }}">
        @csrf @method('PUT')
        <div class="form-grid">
          <div class="f"><label for="status">Order status</label>
            <select class="in" id="status" name="status">@foreach(config('store.order_statuses') as $k => $l)<option value="{{ $k }}" @selected(old('status', $order->status) === $k)>{{ $l }}</option>@endforeach</select></div>
          <div class="f"><label for="payment_status">Payment</label>
            <select class="in" id="payment_status" name="payment_status">@foreach(config('store.payment_statuses') as $k => $l)<option value="{{ $k }}" @selected(old('payment_status', $order->payment_status) === $k)>{{ $l }}</option>@endforeach</select></div>
          <div class="f"><label for="delivery_fee">Delivery fee</label><div class="prefix"><span>Rs.</span><input class="in" id="delivery_fee" type="number" min="0" name="delivery_fee" value="{{ old('delivery_fee', $order->delivery_fee) }}"></div></div>
          <div class="f"><label for="discount">Discount</label><div class="prefix"><span>Rs.</span><input class="in" id="discount" type="number" min="0" name="discount" value="{{ old('discount', $order->discount) }}"></div></div>
          <div class="f full"><label for="tracking_no">Courier tracking number</label><input class="in" id="tracking_no" name="tracking_no" value="{{ old('tracking_no', $order->tracking_no) }}" placeholder="Optional"></div>
          <div class="f full"><label for="admin_notes">Internal notes</label><textarea class="in" id="admin_notes" name="admin_notes" rows="3" placeholder="Only visible to your team">{{ old('admin_notes', $order->admin_notes) }}</textarea></div>
        </div>
        <div class="actions" style="margin-top:18px;justify-content:space-between">
          <button class="btn btn-primary" type="submit"><x-i n="check"/>Save changes</button>
        </div>
      </form>
    </section>
  </div>

  <div class="stack">
    <section class="card">
      <div class="card-h"><h2>Progress</h2></div>
      <div class="card-b">
        @if(in_array($order->status, ['cancelled', 'returned'], true))
          <div class="note" style="background:var(--slate-50);border-color:#dfe2e7;color:var(--slate)"><x-i n="alert"/>This order was {{ strtolower($order->status_label) }}.</div>
        @else
          <div class="timeline">
            @foreach($flow as $i => $st)
              <div class="{{ $idx !== false && $i <= $idx ? 'done' : '' }} {{ $i === $idx ? 'cur' : '' }}"><i></i>{{ config('store.order_statuses.'.$st) }}</div>
            @endforeach
          </div>
        @endif
      </div>
    </section>

    <section class="card">
      <div class="card-h"><h2>Customer</h2>@if($order->customer)<a class="link" href="{{ route('admin.customers.show', $order->customer) }}">Profile <x-i n="arrow"/></a>@endif</div>
      <div class="card-b">
        <div class="who" style="margin-bottom:18px"><span class="avatar lg">{{ strtoupper(mb_substr($order->name, 0, 1)) }}</span><div><b style="font-size:16px">{{ $order->name }}</b><small>{{ $history->count() ? ($history->count() + 1).' orders' : 'First order' }}</small></div></div>
        <dl class="dl">
          <div><dt>Phone</dt><dd><a class="link" href="tel:{{ $order->phone }}">{{ $order->phone }}</a> <button type="button" class="btn btn-ghost xs icon-only" data-copy="{{ $order->phone }}" aria-label="Copy phone"><x-i n="copy"/></button></dd></div>
          @if($order->alt_phone)<div><dt>Alt. phone</dt><dd>{{ $order->alt_phone }}</dd></div>@endif
          <div><dt>Address</dt><dd>{{ $order->address }}<br>{{ $order->city }}, {{ $order->district }}</dd></div>
          @if($order->notes)<div><dt>Order notes</dt><dd style="font-weight:400">{{ $order->notes }}</dd></div>@endif
          <div><dt>Payment</dt><dd>Cash on delivery</dd></div>
          @if($order->tracking_no)<div><dt>Tracking</dt><dd>{{ $order->tracking_no }}</dd></div>@endif
        </dl>
        <button type="button" class="btn btn-ghost sm" style="margin-top:16px;width:100%" data-copy="{{ $order->name }}&#10;{{ $order->phone }}&#10;{{ $order->address }}, {{ $order->city }}, {{ $order->district }}"><x-i n="copy"/>Copy delivery label</button>
      </div>
    </section>

    @if($history->isNotEmpty())
    <section class="card">
      <div class="card-h"><h2>Previous orders</h2></div>
      <div class="card-b tight"><div class="mini-list">
        @foreach($history as $h)
          <a href="{{ route('admin.orders.show', $h) }}"><div><b style="font-weight:600">{{ $h->ref }}</b><br><small class="muted" style="color:var(--muted)">{{ $h->created_at->format('M j, Y') }}</small></div>
            <div class="r">@include('admin.partials.status', ['s' => $h->status])<small style="display:block;margin-top:4px">{{ money($h->total) }}</small></div></a>
        @endforeach
      </div></div>
    </section>
    @endif

    <section class="card">
      <div class="card-h"><h2>Danger zone</h2></div>
      <div class="card-b">
        <form method="post" action="{{ route('admin.orders.destroy', $order) }}" data-confirm="Delete order {{ $order->ref }} permanently? This cannot be undone. Tip: set the status to Cancelled instead to keep a record.">
          @csrf @method('DELETE')
          <button class="btn btn-danger sm" type="submit" style="width:100%"><x-i n="trash"/>Delete order</button>
        </form>
      </div>
    </section>
  </div>
</div>
@endsection
