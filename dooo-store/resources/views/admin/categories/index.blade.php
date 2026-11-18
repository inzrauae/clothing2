@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
<div class="page-head">
  <div><div class="crumb">Catalogue</div><h1>Categories</h1><p>Categories become the filter buttons in your shop (Trousers, Linen, Frocks…).</p></div>
  <div class="actions"><button class="btn btn-primary" type="button" data-open="newCat"><x-i n="plus"/>Add category</button></div>
</div>

<section class="card">
  <div class="table-wrap">
    <table class="t">
      <thead><tr><th>Category</th><th>Description</th><th class="num">Products</th><th class="num">Order</th><th>Status</th><th class="num">Actions</th></tr></thead>
      <tbody>
      @forelse($categories as $c)
        <tr>
          <td><b>{{ $c->name }}</b><div class="muted">/shop?cat={{ $c->name }}</div></td>
          <td class="muted" style="max-width:360px">{{ Str::limit($c->description, 110) ?: '—' }}</td>
          <td class="num"><a class="link" href="{{ route('admin.products.index', ['category' => $c->id]) }}">{{ $c->products_count }}</a></td>
          <td class="num">{{ $c->sort_order }}</td>
          <td><span class="badge {{ $c->is_active ? 's-active' : 's-hidden' }}">{{ $c->is_active ? 'Visible' : 'Hidden' }}</span></td>
          <td class="num"><div class="actions" style="justify-content:flex-end;flex-wrap:nowrap">
            <button class="btn btn-ghost xs" type="button" data-open="cat{{ $c->id }}"><x-i n="edit"/>Edit</button>
            <form method="post" action="{{ route('admin.categories.destroy', $c) }}" data-confirm="Delete the “{{ $c->name }}” category? Its {{ $c->products_count }} products will be kept.">@csrf @method('DELETE')
              <button class="btn btn-danger xs icon-only" type="submit" aria-label="Delete {{ $c->name }}"><x-i n="trash"/></button></form>
          </div></td>
        </tr>
      @empty
        <tr><td colspan="6"><div class="empty"><x-i n="categories"/><b>No categories yet</b>Add one to group your products.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</section>

@foreach($categories->push(null) as $c)
  <dialog class="modal" id="{{ $c ? 'cat'.$c->id : 'newCat' }}">
    <form method="post" action="{{ $c ? route('admin.categories.update', $c) : route('admin.categories.store') }}">
      @csrf @if($c) @method('PUT') @endif
      <div class="modal-h"><h3>{{ $c ? 'Edit '.$c->name : 'New category' }}</h3><button type="button" class="btn btn-ghost xs icon-only" data-close-dialog aria-label="Close"><x-i n="x"/></button></div>
      <div class="modal-b" style="display:grid;gap:16px">
        <div class="f"><label>Name <i>*</i></label><input class="in" name="name" value="{{ $c?->name }}" required maxlength="80"></div>
        <div class="f"><label>Description</label><textarea class="in" name="description" rows="3" maxlength="500">{{ $c?->description }}</textarea><span class="hint">Shown under the title when customers filter the shop.</span></div>
        <div class="f"><label>Display order</label><input class="in" type="number" min="0" name="sort_order" value="{{ $c?->sort_order ?? 0 }}"></div>
        <label class="switch"><input type="checkbox" name="is_active" value="1" @checked($c ? $c->is_active : true)><span class="tr"></span><span><b>Visible in shop</b></span></label>
      </div>
      <div class="modal-f"><button type="button" class="btn btn-ghost sm" data-close-dialog>Cancel</button><button class="btn btn-primary sm" type="submit">{{ $c ? 'Save' : 'Add category' }}</button></div>
    </form>
  </dialog>
@endforeach
@endsection
