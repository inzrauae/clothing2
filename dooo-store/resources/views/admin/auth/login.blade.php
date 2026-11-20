<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Sign in · {{ setting('store_name') }} Admin</title>
<link rel="icon" type="image/png" href="{{ asset('images/icon-192.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&family=Manrope:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('admin-assets/admin.css') }}?v={{ config('app.asset_version') }}">
</head>
<body>
@include('admin.partials.icons')
<div class="auth">
  <section class="auth-art">
    <div class="logo">{{ setting('store_name') }}<small>{{ setting('tagline') }}</small></div>
    <div>
      <h2>Run your boutique <em>beautifully.</em></h2>
      <p>Orders, products, customers and messages — everything for your store in one elegant place.</p>
    </div>
    <div class="auth-feats">
      <span><x-i n="orders"/>Track every order from pending to delivered</span>
      <span><x-i n="products"/>Add designs, prices and photos in seconds</span>
      <span><x-i n="trend"/>See sales and best-sellers at a glance</span>
    </div>
  </section>
  <section class="auth-form">
    <div class="auth-card">
      <h1>Welcome back</h1>
      <p>Sign in to your {{ setting('store_name') }} admin dashboard.</p>
      @if(session('ok'))<div class="flash ok"><x-i n="check-circle"/><span>{{ session('ok') }}</span></div>@endif
      @if($errors->any())<div class="flash bad" role="alert"><x-i n="alert"/><span>{{ $errors->first() }}</span></div>@endif
      <form method="post" action="{{ route('admin.login.attempt') }}">
        @csrf
        <div class="f">
          <label for="email">Email address</label>
          <input class="in" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        </div>
        <div class="f">
          <label for="password">Password</label>
          <div class="pw">
            <input class="in" id="password" name="password" type="password" autocomplete="current-password" required>
            <button type="button" aria-label="Show password"><x-i n="eye"/></button>
          </div>
        </div>
        <label class="switch"><input type="checkbox" name="remember" value="1" checked><span class="tr"></span><span>Keep me signed in</span></label>
        <button class="btn btn-primary" type="submit">Sign in <x-i n="arrow"/></button>
      </form>
      <p style="margin-top:26px;font-size:13px;color:var(--muted);text-align:center"><a class="link" href="{{ route('home') }}"><x-i n="back"/>Back to the website</a></p>
    </div>
  </section>
</div>
<script src="{{ asset('admin-assets/admin.js') }}?v={{ config('app.asset_version') }}"></script>
</body>
</html>
