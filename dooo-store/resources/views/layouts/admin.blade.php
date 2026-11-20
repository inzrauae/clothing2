<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') · {{ setting('store_name') }} Admin</title>
<link rel="icon" type="image/png" href="{{ asset('images/icon-192.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&family=Manrope:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('admin-assets/admin.css') }}?v={{ $assetVersion }}">
@stack('head')
</head>
<body>
@include('admin.partials.icons')
@php($route = request()->route()?->getName() ?? '')
<div class="shell">
  <aside class="side" aria-label="Admin navigation">
    <a class="brand" href="{{ route('admin.dashboard') }}"><b>{{ setting('store_name') }}<sup>®</sup></b><span>Admin</span></a>
    <nav class="nav">
      <a href="{{ route('admin.dashboard') }}" class="{{ $route === 'admin.dashboard' ? 'on' : '' }}"><x-i n="dashboard"/>Dashboard</a>
      <div class="nav-label">Sales</div>
      <a href="{{ route('admin.orders.index') }}" class="{{ str_starts_with($route, 'admin.orders') ? 'on' : '' }}"><x-i n="orders"/>Orders @if($nav['pending'])<span class="pill">{{ $nav['pending'] }}</span>@endif</a>
      <a href="{{ route('admin.customers.index') }}" class="{{ str_starts_with($route, 'admin.customers') ? 'on' : '' }}"><x-i n="customers"/>Customers</a>
      <a href="{{ route('admin.messages.index') }}" class="{{ str_starts_with($route, 'admin.messages') ? 'on' : '' }}"><x-i n="messages"/>Messages @if($nav['unread'])<span class="pill red">{{ $nav['unread'] }}</span>@endif</a>
      <div class="nav-label">Catalogue</div>
      <a href="{{ route('admin.products.index') }}" class="{{ str_starts_with($route, 'admin.products') ? 'on' : '' }}"><x-i n="products"/>Products</a>
      <a href="{{ route('admin.categories.index') }}" class="{{ str_starts_with($route, 'admin.categories') ? 'on' : '' }}"><x-i n="categories"/>Categories</a>
      @can('manage')
        <div class="nav-label">Store</div>
        <a href="{{ route('admin.settings') }}" class="{{ str_starts_with($route, 'admin.settings') ? 'on' : '' }}"><x-i n="settings"/>Settings</a>
        <a href="{{ route('admin.users.index') }}" class="{{ str_starts_with($route, 'admin.users') ? 'on' : '' }}"><x-i n="team"/>Team</a>
      @endcan
    </nav>
    <div class="side-foot">
      <a class="store-link" href="{{ route('home') }}" target="_blank" rel="noopener"><x-i n="store"/>View website <x-i n="external" style="width:14px;height:14px"/></a>
    </div>
  </aside>
  <div class="scrim"></div>

  <div class="main">
    <header class="top">
      <button class="burger" type="button" aria-label="Open menu"><x-i n="menu"/></button>
      <form class="top-search" action="{{ route('admin.orders.index') }}" method="get" role="search">
        <x-i n="search"/>
        <input type="search" name="q" placeholder="Search orders by ref, name or phone…" value="{{ $route === 'admin.orders.index' ? request('q') : '' }}" aria-label="Search orders">
      </form>
      <div class="top-right">
        <a class="icon-btn" href="{{ route('admin.messages.index', ['filter' => 'unread']) }}" aria-label="Unread messages" title="Messages">
          <x-i n="messages"/>@if($nav['unread'])<span class="dot"></span>@endif
        </a>
        <a class="icon-btn" href="{{ route('admin.products.create') }}" aria-label="Add product" title="Add product"><x-i n="plus"/></a>
        <div class="user">
          <button type="button" data-menu="userMenu" aria-haspopup="true" aria-expanded="false">
            <span class="avatar">{{ auth()->user()->initials }}</span>
            <span class="user-meta"><b>{{ auth()->user()->name }}</b><small>{{ auth()->user()->role }}</small></span>
          </button>
          <div class="menu" id="userMenu">
            <a href="{{ route('admin.profile') }}"><x-i n="user"/>My profile</a>
            @can('manage')<a href="{{ route('admin.settings') }}"><x-i n="settings"/>Store settings</a>@endcan
            <a href="{{ route('home') }}" target="_blank" rel="noopener"><x-i n="store"/>View website</a>
            <hr>
            <form method="post" action="{{ route('admin.logout') }}">@csrf<button type="submit"><x-i n="logout"/>Sign out</button></form>
          </div>
        </div>
      </div>
    </header>

    <main class="content">
      @if(session('ok'))
        <div class="flash ok" role="status"><x-i n="check-circle"/><span>{{ session('ok') }}</span><button type="button" data-dismiss aria-label="Dismiss"><x-i n="x"/></button></div>
      @endif
      @if($errors->any())
        <div class="flash bad" role="alert"><x-i n="alert"/>
          <div>@if($errors->count() === 1){{ $errors->first() }}@else<b>Please fix the following:</b><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif</div>
          <button type="button" data-dismiss aria-label="Dismiss"><x-i n="x"/></button>
        </div>
      @endif
      @yield('content')
    </main>
  </div>
</div>

<dialog class="modal" id="confirmDlg">
  <div class="modal-h"><h3>Are you sure?</h3><button type="button" class="btn btn-ghost xs icon-only" data-close-dialog aria-label="Close"><x-i n="x"/></button></div>
  <div class="modal-b"><p id="confirmText"></p></div>
  <div class="modal-f"><button type="button" class="btn btn-ghost sm" data-close-dialog>Cancel</button><button type="button" class="btn btn-danger sm" id="confirmOk">Delete</button></div>
</dialog>

@stack('scripts')
<script src="{{ asset('admin-assets/admin.js') }}?v={{ $assetVersion }}"></script>
</body>
</html>
