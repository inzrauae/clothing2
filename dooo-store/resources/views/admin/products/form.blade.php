@extends('layouts.admin')
@section('title', $product->exists ? 'Edit '.$product->name : 'Add product')

@php
  $editing = $product->exists;
  $selectedCats = old('categories', $editing ? $product->categories->pluck('id')->all() : []);
  $selectedColours = old('colours', $product->colours ?? []);
  $bool = fn ($field, $default) => (bool) old($field, $editing ? $product->{$field} : $default);
  $err = fn ($f) => $errors->has($f) ? 'bad' : '';
@endphp

@section('content')
<div class="page-head">
  <div>
    <div class="crumb"><a href="{{ route('admin.products.index') }}">Products</a> / {{ $editing ? 'Design No '.$product->design_no : 'New' }}</div>
    <h1>{{ $editing ? $product->name : 'Add a product' }}</h1>
    <p>{{ $editing ? 'Changes appear on the website as soon as you save.' : 'Fill in the details below. Only the design number, name and size type are required.' }}</p>
  </div>
  @if($editing)
  <div class="actions"><a class="btn btn-ghost" href="{{ url('product') }}?id={{ urlencode($product->design_no) }}" target="_blank" rel="noopener"><x-i n="external"/>View in shop</a></div>
  @endif
</div>

<form method="post" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" novalidate>
  @csrf
  @if($editing) @method('PUT') @endif

  <div class="grid g-main">
    <div class="stack">
      <section class="card">
        <div class="card-h"><h2>Basic details</h2></div>
        <div class="card-b form-grid">
          <div class="f {{ $err('design_no') }}"><label for="design_no">Design number <i>*</i></label>
            <input class="in" id="design_no" name="design_no" value="{{ old('design_no', $product->design_no) }}" required maxlength="20">
            <span class="hint">Shown on the product and used in WhatsApp orders.</span>
            @error('design_no')<span class="err">{{ $message }}</span>@enderror</div>
          <div class="f {{ $err('tag') }}"><label for="tag">Badge / tag</label>
            <input class="in" id="tag" name="tag" value="{{ old('tag', $product->tag) }}" maxlength="30" placeholder="e.g. New, Bestseller" list="tagList">
            <datalist id="tagList"><option value="New"><option value="Bestseller"><option value="Office"><option value="Tailored"><option value="Sale"><option value="Limited"></datalist>
            @error('tag')<span class="err">{{ $message }}</span>@enderror</div>
          <div class="f full {{ $err('name') }}"><label for="name">Product name <i>*</i></label>
            <input class="in" id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="160">
            @error('name')<span class="err">{{ $message }}</span>@enderror</div>
          <div class="f full {{ $err('description') }}"><label for="description">Description</label>
            <textarea class="in" id="description" name="description" rows="4" maxlength="3000" placeholder="Fabric, fit and styling notes">{{ old('description', $product->description) }}</textarea>
            @error('description')<span class="err">{{ $message }}</span>@enderror</div>
        </div>
      </section>

      <section class="card">
        <div class="card-h"><h2>Pricing</h2><small>Leave the price empty to show “Price on request”</small></div>
        <div class="card-b form-grid">
          <div class="f {{ $err('price') }}"><label for="price">Price</label>
            <div class="prefix"><span>Rs.</span><input class="in" id="price" type="number" min="0" name="price" value="{{ old('price', $product->price) }}" placeholder="On request"></div>
            @error('price')<span class="err">{{ $message }}</span>@enderror</div>
          <div class="f {{ $err('compare_price') }}"><label for="compare_price">Was price (optional)</label>
            <div class="prefix"><span>Rs.</span><input class="in" id="compare_price" type="number" min="0" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}"></div>
            <span class="hint">Shown crossed out next to the price.</span>
            @error('compare_price')<span class="err">{{ $message }}</span>@enderror</div>
          <div class="f {{ $err('price_large') }}" data-waist-only><label for="price_large">Price for sizes 38″ and up</label>
            <div class="prefix"><span>Rs.</span><input class="in" id="price_large" type="number" min="0" name="price_large" value="{{ old('price_large', $product->price_large) }}" placeholder="Same as price"></div>
            @error('price_large')<span class="err">{{ $message }}</span>@enderror</div>
          <div class="f full"><label class="switch"><input type="checkbox" name="price_from" value="1" @checked($bool('price_from', false))><span class="tr"></span><span><b>Show as “From” price</b><small>Use when the price varies by option.</small></span></label></div>
        </div>
      </section>

      <section class="card">
        <div class="card-h"><h2>Sizes &amp; colours</h2></div>
        <div class="card-b form-grid">
          <div class="f {{ $err('size_type') }}"><label for="size_type">Size type <i>*</i></label>
            <select class="in" id="size_type" name="size_type">
              <option value="waist" @selected(old('size_type', $product->size_type) === 'waist')>Waist sizes · {{ implode(', ', config('store.sizes.waist')) }}</option>
              <option value="alpha" @selected(old('size_type', $product->size_type) === 'alpha')>Letter sizes · {{ implode(', ', config('store.sizes.alpha')) }}</option>
            </select>
            @error('size_type')<span class="err">{{ $message }}</span>@enderror</div>
          <div class="f"><label for="sort_order">Display order</label>
            <input class="in" id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order) }}">
            <span class="hint">Lower numbers appear first in the shop.</span></div>
          <div class="f full"><span class="lbl">Colours customers can choose</span>
            <div class="checks">
              @foreach(config('store.colours') as $name => $hex)
                <label class="chip-check"><input type="checkbox" name="colours[]" value="{{ $name }}" @checked(in_array($name, $selectedColours, true))><span><i style="background:{{ $hex }}"></i>{{ $name }}</span></label>
              @endforeach
            </div>
            <span class="hint">Choose none for “colour as pictured”.</span>
            @error('colours')<span class="err">{{ $message }}</span>@enderror</div>
        </div>
      </section>
    </div>

    <div class="stack">
      <section class="card">
        <div class="card-h"><h2>Photo</h2></div>
        <div class="card-b">
          <label class="drop {{ $err('image') }}">
            @if($product->image_url)
              <img src="{{ $product->image_url }}" alt="Current photo">
            @else
              <span class="ph"><x-i n="image"/><b>Upload a photo</b><small>JPG, PNG or WEBP · up to 5 MB</small></span>
            @endif
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" aria-label="Product photo">
          </label>
          @error('image')<p class="err-text" style="margin-top:8px">{{ $message }}</p>@enderror
          <p class="hint" style="font-size:12px;color:var(--muted);margin-top:10px">Tip: portrait photos (4:5) on a plain background look best. Click or drop an image to replace.</p>
          @if($product->image)
            <label class="switch" style="margin-top:10px"><input type="checkbox" id="remove_image" name="remove_image" value="1"><span class="tr"></span><span>Remove current photo</span></label>
          @endif
        </div>
      </section>

      <section class="card">
        <div class="card-h"><h2>Visibility</h2></div>
        <div class="card-b" style="display:grid;gap:14px">
          <label class="switch"><input type="checkbox" name="is_active" value="1" @checked($bool('is_active', true))><span class="tr"></span><span><b>Live in shop</b><small>Turn off to hide without deleting.</small></span></label>
          <label class="switch"><input type="checkbox" name="in_stock" value="1" @checked($bool('in_stock', true))><span class="tr"></span><span><b>In stock</b><small>For your records and reports.</small></span></label>
          <label class="switch"><input type="checkbox" name="is_signature" value="1" @checked($bool('is_signature', false))><span class="tr"></span><span><b>Signature piece</b><small>Featured in “The Atelier Portfolio” on the home page.</small></span></label>
        </div>
      </section>

      <section class="card">
        <div class="card-h"><h2>Categories</h2><a class="link" href="{{ route('admin.categories.index') }}">Manage</a></div>
        <div class="card-b">
          <div class="checks">
            @forelse($categories as $c)
              <label class="chip-check"><input type="checkbox" name="categories[]" value="{{ $c->id }}" @checked(in_array($c->id, array_map('intval', $selectedCats), true))><span>{{ $c->name }}</span></label>
            @empty
              <p class="muted">No categories yet.</p>
            @endforelse
          </div>
        </div>
      </section>
    </div>
  </div>

  <div class="card" style="position:sticky;bottom:12px;margin-top:22px;z-index:5">
    <div class="card-b" style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:14px 22px">
      <a class="btn btn-ghost" href="{{ route('admin.products.index') }}">Cancel</a>
      <button class="btn btn-primary" type="submit"><x-i n="check"/>{{ $editing ? 'Save changes' : 'Create product' }}</button>
    </div>
  </div>
</form>

@if($editing)
<section class="card" style="margin-top:22px">
  <div class="card-b" style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
    <div><b>Delete this product</b><p class="muted" style="color:var(--muted);font-size:13px">Past orders keep their details. Consider hiding it instead.</p></div>
    <form method="post" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Delete “{{ $product->name }}” permanently? Past orders will keep their details.">
      @csrf @method('DELETE')
      <button class="btn btn-danger" type="submit"><x-i n="trash"/>Delete product</button>
    </form>
  </div>
</section>
@endif
@endsection
