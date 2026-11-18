<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Install · dooo store</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('admin-assets/admin.css') }}?v={{ config('app.asset_version') }}">
</head>
<body>
@include('admin.partials.icons')
@php($allOk = $db['ok'] && collect($checks)->every(fn ($c) => $c[1]))
<div class="install">
  <div style="text-align:center;margin-bottom:30px">
    <div style="font-size:56px;font-weight:500;letter-spacing:-.03em;color:var(--green);line-height:1">dooo</div>
    <h1 style="font-family:var(--serif);font-size:36px;color:var(--green-d);margin-top:10px">Store installer</h1>
    <p style="color:var(--muted)">One-time setup: creates the database tables, loads your catalogue and your admin login.</p>
  </div>

  @if($errors->any())
    <div class="flash bad"><x-i n="alert"/><div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>
  @endif

  <section class="card" style="margin-bottom:22px">
    <div class="card-h"><h2>1 · Server check</h2>@if($allOk)<span class="badge s-active">All good</span>@else<span class="badge s-cancelled">Needs attention</span>@endif</div>
    <div class="card-b check-list">
      <div class="{{ $db['ok'] ? 'ok' : 'no' }}"><x-i :n="$db['ok'] ? 'check-circle' : 'alert'"/><span>Database</span><small>{{ $db['message'] }}</small></div>
      @foreach($checks as [$label, $ok, $note])
        <div class="{{ $ok ? 'ok' : 'no' }}"><x-i :n="$ok ? 'check-circle' : 'alert'"/><span>{{ $label }}</span><small>{{ $note }}</small></div>
      @endforeach
    </div>
  </section>

  <section class="card">
    <div class="card-h"><h2>2 · Create your owner account</h2></div>
    <form class="card-b" method="post" action="{{ route('install.run') }}" style="display:grid;gap:16px">
      @csrf
      <div class="form-grid">
        <div class="f"><label for="name">Your name</label><input class="in" id="name" name="name" value="{{ old('name') }}" required></div>
        <div class="f"><label for="email">Email (your login)</label><input class="in" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"></div>
        <div class="f"><label for="password">Password</label><input class="in" id="password" type="password" name="password" minlength="8" required autocomplete="new-password"><span class="hint">At least 8 characters.</span></div>
        <div class="f"><label for="password_confirmation">Confirm password</label><input class="in" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
      </div>
      <label class="switch"><input type="checkbox" name="demo" value="1"><span class="tr"></span><span><b>Add sample orders</b><small>Fills the dashboard with example orders and messages so you can explore. Leave off for a real launch.</small></span></label>
      <button class="btn btn-primary" type="submit" @disabled(! $allOk) style="height:48px">Install store <x-i n="arrow"/></button>
      @unless($allOk)<p class="err-text">Fix the items marked in red above (usually the database details in your .env file), then reload this page.</p>@endunless
    </form>
  </section>
</div>
</body>
</html>
