@extends('layouts.store')

@section('page', 'checkout')
@section('title', 'Checkout — dooo')
@section('noindex', '1')

@section('content')
<section class="page-hero">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span><a href="{{ url('cart') }}">Bag</a></span><span>Checkout</span></nav>
    <h1 id="coTitle">Secure <em>Checkout</em></h1>
    <div class="co-steps" id="coSteps"><span>Bag</span><span class="on">Delivery details</span><span>Confirmed</span></div>
  </div>
</section>

<section class="cart-wrap">
  <div class="wrap" id="coRoot"></div>
</section>
@endsection

@push('scripts')
<script nonce="{{ csp_nonce() }}">
@verbatim
(() => {
  const S = Store, { esc, fmt } = S;
  const root = document.getElementById("coRoot");
  const DISTRICTS = ["Colombo","Gampaha","Kalutara","Kandy","Matale","Nuwara Eliya","Galle","Matara","Hambantota","Jaffna","Kilinochchi","Mannar","Vavuniya","Mullaitivu","Batticaloa","Ampara","Trincomalee","Kurunegala","Puttalam","Anuradhapura","Polonnaruwa","Badulla","Monaragala","Ratnapura","Kegalle"];
  let saved = {}; try { saved = JSON.parse(localStorage.getItem("dooo-customer") || "{}"); } catch (e) {}

  const summaryHTML = () => {
    const t = S.Cart.totals();
    return `
    <aside class="summary">
      <h3>Your order</h3>
      ${S.Cart.lines().map((l) => `
        <div class="mini-line">
          <div class="ci"><img src="${S.imgSrc(l.p)}" data-ph="No · ${l.id}" alt=""><em>${l.qty}</em></div>
          <div><b>${esc(l.p.name)}</b><small>No ${l.id} · Size ${esc(l.size)}${l.colour ? " · " + esc(l.colour) : ""}</small></div>
          <span class="lt">${l.unit == null ? "TBC" : fmt(l.unit * l.qty)}</span>
        </div>`).join("")}
      <div class="sum-row" style="margin-top:8px"><span>Subtotal</span><b>${fmt(t.sub)}</b></div>
      ${t.tbc ? `<div class="sum-row"><span>Priced on confirmation</span><b>${t.tbc} ${t.tbc === 1 ? "item" : "items"}</b></div>` : ""}
      <div class="sum-row"><span>Delivery</span><b>Confirmed with your order</b></div>
      <div class="sum-total"><span>Pay on delivery</span><b>${fmt(t.sub)}${t.tbc ? "+" : ""}</b></div>
      <p class="sum-note">Our team will confirm the final amount, including delivery, on WhatsApp before dispatch.</p>
      <a class="continue" href="cart" style="margin-top:0"><svg><use href="#i-arrow"/></svg>Edit bag</a>
    </aside>`;
  };

  const field = (id, label, type = "text", req = true, extra = "") => `
    <div class="field${extra.includes("full") ? " full" : ""}">
      <label for="${id}">${label}${req ? " <i>*</i>" : ""}</label>
      ${type === "textarea" ? `<textarea id="${id}" name="${id}" ${req ? "required" : ""}>${esc(saved[id] || "")}</textarea>`
        : type === "district" ? `<select id="${id}" name="${id}" ${req ? "required" : ""}><option value="">Select district</option>${DISTRICTS.map((d) => `<option${saved[id] === d ? " selected" : ""}>${d}</option>`).join("")}</select>`
        : `<input id="${id}" name="${id}" type="${type}" value="${esc(saved[id] || "")}" ${req ? "required" : ""} ${extra.replace("full", "")}>`}
      <span class="err" id="${id}-err"></span>
    </div>`;

  const renderForm = () => {
    if (!S.Cart.count()) {
      root.innerHTML = `<div class="empty"><svg><use href="#i-bag"/></svg><h3>Your bag is empty</h3><p>Add a few favourites before checking out.</p><a class="btn btn-primary" href="shop">Shop the collection</a></div>`;
      return;
    }
    root.innerHTML = `
    <div class="cart-grid">
      <form id="coForm" novalidate>
        <div class="hp" aria-hidden="true"><label>Leave this field empty <input name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="co-block">
          <h3><b>1</b>Contact</h3>
          <p>We'll confirm your order on this number, usually via WhatsApp.</p>
          <div class="form-grid">
            ${field("name", "Full name", "text", true, 'autocomplete="name" full')}
            ${field("phone", "Mobile / WhatsApp", "tel", true, 'autocomplete="tel" inputmode="tel" placeholder="07X XXX XXXX"')}
            ${field("alt", "Alternative phone", "tel", false, 'inputmode="tel"')}
          </div>
        </div>
        <div class="co-block">
          <h3><b>2</b>Delivery address</h3>
          <p>Fast islandwide delivery across Sri Lanka.</p>
          <div class="form-grid">
            ${field("address", "Street address", "text", true, 'autocomplete="street-address" placeholder="House no, street, area" full')}
            ${field("city", "City / Town", "text", true, 'autocomplete="address-level2"')}
            ${field("district", "District", "district", true)}
            ${field("notes", "Order notes (optional)", "textarea", false, "full")}
          </div>
        </div>
        <div class="co-block">
          <h3><b>3</b>Payment</h3>
          <p>No card or advance payment needed.</p>
          <label class="pay-opt"><input type="radio" name="pay" checked>
            <span><b>Cash on delivery</b><small>Open and inspect your order before payment. Not right? Exchanges are available for size issues or product defects.</small></span></label>
        </div>
        <label class="agree"><input type="checkbox" id="agree" required>
          <span>I agree to dooo's <a href="shipping" target="_blank">shipping, return &amp; exchange policy</a>.</span></label>
        <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center"><svg><use href="#i-check"/></svg>Place order</button>
        <p class="sum-note" style="text-align:center;margin-top:14px">Placing your order opens WhatsApp with your order details so our team can confirm it right away.</p>
      </form>
      ${summaryHTML()}
    </div>`;

    const form = document.getElementById("coForm");
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const v = Object.fromEntries(new FormData(form).entries());
      Object.keys(v).forEach((k) => (v[k] = String(v[k]).trim()));
      const errors = {};
      if (v.name.length < 2) errors.name = "Please enter your full name";
      const digits = v.phone.replace(/\D/g, "");
      if (!/^(0\d{9}|94\d{9})$/.test(digits)) errors.phone = "Enter a valid Sri Lankan number, e.g. 0771234567";
      if (v.address.length < 5) errors.address = "Please enter your delivery address";
      if (v.city.length < 2) errors.city = "Please enter your city";
      if (!v.district) errors.district = "Please choose your district";
      form.querySelectorAll(".field").forEach((f) => f.classList.remove("bad"));
      Object.entries(errors).forEach(([k, m]) => { const el = document.getElementById(k); el.closest(".field").classList.add("bad"); document.getElementById(k + "-err").textContent = m; });
      if (Object.keys(errors).length) { document.getElementById(Object.keys(errors)[0]).focus(); return; }
      if (!document.getElementById("agree").checked) { S.toast("Please accept the shipping & returns policy"); return; }

      const customer = { name: v.name, phone: v.phone, alt: v.alt, address: v.address, city: v.city, district: v.district, notes: v.notes };
      try { localStorage.setItem("dooo-customer", JSON.stringify({ name: v.name, phone: v.phone, alt: v.alt, address: v.address, city: v.city, district: v.district })); } catch (e) {}

      const btn = form.querySelector('button[type="submit"]');
      btn.disabled = true; btn.dataset.label = btn.innerHTML; btn.innerHTML = "Placing your order…";
      /* open the WhatsApp tab now (inside the click) so pop-up blockers allow it */
      const win = window.open("", "_blank");
      const payload = { ...customer, website: v.website || "", items: S.Cart.lines().map((l) => ({ id: l.id, size: l.size, colour: l.colour || null, qty: l.qty })) };

      fetch(window.DOOO.routes.order, {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify(payload)
      })
        .then(async (r) => ({ ok: r.ok, status: r.status, data: await r.json().catch(() => ({})) }))
        .then(({ ok, status, data }) => {
          if (!ok) {
            if (win) win.close();
            btn.disabled = false; btn.innerHTML = btn.dataset.label;
            if (status === 419) { S.toast("Your session expired — refreshing the page…"); setTimeout(() => location.reload(), 1500); return; }
            if (status === 429) { S.toast("Too many attempts. Please wait a minute and try again."); return; }
            const errs = data.errors || {};
            const first = Object.values(errs)[0];
            Object.entries(errs).forEach(([k, m]) => {
              const el = document.getElementById(k);
              if (el) { el.closest(".field").classList.add("bad"); document.getElementById(k + "-err").textContent = m[0]; }
            });
            S.toast(first ? first[0] : "Sorry, something went wrong. Please try again or order on WhatsApp.");
            return;
          }
          if (win) { win.opener = null; win.location.href = data.whatsapp_url; }
          S.Cart.clear();
          renderDone({ ref: data.ref, customer }, data.whatsapp_url);
        })
        .catch(() => {
          if (win) win.close();
          btn.disabled = false; btn.innerHTML = btn.dataset.label;
          S.toast("Connection problem. Please check your internet and try again.");
        });
    });
    form.addEventListener("input", (e) => e.target.closest(".field")?.classList.remove("bad"));
  };

  const renderDone = (o, link) => {
    document.getElementById("coTitle").innerHTML = "Thank <em>you!</em>";
    document.getElementById("coSteps").innerHTML = `<span>Bag</span><span>Delivery details</span><span class="on">Confirmed</span>`;
    root.innerHTML = `
    <div class="confirm">
      <div class="seal-core"><div><svg><use href="#i-check"/></svg><b>dooo</b><small>Order placed</small></div></div>
      <h2>Your order is on its way to us, ${esc(o.customer.name.split(" ")[0])}.</h2>
      <p class="lead" style="margin:0 auto">We've opened WhatsApp with your order details. Please press <b>send</b> so our team can confirm your order, price and delivery.</p>
      <div class="ref">Order ref · <b>${o.ref}</b></div>
      <div class="confirm-actions">
        <a class="btn btn-primary" href="${link}" target="_blank" rel="noopener"><svg><use href="#i-wa"/></svg>Send order on WhatsApp</a>
        <a class="btn btn-ghost" href="shop">Continue shopping</a>
      </div>
      <div class="confirm-steps">
        <div><svg><use href="#i-chat"/></svg><b>We confirm</b><p>Our team replies with your final price and delivery details.</p></div>
        <div><svg><use href="#i-truck"/></svg><b>Fast delivery</b><p>Your order is couriered to your door, islandwide.</p></div>
        <div><svg><use href="#i-box"/></svg><b>Inspect, then pay</b><p>Open and check your order before paying the courier.</p></div>
      </div>
    </div>`;
    scrollTo({ top: 0, behavior: "smooth" });
  };

  renderForm();
})();
@endverbatim
</script>
@endpush
