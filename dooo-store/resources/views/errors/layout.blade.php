<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>@yield('code') · @yield('title') · {{ setting('store_name', 'dooo') }}</title>
<link rel="icon" type="image/png" href="{{ asset('images/icon-192.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&family=Manrope:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap" rel="stylesheet">
<style>
  :root{--green:#0f4a32;--green-d:#0a3524;--gold:#c6a35a;--gold-ink:#8a6a2e;--ivory:#faf6ee;--muted:#5c5a53}
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;display:grid;place-items:center;padding:32px 16px;background:radial-gradient(700px 400px at 15% 10%,rgba(198,163,90,.18),transparent 60%),linear-gradient(120deg,#fbf8f1,#efe4cf);font-family:"Manrope",system-ui,sans-serif;color:#1f2420;text-align:center}
  .box{max-width:560px}
  .logo{font-family:"Jost",sans-serif;font-size:44px;font-weight:500;letter-spacing:-.02em;color:var(--green);text-decoration:none;display:inline-block;line-height:1}
  .logo small{display:block;font-family:"Playfair Display",Georgia,serif;font-style:italic;font-size:14px;letter-spacing:.12em;color:var(--gold-ink);margin-top:4px}
  .code{font-family:"Playfair Display",Georgia,serif;font-size:clamp(90px,22vw,150px);font-weight:600;line-height:1;color:var(--gold);margin:36px 0 6px}
  h1{font-family:"Playfair Display",Georgia,serif;font-size:clamp(30px,6vw,42px);font-weight:600;color:var(--green-d);line-height:1.15}
  p{color:var(--muted);font-size:16px;line-height:1.6;margin:14px auto 30px;max-width:440px}
  .actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
  a.btn{display:inline-flex;align-items:center;gap:8px;padding:14px 26px;font-size:13px;letter-spacing:.16em;text-transform:uppercase;text-decoration:none;font-weight:500}
  a.primary{background:var(--green);color:#e6cf96}
  a.primary:hover{background:var(--green-d)}
  a.ghost{border:1px solid var(--green);color:var(--green)}
  a.ghost:hover{background:var(--green);color:var(--ivory)}
</style>
</head>
<body>
  <main class="box">
    <a class="logo" href="{{ url('/') }}">{{ setting('store_name', 'dooo') }}<small>{{ setting('tagline', 'Fashionably Elegant') }}</small></a>
    <div class="code">@yield('code')</div>
    <h1>@yield('title')</h1>
    <p>@yield('message')</p>
    <div class="actions">
      @hasSection('actions')
        @yield('actions')
      @else
        <a class="btn primary" href="{{ url('/') }}">Back to the shop</a>
        <a class="btn ghost" href="{{ url('contact') }}">Contact us</a>
      @endif
    </div>
  </main>
</body>
</html>
