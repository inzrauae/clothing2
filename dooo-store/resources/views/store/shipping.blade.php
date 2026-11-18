@extends('layouts.store')

@section('page', 'shipping')
@section('title', 'Shipping, Returns & Exchange — dooo')
@section('description', 'Fast islandwide delivery across Sri Lanka, inspect before payment, easy returns and exchanges, and a money-back guarantee from dooo.')

@section('content')
<section class="page-hero">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>Shipping &amp; Returns</span></nav>
    <h1>Shipping, Return <em>&amp; Exchange</em></h1>
    <p class="lead">We are committed to providing a smooth and reliable shopping experience for every customer.</p>
  </div>
</section>

<!-- Shipping -->
<section id="shipping">
  <div class="wrap pol-grid">
    <div class="rv-up">
      <span class="eyebrow">Our Commitment</span>
      <h2 style="margin:14px 0 18px">Shop with <em style="color:var(--gold)">complete confidence.</em></h2>
      <p class="lead">From the moment you order to the moment it reaches your door, we make every step simple, safe and transparent.</p>
      <div class="safe">
        <svg><use href="#i-lock"/></svg>
        <div><b>No advance payment</b><p>You pay only after you've opened and checked your order. dooo will never ask for your card number, bank PIN or OTP.</p></div>
      </div>
    </div>
    <ul class="pol-list rv-up d1">
      <li><span><svg><use href="#i-truck"/></svg></span><div><b>Fast islandwide delivery</b><small>Delivered to your door, anywhere across Sri Lanka.</small></div></li>
      <li><span><svg><use href="#i-box"/></svg></span><div><b>Open &amp; inspect before payment</b><small>Check the fabric, fit and finish before you pay.</small></div></li>
      <li><span><svg><use href="#i-return"/></svg></span><div><b>Easy returns &amp; exchanges</b><small>Available for eligible items.</small></div></li>
      <li><span><svg><use href="#i-swap"/></svg></span><div><b>Exchanges for size or defects</b><small>Wrong size or a product defect? We'll exchange it.</small></div></li>
      <li><span><svg><use href="#i-headset"/></svg></span><div><b>Friendly customer support</b><small>Ready to assist you anytime.</small></div></li>
    </ul>
  </div>
</section>

<!-- Returns process -->
<section id="returns" style="background:var(--cream)">
  <div class="wrap">
    <div class="sec-head">
      <div><span class="eyebrow">Returns &amp; Exchanges</span><h2>How an exchange works</h2></div>
      <p class="lead">Simple, quick and handled personally by our team.</p>
    </div>
    <div class="ret-steps">
      <div class="rv-up"><b>Inspect on delivery</b><p>Open your parcel and check it before paying the courier.</p></div>
      <div class="rv-up d1"><b>Message us</b><p>Send your order details and a photo of the issue on WhatsApp.</p></div>
      <div class="rv-up d2"><b>We arrange it</b><p>Our team confirms eligibility and arranges your exchange or refund.</p></div>
      <div class="rv-up d3"><b>Enjoy the right fit</b><p>Receive your replacement in the right size, or your money back.</p></div>
    </div>
    <div style="margin-top:34px;display:flex;gap:14px;flex-wrap:wrap">
      <a class="btn btn-primary" href="{{ wa_link() }}?text=Hi%20dooo!%20I'd%20like%20to%20request%20an%20exchange%20or%20return." target="_blank" rel="noopener"><svg><use href="#i-wa"/></svg>Start an exchange</a>
      <a class="btn btn-ghost" href="{{ url('contact') }}#faq">Read the FAQ</a>
    </div>
  </div>
</section>

<!-- Money back -->
<section class="guarantee" id="guarantee">
  <div class="wrap">
    <div class="g-card rv-up">
      <div class="seal" aria-hidden="true">
        <svg class="ring" viewBox="0 0 300 300"><defs><path id="circ2" d="M150,150 m-128,0 a128,128 0 1,1 256,0 a128,128 0 1,1 -256,0"/></defs><text font-family="Jost" font-size="13" fill="#c6a35a"><textPath href="#circ2" textLength="800" lengthAdjust="spacing">MONEY BACK ✦ GUARANTEE ✦ SHOP WITH CONFIDENCE ✦</textPath></text></svg>
        <div class="seal-core"><div><svg><use href="#i-cash"/></svg><b>100%</b><small>Money back</small></div></div>
      </div>
      <div>
        <span class="eyebrow">Money Back Guarantee</span>
        <h2 style="margin-top:12px">Your satisfaction <em>means everything.</em></h2>
        <p>If you receive a damaged or incorrect item, we offer a hassle-free exchange or money-back guarantee for eligible purchases.</p>
        <p>Shop with confidence knowing that your order is protected with dooo.</p>
        <div class="tick">
          <span><svg><use href="#i-check"/></svg>Damaged items</span>
          <span><svg><use href="#i-check"/></svg>Incorrect items</span>
          <span><svg><use href="#i-check"/></svg>Hassle-free process</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Support -->
<section class="support-band" id="support">
  <div class="wrap support-grid">
    <div class="rv-up">
      <span class="eyebrow">24/7 Online Support</span>
      <h2 style="margin-top:12px">Our team is <em>always here to help.</em></h2>
      <p>Whether you need assistance with orders, sizes, deliveries, or product details, we're just a message away, anytime, anywhere.</p>
      <div class="support-pills"><span>Fast responses</span><span>Friendly support</span><span>Reliable service</span></div>
      <div class="cta-actions" style="justify-content:flex-start;margin-top:28px">
        <a class="btn btn-gold" href="{{ wa_link() }}" target="_blank" rel="noopener"><svg><use href="#i-wa"/></svg>WhatsApp us</a>
        <a class="btn btn-line" href="{{ url('contact') }}">All contact options</a>
      </div>
    </div>
    <div class="clock rv-up d1" aria-hidden="true"><div><b>24/7</b><small>Online support</small></div></div>
  </div>
</section>
@endsection
