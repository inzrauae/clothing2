@extends('layouts.admin')
@section('title', 'Settings')

@php
  $v = fn ($k) => old($k, $s[$k] ?? '');
  $field = function ($k, $label, $hint = '', $type = 'text', $attrs = '') use ($v, $errors) {
      $bad = $errors->has($k) ? ' bad' : '';
      $html = '<div class="f'.$bad.'"><label for="'.$k.'">'.e($label).'</label><input class="in" id="'.$k.'" name="'.$k.'" type="'.$type.'" value="'.e($v($k)).'" '.$attrs.'>';
      if ($hint) $html .= '<span class="hint">'.e($hint).'</span>';
      if ($errors->has($k)) $html .= '<span class="err">'.e($errors->first($k)).'</span>';
      return $html.'</div>';
  };
@endphp

@section('content')
<div class="page-head">
  <div><div class="crumb">Store</div><h1>Settings</h1><p>These details appear across your website: header, footer, contact page, invoices and WhatsApp links.</p></div>
</div>

<form method="post" action="{{ route('admin.settings.update') }}" novalidate>
  @csrf @method('PUT')
  <div class="grid g-2">
    <section class="card">
      <div class="card-h"><h2>Brand</h2></div>
      <div class="card-b" style="display:grid;gap:16px">
        {!! $field('store_name', 'Store name', '', 'text', 'required maxlength="60"') !!}
        {!! $field('tagline', 'Tagline', 'Shown under the logo.') !!}
        {!! $field('company', 'Company name', 'Used in the footer and on invoices.') !!}
        {!! $field('group', 'Group / parent company') !!}
        <div class="f {{ $errors->has('meta_description') ? 'bad' : '' }}"><label for="meta_description">Search engine description</label>
          <textarea class="in" id="meta_description" name="meta_description" rows="3" maxlength="300">{{ $v('meta_description') }}</textarea>
          <span class="hint">The short summary Google shows under your site name (max 300 characters).</span></div>
      </div>
    </section>

    <section class="card">
      <div class="card-h"><h2>Contact &amp; orders</h2></div>
      <div class="card-b" style="display:grid;gap:16px">
        <div class="form-grid">
          {!! $field('whatsapp', 'WhatsApp number', 'International format, digits only: 94761400696', 'text', 'required inputmode="numeric"') !!}
          {!! $field('whatsapp_display', 'WhatsApp (as displayed)', 'e.g. +94 76 140 0696', 'text', 'required') !!}
          {!! $field('phone', 'Landline (for dialling)', 'e.g. +94342121312', 'tel') !!}
          {!! $field('phone_display', 'Landline (as displayed)', 'e.g. +94 342 121 312') !!}
        </div>
        {!! $field('email', 'Email', '', 'email') !!}
        {!! $field('address', 'Address') !!}
        <div class="f {{ $errors->has('delivery_fee') ? 'bad' : '' }}"><label for="delivery_fee">Default delivery fee</label>
          <div class="prefix"><span>Rs.</span><input class="in" id="delivery_fee" name="delivery_fee" type="number" min="0" value="{{ $v('delivery_fee') }}"></div>
          <span class="hint">Added to new website orders. Use 0 if you confirm delivery charges with the customer. You can change it on each order.</span></div>
      </div>
    </section>

    <section class="card">
      <div class="card-h"><h2>Social media</h2></div>
      <div class="card-b" style="display:grid;gap:16px">
        {!! $field('facebook', 'Facebook page URL', '', 'url') !!}
        {!! $field('instagram', 'Instagram URL', '', 'url') !!}
        {!! $field('tiktok', 'TikTok URL', '', 'url') !!}
        {!! $field('youtube', 'YouTube URL', '', 'url') !!}
      </div>
    </section>

    <section class="card">
      <div class="card-h"><h2>Announcement bar</h2><small>The scrolling strip at the top of every page</small></div>
      <div class="card-b" style="display:grid;gap:16px">
        <div class="f {{ $errors->has('announcement') ? 'bad' : '' }}"><label for="announcement">Messages (one per line)</label>
          <textarea class="in" id="announcement" name="announcement" rows="8" maxlength="1000">{{ $v('announcement') }}</textarea>
          <span class="hint">Keep each line short, e.g. “Free delivery this weekend”.</span></div>
      </div>
    </section>
  </div>

  <div class="card" style="position:sticky;bottom:12px;margin-top:22px;z-index:5">
    <div class="card-b" style="display:flex;justify-content:flex-end;gap:12px;padding:14px 22px">
      <button class="btn btn-primary" type="submit"><x-i n="check"/>Save settings</button>
    </div>
  </div>
</form>
@endsection
