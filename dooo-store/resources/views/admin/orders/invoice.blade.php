<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Invoice {{ $order->ref }} · {{ setting('store_name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,500&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('admin-assets/admin.css') }}?v={{ config('app.asset_version') }}">
</head>
<body>
@include('admin.partials.icons')
<div class="inv-tools">
  <a class="btn btn-ghost sm" href="{{ route('admin.orders.show', $order) }}"><x-i n="back"/>Back to order</a>
  <button class="btn btn-primary sm" type="button" onclick="window.print()"><x-i n="print"/>Print</button>
</div>
<article class="invoice">
  <header class="inv-head">
    <div>
      <div class="logo">{{ setting('store_name') }}<small>{{ setting('tagline') }}</small></div>
      <p style="margin-top:14px;color:var(--muted);font-size:13px;line-height:1.6">{{ setting('company') }}<br>{{ setting('address') }}<br>{{ setting('phone_display') }} · {{ setting('whatsapp_display') }}<br>{{ setting('email') }}</p>
    </div>
    <div style="text-align:right">
      <div style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;color:var(--gold-ink)">Invoice</div>
      <div style="font-size:26px;font-weight:600;color:var(--green-d)">{{ $order->ref }}</div>
      <p style="color:var(--muted);font-size:13px;margin-top:6px">Date: {{ $order->created_at->format('j M Y') }}<br>Payment: Cash on delivery<br>Status: {{ $order->status_label }}</p>
    </div>
  </header>

  <div style="display:flex;justify-content:space-between;gap:30px;flex-wrap:wrap;margin-bottom:26px">
    <div>
      <div style="font-size:11.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);margin-bottom:6px">Deliver to</div>
      <b style="font-size:16px">{{ $order->name }}</b>
      <p style="font-size:14px;line-height:1.6">{{ $order->address }}<br>{{ $order->city }}, {{ $order->district }}<br>{{ $order->phone }}@if($order->alt_phone) / {{ $order->alt_phone }}@endif</p>
    </div>
    @if($order->tracking_no)
    <div><div style="font-size:11.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);margin-bottom:6px">Tracking no</div><b>{{ $order->tracking_no }}</b></div>
    @endif
  </div>

  <table class="t" style="border:1px solid var(--line);border-radius:12px;overflow:hidden">
    <thead><tr><th>Item</th><th>Size / colour</th><th class="num">Qty</th><th class="num">Unit</th><th class="num">Amount</th></tr></thead>
    <tbody>
    @foreach($order->items as $item)
      <tr>
        <td><b>{{ $item->name }}</b><div class="muted">Design No {{ $item->design_no }}</div></td>
        <td>{{ $item->size }}{{ $item->colour ? ' · '.$item->colour : '' }}</td>
        <td class="num">{{ $item->qty }}</td>
        <td class="num">{{ $item->unit_price === null ? 'TBC' : money($item->unit_price) }}</td>
        <td class="num">{{ $item->line_total === null ? 'TBC' : money($item->line_total) }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>

  <div class="totals" style="margin-top:22px">
    <div><span>Subtotal</span><b>{{ money($order->subtotal) }}</b></div>
    <div><span>Delivery</span><b>{{ money($order->delivery_fee) }}</b></div>
    @if($order->discount)<div><span>Discount</span><b>− {{ money($order->discount) }}</b></div>@endif
    <div class="grand"><span>Amount due on delivery</span><span>{{ money($order->total) }}</span></div>
    @if($order->items_tbc)<div style="font-size:12px;color:var(--amber)">{{ $order->items_tbc }} item(s) still to be priced.</div>@endif
  </div>

  <footer style="margin-top:40px;padding-top:20px;border-top:1px solid var(--line);text-align:center;color:var(--muted);font-size:13px">
    Thank you for shopping with {{ setting('store_name') }}. Please open and inspect your order before payment.<br>
    Exchanges are available for size issues or product defects. Questions? WhatsApp {{ setting('whatsapp_display') }}.
  </footer>
</article>
</body>
</html>
