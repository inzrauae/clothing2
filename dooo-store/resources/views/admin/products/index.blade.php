@extends('layouts.admin')
@section('title', 'Products')

@section('content')
<div class="page-head">
  <div><div class="crumb">Catalogue</div><h1>Products</h1><p>Everything shown in your online shop. Hidden products stay saved but don't appear on the website.</p></div>
  <div class="actions"><a class="btn btn-primary" href="{{ route('admin.products.create') }}"><x-i n="plus"/>Add product</a></div>
</div>

@php($st = request('status'))
<nav class="tabs" aria-label="Filter products">
  @foreach(['' => 'All', 'active' => 'Live', 'hidden' => 'Hidden', 'signature' => 'Signature', 'noprice' => 'Price on request', 'out' => 'Out of stock'] as $k => $l)
    <a href="{{ route('admin.products.index', array_filter(array_merge(request()->except('page', 'status'), ['status' => $k]))) }}" class="{{ (string) $st === (string) $k ? 'on' : '' }}">{{ $l }}</a>
  @endforeach
</nav>

<section class="card">
  <form class="filters" method="get">
    @if($st)<input type="hidden" name="status" value="{{ $st }}">@endif
    <div class="search"><x-i n="search"/><input class="in" type="search" name="q" value="{{ request('q') }}" placeholder="Name or design no" aria-label="Search products"></div>
    <select class="in auto" name="category" data-autosubmit aria-label="Category">
      <option value="">All categories</option>
      @foreach($categories as $c)<option value="{{ $c->id }}" @selected((string) request('category') === (string) $c->id)>{{ $c->name }}</option>@endforeach
    </select>
    <button class="btn btn-primary sm" type="submit">Search</button>
    @if(request()->hasAny(['q', 'category']))<a class="link" href="{{ route('admin.products.index', $st ? ['status' => $st] : []) }}">Clear</a>@endif
  </form>

  <div class="table-wrap">
    <table class="t">
      <thead><tr><th>Product</th><th>Categories</th><th class="num">Price</th><th class="num">Sold</th><th>Status</th><th class="num">Actions</th></tr></thead>
      <tbody>
      @forelse($products as $p)
        <tr>
          <td><a class="p-cell" href="{{ route('admin.products.edit', $p) }}">
            @if($p->image_url)<img class="thumb" src="{{ $p->image_url }}" alt="" loading="lazy">@else<span class="thumb" style="display:grid;place-items:center;color:var(--faint)"><x-i n="image"/></span>@endif
            <div><b>{{ $p->name }}</b><small>Design No {{ $p->design_no }}{{ $p->tag ? ' · '.$p->tag : '' }}</small></div>
          </a></td>
          <td><div class="tags">@forelse($p->categories as $c)<span class="badge plain s-returned">{{ $c->name }}</span>@empty<span class="muted">—</span>@endforelse</div></td>
          <td class="num">
            @if($p->price === null)<span class="badge s-tbc plain">On request</span>
            @else<b>{{ $p->price_from ? 'From ' : '' }}{{ money($p->price) }}</b>
              @if($p->price_large)<div class="muted">{{ money($p->price_large) }} for 38+</div>@endif
              @if($p->compare_price)<div class="muted"><s>{{ money($p->compare_price) }}</s></div>@endif
            @endif
          </td>
          <td class="num">{{ (int) $p->sold }}</td>
          <td><div class="tags">
            <span class="badge {{ $p->is_active ? 's-active' : 's-hidden' }}">{{ $p->is_active ? 'Live' : 'Hidden' }}</span>
            @if($p->is_signature)<span class="badge s-signature plain">Signature</span>@endif
            @unless($p->in_stock)<span class="badge s-out plain">Out of stock</span>@endunless
          </div></td>
          <td class="num"><div class="actions" style="justify-content:flex-end;flex-wrap:nowrap">
            <form method="post" action="{{ route('admin.products.toggle', $p) }}">@csrf @method('PATCH')
              <button class="btn btn-ghost xs icon-only" type="submit" title="{{ $p->is_active ? 'Hide from shop' : 'Show in shop' }}" aria-label="{{ $p->is_active ? 'Hide from shop' : 'Show in shop' }}"><x-i :n="$p->is_active ? 'eye' : 'eye-off'"/></button></form>
            <a class="btn btn-ghost xs icon-only" href="{{ url('product') }}?id={{ urlencode($p->design_no) }}" target="_blank" rel="noopener" title="View in shop" aria-label="View in shop"><x-i n="external"/></a>
            <a class="btn btn-ghost xs" href="{{ route('admin.products.edit', $p) }}"><x-i n="edit"/>Edit</a>
          </div></td>
        </tr>
      @empty
        <tr><td colspan="6"><div class="empty"><x-i n="products"/><b>No products found</b><a class="link" href="{{ route('admin.products.create') }}">Add your first product</a></div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $products->links() }}
</section>
@endsection
