@extends('layouts.store')

@section('page', 'cart')
@section('title', 'Your Bag — dooo')
@section('noindex', '1')

@section('content')
<section class="page-hero">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>Your Bag</span></nav>
    <h1>Your <em>Bag</em></h1>
    <div class="co-steps"><span class="on">Bag</span><span>Delivery details</span><span>Confirmed</span></div>
  </div>
</section>

<section class="cart-wrap">
  <div class="wrap" id="cartRoot"></div>
</section>

<section class="related" id="suggestWrap" hidden>
  <div class="wrap">
    <div class="sec-head"><div><span class="eyebrow">Just for you</span><h2>Bestselling pieces</h2></div></div>
    <div class="p-grid" id="suggest"></div>
  </div>
</section>
@endsection

@push('scripts')
<script nonce="{{ csp_nonce() }}">
@verbatim
(() => {
  const S = Store, { esc, fmt } = S;
  const root = document.getElementById("cartRoot");

  const render = () => {
    const lines = S.Cart.lines(), t = S.Cart.totals(), n = S.Cart.count();
    document.getElementById("suggestWrap").hidden = n > 0;
    if (!n) {
      root.innerHTML = `<div class="empty"><svg><use href="#i-bag"/></svg><h3>Your bag is empty</h3>
        <p>Discover elegant, comfortable pieces made for the modern woman.</p>
        <a class="btn btn-primary" href="shop">Start shopping</a></div>`;
      const sug = document.getElementById("suggest");
      if (!sug.children.length) { sug.innerHTML = S.products.filter((p) => p.signature).map(S.card).join(""); S.enhance(sug); }
      return;
    }
    root.innerHTML = `
    <div class="cart-grid">
      <div>
        <div class="cart-head"><span>${n} ${n === 1 ? "item" : "items"}</span><span>Total</span></div>
        ${lines.map((l) => `
        <div class="cart-line">
          <a class="ci" href="product?id=${l.id}"><img src="${S.imgSrc(l.p)}" data-ph="No · ${l.id}" alt="${esc(l.p.name)}"></a>
          <div>
            <div class="dn">Design No · ${l.id}</div>
            <h4><a href="product?id=${l.id}">${esc(l.p.name)}</a></h4>
            <div class="meta"><span>Size ${esc(l.size)}</span>${l.colour ? `<span><i style="background:${S.COLOURS[l.colour] || "#ccc"}"></i>${esc(l.colour)}</span>` : ""}<span>${l.unit == null ? "Price on request" : fmt(l.unit) + " each"}</span></div>
            <div class="row">
              <div class="qty"><button type="button" data-q="-1" data-k="${esc(l.key)}" aria-label="Decrease"><svg><use href="#i-minus"/></svg></button><span>${l.qty}</span><button type="button" data-q="1" data-k="${esc(l.key)}" aria-label="Increase"><svg><use href="#i-plus"/></svg></button></div>
              <button class="rm" type="button" data-rm="${esc(l.key)}"><svg><use href="#i-trash"/></svg>Remove</button>
            </div>
          </div>
          <div class="line-total">${l.unit == null ? `<small>To be confirmed</small>` : fmt(l.unit * l.qty)}</div>
        </div>`).join("")}
        <a class="continue" href="shop"><svg><use href="#i-arrow"/></svg>Continue shopping</a>
      </div>
      <aside class="summary">
        <h3>Order summary</h3>
        <div class="sum-row"><span>Subtotal</span><b>${fmt(t.sub)}</b></div>
        ${t.tbc ? `<div class="sum-row"><span>Priced on confirmation</span><b>${t.tbc} ${t.tbc === 1 ? "item" : "items"}</b></div>` : ""}
        <div class="sum-row"><span>Delivery</span><b>Islandwide · confirmed with order</b></div>
        <div class="sum-row"><span>Payment</span><b>Cash on delivery</b></div>
        <div class="sum-total"><span>Estimated total</span><b>${fmt(t.sub)}${t.tbc ? "+" : ""}</b></div>
        <p class="sum-note">No advance payment. Open and inspect your order before you pay.${t.tbc ? " Items marked “price on request” are confirmed by our team before dispatch." : ""}</p>
        <a class="btn btn-primary" href="checkout">Proceed to checkout <svg><use href="#i-arrow"/></svg></a>
        <a class="btn btn-ghost" href="${S.waLink(S.Cart.orderText())}" target="_blank" rel="noopener"><svg><use href="#i-wa"/></svg>Order via WhatsApp</a>
        <div class="sum-assure">
          <span><svg><use href="#i-box"/></svg>Open &amp; inspect before payment</span>
          <span><svg><use href="#i-swap"/></svg>Easy exchanges for size issues or defects</span>
          <span><svg><use href="#i-return"/></svg>Money-back guarantee on eligible items</span>
        </div>
      </aside>
    </div>`;
  };

  root.addEventListener("click", (e) => {
    const q = e.target.closest("[data-q]"), r = e.target.closest("[data-rm]");
    if (q) {
      const l = S.Cart.lines().find((x) => x.key === q.dataset.k);
      if (l) S.Cart.setQty(l.key, l.qty + +q.dataset.q);
    }
    if (r) { S.Cart.remove(r.dataset.rm); S.toast("Removed from your bag"); }
  });
  S.onChange(render);
  render();
})();
@endverbatim
</script>
@endpush
