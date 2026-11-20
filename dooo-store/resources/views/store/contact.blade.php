@extends('layouts.store')

@section('page', 'contact')
@section('title', 'Contact & Support — dooo')
@section('description', 'Contact dooo for help with orders, sizes, deliveries or product details. 24/7 online support via WhatsApp, phone and email.')

@section('content')
<section class="page-hero">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>Contact</span></nav>
    <h1>We're here <em>24/7</em></h1>
    <p class="lead">Fast responses. Friendly support. Reliable service. Reach us anytime, anywhere.</p>
  </div>
</section>

<section id="support">
  <div class="wrap c-grid">
    <div class="rv-up">
      <span class="eyebrow">24/7 Online Support</span>
      <h2 style="margin:12px 0 16px">Just a message away</h2>
      <p class="lead" style="margin-bottom:28px">Whether you need assistance with orders, sizes, deliveries, or product details, our team is always here to help.</p>
      <div class="c-cards">
        <a class="c-card" href="{{ wa_link() }}" target="_blank" rel="noopener"><span><svg><use href="#i-wa"/></svg></span><div><small>WhatsApp · fastest</small><b>{{ setting('whatsapp_display') }}</b></div></a>
        <a class="c-card" href="tel:{{ setting('phone') }}"><span><svg><use href="#i-phone"/></svg></span><div><small>Landline</small><b>{{ setting('phone_display') }}</b></div></a>
        <a class="c-card" href="mailto:{{ setting('email') }}"><span><svg><use href="#i-mail"/></svg></span><div><small>Email</small><b>{{ setting('email') }}</b></div></a>
        <div class="c-card"><span><svg><use href="#i-pin"/></svg></span><div><small>Registered address</small><b>{{ setting('address') }}</b></div></div>
        <a class="c-card" href="{{ setting('facebook') }}" target="_blank" rel="noopener"><span><svg><use href="#i-fb"/></svg></span><div><small>Facebook</small><b>{{ preg_replace('#^https?://(www\.)?#', '', setting('facebook')) }}</b></div></a>
      </div>
    </div>
    <form class="c-form rv-up d1" id="cForm" novalidate>
      <div class="hp" aria-hidden="true"><label>Leave this field empty <input id="cWebsite" name="website" tabindex="-1" autocomplete="off"></label></div>
      <h3>Send us a message</h3>
      <p>Your message opens in WhatsApp so you get a reply as fast as possible.</p>
      <div class="form-grid">
        <div class="field"><label for="cName">Your name <i>*</i></label><input id="cName" required autocomplete="name"><span class="err">Please enter your name</span></div>
        <div class="field"><label for="cPhone">Phone</label><input id="cPhone" type="tel" inputmode="tel" autocomplete="tel"></div>
        <div class="field full"><label for="cTopic">How can we help?</label>
          <select id="cTopic"><option>Order enquiry</option><option>Size advice</option><option>Delivery</option><option>Return or exchange</option><option>Product details</option><option>Wholesale</option><option>Other</option></select></div>
        <div class="field full"><label for="cOrder">Order ref or design no (optional)</label><input id="cOrder" placeholder="e.g. DO1A2B3C or Design No 02"></div>
        <div class="field full"><label for="cMsg">Message <i>*</i></label><textarea id="cMsg" required></textarea><span class="err">Please write a short message</span></div>
      </div>
      <button class="btn btn-primary" type="submit"><svg><use href="#i-wa"/></svg>Send on WhatsApp</button>
    </form>
  </div>
</section>

<section class="faq" id="faq" style="background:var(--cream)">
  <div class="wrap faq-grid">
    <div>
      <span class="eyebrow">Good to Know</span>
      <h2 style="margin:12px 0 18px">Questions, answered honestly</h2>
      <p class="lead">Can't find what you need? Message us. A real person will reply.</p>
      <div class="safe" style="background:var(--white)">
        <svg><use href="#i-lock"/></svg>
        <div><b>Your safety matters</b><p>dooo will never ask for your card number, bank PIN or OTP. You pay the courier only after checking your order. Only trust our official number <b style="display:inline;font-family:var(--sans);font-size:13.5px">{{ setting('whatsapp_display') }}</b>.</p></div>
      </div>
    </div>
    <div>
      <details open><summary>Do I have to pay before delivery?</summary><p>No. Payment is cash on delivery, and you can open and inspect your order before you pay.</p></details>
      <details><summary>How do I place an order?</summary><p>Choose your size and colour, add the piece to your bag and check out with your delivery details. We'll confirm your order on WhatsApp and dispatch it. You can also send us the design number, size and colour directly on WhatsApp.</p></details>
      <details><summary>Do you deliver to my area?</summary><p>Yes. We offer fast islandwide delivery across Sri Lanka, and we'll confirm the delivery timing when you order.</p></details>
      <details><summary>Can I return or exchange an item?</summary><p>Yes. Easy returns and exchanges are available for eligible items, including exchanges for size issues or product defects. <a href="{{ url('shipping') }}#returns" style="color:var(--green);border-bottom:1px solid var(--gold)">See how it works</a>.</p></details>
      <details><summary>What if my item is damaged or incorrect?</summary><p>We offer a hassle-free exchange or money-back guarantee for eligible purchases. Just message us with your order details.</p></details>
      <details><summary>What sizes are available?</summary><p>Our trousers come in waist sizes 28" to 46". Tops, frocks and sets come in M, L, XL, 2XL and 3XL. Unsure? Send us your measurements and we'll help you choose.</p></details>
      <details><summary>Do you accept wholesale orders?</summary><p>Yes, both wholesale and retail orders are welcome. Message us on WhatsApp for wholesale pricing.</p></details>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script nonce="{{ csp_nonce() }}">
@verbatim
(() => {
  const S = Store, f = document.getElementById("cForm");
  const o = S.qs("order"); if (o) document.getElementById("cOrder").value = o;
  f.addEventListener("submit", (e) => {
    e.preventDefault();
    const val = (id) => document.getElementById(id).value.trim();
    let ok = true;
    ["cName", "cMsg"].forEach((id) => { const bad = val(id).length < 2; document.getElementById(id).closest(".field").classList.toggle("bad", bad); if (bad && ok) { document.getElementById(id).focus(); ok = false; } });
    if (!ok) return;
    const btn = f.querySelector('button[type="submit"]'), label = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = "Sending…";
    fetch(window.DOOO.routes.contact, {
      method: "POST",
      headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ name: val("cName"), phone: val("cPhone"), topic: val("cTopic"), order_ref: val("cOrder"), message: val("cMsg"), website: val("cWebsite") })
    })
      .then(async (r) => ({ ok: r.ok, status: r.status, data: await r.json().catch(() => ({})) }))
      .then(({ ok, status, data }) => {
        btn.disabled = false; btn.innerHTML = label;
        if (!ok) {
          if (status === 419) { S.toast("Your session expired — refreshing…"); setTimeout(() => location.reload(), 1500); return; }
          const first = data.errors ? Object.values(data.errors)[0][0] : "Sorry, your message could not be sent. Please try WhatsApp.";
          S.toast(status === 429 ? "Too many messages. Please wait a minute." : first);
          return;
        }
        f.innerHTML = `<div class="confirm" style="padding:10px 0">
          <div class="seal-core" style="width:120px;height:120px;margin-bottom:22px"><div><svg><use href="#i-check"/></svg><small>Sent</small></div></div>
          <h3 style="font-size:32px;margin-bottom:10px">Thank you, ${S.esc(val("cName") || "")}!</h3>
          <p style="color:var(--muted);margin-bottom:22px">We've received your message and will reply soon. For the fastest reply, you can also send it to us on WhatsApp.</p>
          <a class="btn btn-primary" href="${S.esc(data.whatsapp_url)}" target="_blank" rel="noopener"><svg><use href="#i-wa"/></svg>Also send on WhatsApp</a></div>`;
      })
      .catch(() => { btn.disabled = false; btn.innerHTML = label; S.toast("Connection problem. Please try again."); });
  });
  f.addEventListener("input", (e) => e.target.closest(".field")?.classList.remove("bad"));
})();
@endverbatim
</script>
@endpush
