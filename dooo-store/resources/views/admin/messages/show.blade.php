@extends('layouts.admin')
@section('title', 'Message from '.$message->name)

@section('content')
<div class="page-head">
  <div><div class="crumb"><a href="{{ route('admin.messages.index') }}">Messages</a> / {{ $message->topic ?: 'Enquiry' }}</div><h1>{{ $message->name }}</h1><p>{{ $message->created_at->format('l, j M Y \a\t g:i A') }} · {{ $message->created_at->diffForHumans() }}</p></div>
  <div class="actions">
    @if($message->phone)
      <a class="btn btn-wa" href="{{ wa_link('Hi '.$message->name.'! Thank you for contacting '.setting('store_name').'. ', wa_number($message->phone)) }}" target="_blank" rel="noopener"><x-i n="wa"/>Reply on WhatsApp</a>
      <a class="btn btn-ghost" href="tel:{{ $message->phone }}"><x-i n="phone"/>Call</a>
    @endif
  </div>
</div>

<div class="grid g-main">
  <section class="card">
    <div class="card-h"><div class="who"><span class="avatar">{{ strtoupper(mb_substr($message->name, 0, 1)) }}</span><div><b>{{ $message->name }}</b><small>{{ $message->phone ?: 'No phone given' }}</small></div></div>@if($message->topic)<span class="badge plain s-returned">{{ $message->topic }}</span>@endif</div>
    <div class="card-b"><div class="bubble">{{ $message->message }}</div></div>
  </section>
  <div class="stack">
    <section class="card">
      <div class="card-h"><h2>Details</h2></div>
      <div class="card-b">
        <dl class="dl">
          <div><dt>Topic</dt><dd>{{ $message->topic ?: '—' }}</dd></div>
          <div><dt>Phone</dt><dd>{{ $message->phone ?: '—' }}</dd></div>
          <div><dt>Order / design</dt><dd>
            @php($linked = $message->order_ref ? \App\Models\Order::where('ref', strtoupper(trim($message->order_ref)))->first() : null)
            @if($linked)<a class="link" href="{{ route('admin.orders.show', $linked) }}">{{ $linked->ref }}</a>@else{{ $message->order_ref ?: '—' }}@endif
          </dd></div>
          <div><dt>Received</dt><dd>{{ $message->created_at->format('j M Y, g:i A') }}</dd></div>
        </dl>
      </div>
    </section>
    <section class="card">
      <div class="card-b" style="display:grid;gap:10px">
        <form method="post" action="{{ route('admin.messages.unread', $message) }}">@csrf @method('PATCH')<button class="btn btn-ghost" type="submit" style="width:100%"><x-i n="mail"/>Mark as unread</button></form>
        <form method="post" action="{{ route('admin.messages.destroy', $message) }}" data-confirm="Delete this message from {{ $message->name }}?">@csrf @method('DELETE')<button class="btn btn-danger" type="submit" style="width:100%"><x-i n="trash"/>Delete message</button></form>
      </div>
    </section>
  </div>
</div>
@endsection
