{{-- Status badge: @include('admin.partials.status', ['s' => $order->status]) or ['p' => $order->payment_status] --}}
@isset($s)<span class="badge s-{{ $s }}">{{ config('store.order_statuses.'.$s, ucfirst($s)) }}</span>@endisset
@isset($p)<span class="badge s-{{ $p }}">{{ config('store.payment_statuses.'.$p, ucfirst($p)) }}</span>@endisset
