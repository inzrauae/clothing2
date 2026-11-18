<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0f4a32">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ setting('store_name') }}">
<meta name="format-detection" content="telephone=no">
<title>@yield('title', setting('store_name').' — '.setting('tagline'))</title>
<meta name="description" content="@yield('description', setting('meta_description'))">
@hasSection('noindex')<meta name="robots" content="noindex">@endif
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icon-192.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/icon-180.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ $assetVersion }}">
<link rel="stylesheet" href="{{ asset('assets/css/store.css') }}?v={{ $assetVersion }}">
</head>
<body data-page="@yield('page')">

@yield('content')

<script>window.DOOO = @json($catalog);</script>
<script src="{{ asset('assets/js/app.js') }}?v={{ $assetVersion }}"></script>
@stack('scripts')
</body>
</html>
