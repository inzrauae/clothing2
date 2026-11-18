/* =========================================================
   dooo — shared storefront script
   Renders header / footer / mobile tab bar, and powers the
   bag, wishlist, quick-add, image fallback and animations.
   Every page loads data.js, then this file, then its own script.
   ========================================================= */
(() => {
  const { COLOURS, SIZES, CATS, products } = window.DOOO;
  const CFG = window.DOOO.settings || {};
  const WA = String(CFG.whatsapp || "94761400696").replace(/\D/g, "");
  const PAGE = document.body.dataset.page || "";

  /* ---------------- helpers ---------------- */
  const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const fmt = (n) => "Rs. " + Number(n).toLocaleString("en-US");
  const waLink = (t) => `https://wa.me/${WA}?text=${encodeURIComponent(t)}`;
  const find = (id) => products.find((p) => p.id === String(id));
  const imgSrc = (p) => (/^(https?:|\/)/.test(p.img || "") ? p.img : "images/premium/" + p.img);
  const sizesOf = (p) => SIZES[p.sizes] || SIZES.alpha;
  const priceFor = (p, size) => (p.price == null ? null : p.priceLarge && size && +size >= 38 ? p.priceLarge : p.price);
  const priceHTML = (p) =>
    p.price == null
      ? "Price on request"
      : `${p.compare ? `<s>${fmt(p.compare)}</s>` : ""}${p.from ? "From " : ""}${fmt(p.price)}`;
  const qs = (k) => new URLSearchParams(location.search).get(k);
  const load = (k, d) => { try { const v = JSON.parse(localStorage.getItem(k)); return v ?? d; } catch (e) { return d; } };
  const save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} };
  const buzz = () => { try { navigator.vibrate && navigator.vibrate(8); } catch (e) {} };

  /* ---------------- change events ---------------- */
  const subs = [];
  const onChange = (f) => subs.push(f);
  const emit = () => { updateBadges(); subs.forEach((f) => f()); };

  /* ---------------- wishlist ---------------- */
  let wish = new Set(load("dooo-wish", []).filter((id) => find(id)));
  const Wish = {
    has: (id) => wish.has(id),
    count: () => wish.size,
    list: () => [...wish].map(find).filter(Boolean),
    toggle(id) {
      wish.has(id) ? wish.delete(id) : wish.add(id);
      save("dooo-wish", [...wish]); emit();
      return wish.has(id);
    },
    remove(id) { wish.delete(id); save("dooo-wish", [...wish]); emit(); }
  };

  /* ---------------- bag ---------------- */
  let cart = load("dooo-cart", []).filter((l) => find(l.id));
  const keyOf = (l) => `${l.id}|${l.size}|${l.colour || ""}`;
  const persist = () => { save("dooo-cart", cart); emit(); };
  const Cart = {
    key: keyOf,
    lines: () => cart.map((l) => { const p = find(l.id); return { ...l, key: keyOf(l), p, unit: priceFor(p, l.size) }; }),
    count: () => cart.reduce((a, l) => a + l.qty, 0),
    add(id, size, colour, qty = 1) {
      const k = keyOf({ id, size, colour });
      const ex = cart.find((l) => keyOf(l) === k);
      if (ex) ex.qty = Math.min(20, ex.qty + qty);
      else cart.push({ id, size, colour: colour || "", qty: Math.min(20, qty) });
      persist();
    },
    setQty(k, q) {
      const l = cart.find((x) => keyOf(x) === k); if (!l) return;
      if (q < 1) cart = cart.filter((x) => x !== l); else l.qty = Math.min(20, q);
      persist();
    },
    remove(k) { cart = cart.filter((x) => keyOf(x) !== k); persist(); },
    clear() { cart = []; persist(); },
    totals() {
      let sub = 0, tbc = 0;
      Cart.lines().forEach((l) => { if (l.unit == null) tbc += l.qty; else sub += l.unit * l.qty; });
      return { sub, tbc };
    },
    /* plain-text order used for WhatsApp */
    orderText(customer, ref) {
      const lines = Cart.lines(), t = Cart.totals();
      const out = [`New order — dooo${ref ? ` (Ref ${ref})` : ""}`, ""];
      lines.forEach((l, i) => {
        out.push(`${i + 1}. Design No ${l.id} — ${l.p.name}`);
        out.push(`   Size ${l.size}${l.colour ? ` · ${l.colour}` : ""} · Qty ${l.qty} · ${l.unit == null ? "price to confirm" : fmt(l.unit * l.qty)}`);
      });
      out.push("", `Subtotal: ${fmt(t.sub)}${t.tbc ? ` + ${t.tbc} item(s) priced on confirmation` : ""}`);
      out.push("Delivery: islandwide (charge confirmed by dooo)");
      out.push("Payment: Cash on delivery — I'll open & inspect before paying");
      if (customer) {
        out.push("", `Name: ${customer.name}`, `Phone: ${customer.phone}`);
        if (customer.alt) out.push(`Alt. phone: ${customer.alt}`);
        out.push(`Address: ${customer.address}`, `City: ${customer.city}`, `District: ${customer.district}`);
        if (customer.notes) out.push(`Notes: ${customer.notes}`);
      }
      return out.join("\n");
    }
  };

  /* keep tabs in sync */
  addEventListener("storage", (e) => {
    if (e.key === "dooo-cart") cart = load("dooo-cart", []).filter((l) => find(l.id));
    if (e.key === "dooo-wish") wish = new Set(load("dooo-wish", []));
    if (e.key === "dooo-cart" || e.key === "dooo-wish") emit();
  });

  /* ---------------- missing-image fallback ---------------- */
  const placeholder = (label) =>
    "data:image/svg+xml;charset=utf-8," +
    encodeURIComponent(
      `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 700'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='#f8f3ea'/><stop offset='1' stop-color='#e9dfcc'/></linearGradient></defs><rect width='600' height='700' fill='url(#g)'/><rect x='22' y='22' width='556' height='656' fill='none' stroke='#c6a35a' stroke-opacity='.55'/><text x='300' y='350' text-anchor='middle' font-family='Jost,Arial,sans-serif' font-size='92' font-weight='500' fill='#0f4a32'>dooo</text><text x='300' y='398' text-anchor='middle' font-family='Georgia,serif' font-style='italic' font-size='24' fill='#a8853f'>${esc(label)}</text></svg>`
    );
  const fixImg = (img) => {
    if (img.dataset.fb) return;
    img.dataset.fb = "1";
    img.classList.add("img-ph");
    img.src = placeholder(img.dataset.ph || "Fashionably Elegant");
  };
  document.addEventListener("error", (e) => { if (e.target.tagName === "IMG") fixImg(e.target); }, true);
  const sweepImages = () => [...document.images].forEach((i) => { if (i.complete && !i.naturalWidth && i.getAttribute("src")) fixImg(i); });

  /* ---------------- icons ---------------- */
  const SPRITE = `<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-wa" viewBox="0 0 24 24"><path fill="currentColor" d="M17.5 14.4c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.1-.2.3-.8 1-.9 1.2-.2.2-.3.2-.6.1-.3-.1-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.4-.5c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.2-.3-.3-.6-.4zM12 21.8c-1.8 0-3.5-.5-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4C2.7 15.6 2.2 13.8 2.2 12 2.2 6.6 6.6 2.2 12 2.2s9.8 4.4 9.8 9.8-4.4 9.8-9.8 9.8zM12 0C5.4 0 0 5.4 0 12c0 2.1.6 4.2 1.6 6L0 24l6.2-1.6c1.8 1 3.8 1.5 5.8 1.5 6.6 0 12-5.4 12-12S18.6 0 12 0z"/></symbol>
  <symbol id="i-search" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></g></symbol>
  <symbol id="i-heart" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.6" d="M12 20.5s-8-4.6-8-10.4A4.4 4.4 0 0 1 12 7.6a4.4 4.4 0 0 1 8 2.5c0 5.8-8 10.4-8 10.4z"/></symbol>
  <symbol id="i-bag" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 8h14l-1 13H6z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></g></symbol>
  <symbol id="i-menu" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="1.6" d="M3 7h18M3 12h18M3 17h12"/></symbol>
  <symbol id="i-truck" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M2 6h12v10H2zM14 9h4l3 3v4h-7"/><circle cx="6" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/></g></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 2.5 4 5.5v6c0 5 3.4 8.6 8 10 4.6-1.4 8-5 8-10v-6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/></g></symbol>
  <symbol id="i-ruler" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M3 16 16 3l5 5L8 21z"/><path d="m7 12 2 2M10 9l2 2M13 6l2 2"/></g></symbol>
  <symbol id="i-gem" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M6 3h12l4 6-10 12L2 9z"/><path d="M2 9h20M9 3l3 18M15 3l-3 18"/></g></symbol>
  <symbol id="i-ig" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor"/></g></symbol>
  <symbol id="i-fb" viewBox="0 0 24 24"><path fill="currentColor" d="M14 8V6.3c0-.8.2-1.3 1.4-1.3H17V2h-2.6C11.6 2 10.6 3.5 10.6 6v2H8v3h2.6v11H14V11h2.6l.4-3z"/></symbol>
  <symbol id="i-tt" viewBox="0 0 24 24"><path fill="currentColor" d="M16.6 2h-3.3v13.4a2.9 2.9 0 1 1-2-2.8V9.2a6.3 6.3 0 1 0 5.3 6.2V8.6a7.8 7.8 0 0 0 4.4 1.4V6.7a4.5 4.5 0 0 1-4.4-4.7z"/></symbol>
  <symbol id="i-yt" viewBox="0 0 24 24"><path fill="currentColor" d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .5 12 31 31 0 0 0 1 16.8a3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1c.4-1.6.5-4.8.5-4.8s0-3.2-.5-4.8zM9.7 15V9l5.8 3z"/></symbol>
  <symbol id="i-phone" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.6" d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2z"/></symbol>
  <symbol id="i-mail" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="1"/><path d="m3 6 9 7 9-7"/></g></symbol>
  <symbol id="i-pin" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></g></symbol>
  <symbol id="i-box" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/><path d="m15 15.5 1.5 1.5 3-3"/></g></symbol>
  <symbol id="i-cash" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><rect x="2" y="6" width="20" height="12" rx="1"/><circle cx="12" cy="12" r="2.8"/><path d="M6 9v.01M18 15v.01"/></g></symbol>
  <symbol id="i-return" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-4"/></g></symbol>
  <symbol id="i-camera" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M3 8h4l2-3h6l2 3h4v11H3z"/><circle cx="12" cy="13" r="3.5"/></g></symbol>
  <symbol id="i-chat" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M4 5h16v11H9l-5 4z"/><path d="M8 9.5h8M8 12.5h5"/></g></symbol>
  <symbol id="i-reg" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="12" cy="12" r="9"/><path d="M9.5 16.5v-9h3.2a2.3 2.3 0 0 1 0 4.6H9.5m3 0 2.5 4.4"/></g></symbol>
  <symbol id="i-lock" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><rect x="4" y="10" width="16" height="11" rx="1"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14.5v2.5"/></g></symbol>
  <symbol id="i-home" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 10.5 12 3l9 7.5V21h-6v-6H9v6H3z"/></g></symbol>
  <symbol id="i-grid" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="7.5" height="7.5" rx="1"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1"/></g></symbol>
  <symbol id="i-x" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="1.6" d="M6 6l12 12M18 6 6 18"/></symbol>
  <symbol id="i-arrow" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.6" d="M4 12h16m-6-6 6 6-6 6"/></symbol>
  <symbol id="i-minus" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="1.6" d="M5 12h14"/></symbol>
  <symbol id="i-plus" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="1.6" d="M5 12h14M12 5v14"/></symbol>
  <symbol id="i-trash" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/></g></symbol>
  <symbol id="i-clock" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></g></symbol>
  <symbol id="i-leaf" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 19C5 10 10 5 20 4c0 9-5 15-13 15z"/><path d="M5 19 13 11"/></g></symbol>
  <symbol id="i-hanger" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M10 6a2 2 0 1 1 2 2v2.5L21 17H3l9-6.5"/><path d="M3 17v1h18v-1"/></g></symbol>
  <symbol id="i-spark" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.4" d="M12 3c.6 4.6 3.4 7.4 8 8-4.6.6-7.4 3.4-8 8-.6-4.6-3.4-7.4-8-8 4.6-.6 7.4-3.4 8-8z"/></symbol>
  <symbol id="i-users" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14a6.5 6.5 0 0 1 3.5 6"/></g></symbol>
  <symbol id="i-headset" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3" y="13" width="4" height="6" rx="1"/><rect x="17" y="13" width="4" height="6" rx="1"/><path d="M19 19c0 1.5-2 2.5-5 2.5"/></g></symbol>
  <symbol id="i-swap" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.4"><path d="M4 8h14l-3-3M20 16H6l3 3"/></g></symbol>
</svg>`;

  /* ---------------- chrome ---------------- */
  const nav = [
    ["shop", "shop", "Shop"],
    ["about", "about", "Our Story"],
    ["shipping", "shipping", "Shipping &amp; Returns"],
    ["contact", "contact", "Contact"]
  ];
  const isOn = (k) => (PAGE === k || (k === "shop" && PAGE === "product") ? " on" : "");

  const HEADER = `
<div class="announce" aria-label="Store highlights"><div class="track">
  ${Array(2).fill(String(CFG.announcement || "").split(/\r?\n/).filter((x) => x.trim()).map((x) => `<span>${esc(x.trim())}</span>`).join("")).join("")}
</div></div>
<header id="top">
  <div class="wrap nav">
    <nav class="nav-links" aria-label="Primary">
      ${nav.map(([k, h, t]) => `<a href="${h}" class="${isOn(k).trim()}">${t}</a>`).join("")}
    </nav>
    <a href="./" class="logo" aria-label="dooo home">dooo<sup>®</sup><small>Fashionably Elegant</small></a>
    <div class="nav-right">
      <a class="icon-btn" href="shop#search" aria-label="Search"><svg><use href="#i-search"/></svg></a>
      <a class="icon-btn" href="wishlist" aria-label="Wishlist" id="wishBtn"><svg><use href="#i-heart"/></svg><b class="hcount" data-count="wish"></b></a>
      <a class="icon-btn" href="cart" aria-label="Shopping bag" id="bagBtn"><svg><use href="#i-bag"/></svg><b class="hcount" data-count="bag"></b></a>
      <button class="icon-btn burger" aria-label="Menu" id="burger"><svg><use href="#i-menu"/></svg></button>
    </div>
  </div>
</header>
<div class="mnav" id="mnav" aria-label="Menu">
  <div class="sheet-handle"></div>
  <button class="close" id="mclose">Close ✕</button>
  <a href="./">Home</a>
  <a href="shop">Shop All</a>
  ${CATS.map((c) => `<a href="shop?cat=${c}" class="sub">${c}</a>`).join("")}
  <a href="about">Our Story</a>
  <a href="shipping">Shipping &amp; Returns</a>
  <a href="contact">Contact &amp; Support</a>
  <a href="wishlist">Wishlist</a>
  <a href="cart">My Bag</a>
</div>`;

  const FOOTER = `
<footer>
  <div class="wrap">
    <div class="f-grid f-grid-5">
      <div>
        <a href="./" class="logo">dooo<sup>®</sup><small>Fashionably Elegant</small></a>
        <p style="margin-top:20px;max-width:300px">Premium ladies' wear crafted with care — elegance, comfort and confidence in every piece.</p>
        <p class="f-legal">A brand of ${esc(CFG.company || "")},<br>a member of the ${esc(CFG.group || "")}.</p>
      </div>
      <div>
        <h5>Shop</h5>
        <ul>
          <li><a href="shop">Shop All</a></li>
          ${CATS.map((c) => `<li><a href="shop?cat=${c}">${c}</a></li>`).join("")}
        </ul>
      </div>
      <div>
        <h5>Customer Care</h5>
        <ul>
          <li><a href="shipping">Shipping &amp; Delivery</a></li>
          <li><a href="shipping#returns">Returns &amp; Exchanges</a></li>
          <li><a href="shipping#guarantee">Money Back Guarantee</a></li>
          <li><a href="contact#support">24/7 Online Support</a></li>
          <li><a href="contact#faq">FAQ</a></li>
        </ul>
      </div>
      <div>
        <h5>About dooo</h5>
        <ul>
          <li><a href="about">About Us</a></li>
          <li><a href="about#story">Our Story</a></li>
          <li><a href="about#mission">Our Mission</a></li>
          <li><a href="about#why">Why Choose Us</a></li>
        </ul>
      </div>
      <div>
        <h5>Contact</h5>
        <ul class="f-contact">
          <li><svg><use href="#i-wa"/></svg><a href="https://wa.me/${WA}" target="_blank" rel="noopener">${esc(CFG.whatsapp_display || "")}</a></li>
          <li><svg><use href="#i-phone"/></svg><a href="tel:${esc(CFG.phone || "")}">${esc(CFG.phone_display || "")}</a></li>
          <li><svg><use href="#i-mail"/></svg><a href="mailto:${esc(CFG.email || "")}">${esc(CFG.email || "")}</a></li>
          <li><svg><use href="#i-pin"/></svg><span>${esc(CFG.address || "")}</span></li>
        </ul>
        <div class="f-social">
          <a href="${esc(CFG.facebook || "#")}" target="_blank" rel="noopener" aria-label="Facebook"><svg><use href="#i-fb"/></svg></a>
          <a href="${esc(CFG.instagram || "#")}" target="_blank" rel="noopener" aria-label="Instagram"><svg><use href="#i-ig"/></svg></a>
          <a href="${esc(CFG.tiktok || "#")}" target="_blank" rel="noopener" aria-label="TikTok"><svg><use href="#i-tt"/></svg></a>
          <a href="${esc(CFG.youtube || "#")}" target="_blank" rel="noopener" aria-label="YouTube"><svg><use href="#i-yt"/></svg></a>
        </div>
      </div>
    </div>
    <div class="f-trust">
      <span><svg><use href="#i-truck"/></svg>Fast islandwide delivery</span>
      <span><svg><use href="#i-box"/></svg>Open &amp; inspect before payment</span>
      <span><svg><use href="#i-swap"/></svg>Easy returns &amp; exchanges</span>
      <span><svg><use href="#i-cash"/></svg>Money-back guarantee</span>
      <span><svg><use href="#i-headset"/></svg>24/7 online support</span>
    </div>
    <div class="f-bottom">
      <span>© ${new Date().getFullYear()} dooo — Fashionably Elegant. All rights reserved.</span>
      <span>Timeless Fashion. Modern You.</span>
    </div>
  </div>
</footer>
<div class="sheet-bg" id="sheetBg"></div>
<nav class="tabbar" aria-label="App navigation">
  <a href="./" data-tab="home"><svg><use href="#i-home"/></svg><span>Home</span></a>
  <a href="shop" data-tab="shop"><svg><use href="#i-grid"/></svg><span>Shop</span></a>
  <a href="cart" data-tab="bag" class="tab-wa" aria-label="Shopping bag"><i><svg><use href="#i-bag"/></svg><b class="count" data-count="bag"></b></i><span>Bag</span></a>
  <a href="wishlist" data-tab="saved"><svg><use href="#i-heart"/></svg><span>Saved</span><b class="count" data-count="wish"></b></a>
  <button type="button" data-tab="menu" id="tabMenu"><svg><use href="#i-menu"/></svg><span>Menu</span></button>
</nav>
<a class="wa-float" href="https://wa.me/${WA}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
  <span class="bubble">24/7 support</span>
  <span class="dot"><svg><use href="#i-wa"/></svg></span>
</a>
<div class="qa-bg" id="qaBg"></div>
<div class="qa" id="qa" role="dialog" aria-modal="true" aria-label="Choose options"></div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<div class="progress" id="progress" aria-hidden="true"></div>
<div class="cursor" id="cursor" aria-hidden="true"></div><div class="cursor-dot" id="cursorDot" aria-hidden="true"></div>`;

  document.body.insertAdjacentHTML("afterbegin", SPRITE + HEADER);
  document.body.insertAdjacentHTML("beforeend", FOOTER);
  sweepImages();

  /* ---------------- badges ---------------- */
  function updateBadges() {
    const n = { bag: Cart.count(), wish: Wish.count() };
    document.querySelectorAll("[data-count]").forEach((el) => {
      const v = n[el.dataset.count];
      el.textContent = v > 9 ? "9+" : v;
      el.classList.toggle("show", v > 0);
    });
    document.querySelectorAll("[data-wish]").forEach((b) => b.classList.toggle("on", wish.has(b.dataset.wish)));
  }
  updateBadges();

  /* ---------------- toast ---------------- */
  const toast = (msg) => {
    const t = document.getElementById("toast");
    t.textContent = msg; t.classList.add("show");
    clearTimeout(t._h); t._h = setTimeout(() => t.classList.remove("show"), 2400);
  };

  /* ---------------- header + menu ---------------- */
  const header = document.querySelector("header");
  addEventListener("scroll", () => header.classList.toggle("scrolled", scrollY > 10), { passive: true });

  const mnav = document.getElementById("mnav"), sbg = document.getElementById("sheetBg");
  const closeMenu = () => { mnav.classList.remove("open"); sbg.classList.remove("on"); document.body.style.overflow = ""; };
  const openMenu = () => { mnav.classList.add("open"); sbg.classList.add("on"); document.body.style.overflow = "hidden"; buzz(); };
  document.getElementById("burger").onclick = openMenu;
  document.getElementById("tabMenu").onclick = openMenu;
  document.getElementById("mclose").onclick = closeMenu;
  sbg.onclick = closeMenu;
  mnav.querySelectorAll("a").forEach((a) => a.addEventListener("click", closeMenu));
  (() => { /* swipe down to close */
    let y0 = null, dy = 0;
    mnav.addEventListener("touchstart", (e) => { if (mnav.scrollTop <= 0) y0 = e.touches[0].clientY; }, { passive: true });
    mnav.addEventListener("touchmove", (e) => { if (y0 === null) return; dy = Math.max(0, e.touches[0].clientY - y0); mnav.style.transform = `translateY(${dy}px)`; mnav.style.transition = "none"; }, { passive: true });
    mnav.addEventListener("touchend", () => { mnav.style.transition = ""; mnav.style.transform = ""; if (dy > 90) closeMenu(); y0 = null; dy = 0; });
  })();

  /* active tab */
  const tabFor = { home: "home", shop: "shop", product: "shop", cart: "bag", checkout: "bag", wishlist: "saved" }[PAGE];
  document.querySelectorAll(".tabbar [data-tab]").forEach((t) => t.classList.toggle("on", t.dataset.tab === tabFor));

  /* ---------------- product card ---------------- */
  const card = (p) => `
<article class="p-card" data-id="${p.id}">
  <div class="p-img">
    ${p.tag ? `<span class="tag">${esc(p.tag)}</span>` : ""}
    <a class="p-link" href="product?id=${p.id}" aria-label="${esc(p.name)}"><img src="${imgSrc(p)}" data-ph="Design No · ${p.id}" alt="${esc(p.name)}, design number ${p.id}" loading="lazy" decoding="async"></a>
    <button class="p-wish${wish.has(p.id) ? " on" : ""}" data-wish="${p.id}" aria-label="Save ${esc(p.name)}"><svg><use href="#i-heart"/></svg></button>
    <button class="p-quick" data-quick="${p.id}"><svg><use href="#i-bag"/></svg>Quick add</button>
  </div>
  <div class="p-info">
    <div class="dn">Design No · ${p.id}</div>
    <h4><a href="product?id=${p.id}">${esc(p.name)}</a></h4>
    <div class="pp${p.price == null ? " ask" : ""}">${priceHTML(p)}</div>
    <button class="p-quick m-only" data-quick="${p.id}"><svg><use href="#i-bag"/></svg>Add to bag</button>
  </div>
</article>`;

  /* ---------------- size / colour options ---------------- */
  const optsHTML = (p) => `
<div class="opt">
  <div class="opt-head"><span>Size${p.sizes === "waist" ? ` <small>· waist in inches</small>` : ""}</span>
    <a href="${waLink(`Hi dooo! Could you help me choose a size for Design No ${p.id} — ${p.name}?`)}" target="_blank" rel="noopener">Need size help?</a></div>
  <div class="size-opts">${sizesOf(p).map((s) => `<button type="button" data-size="${s}">${s}</button>`).join("")}</div>
</div>
${p.colours
  ? `<div class="opt"><div class="opt-head"><span>Colour: <b data-cname>Select a colour</b></span></div>
     <div class="colour-opts">${p.colours.map((c) => `<button type="button" data-colour="${esc(c)}" title="${esc(c)}" aria-label="${esc(c)}" style="--c:${COLOURS[c] || "#ccc"}"></button>`).join("")}</div></div>`
  : `<p class="opt-note"><svg><use href="#i-camera"/></svg>Colour as pictured. Ask us on WhatsApp about other shades.</p>`}`;

  const bindOpts = (root, p, cb) => {
    const st = { size: null, colour: p.colours ? null : "" };
    root.addEventListener("click", (e) => {
      const s = e.target.closest("[data-size]"), c = e.target.closest("[data-colour]");
      if (s) { st.size = s.dataset.size; root.querySelectorAll("[data-size]").forEach((b) => b.classList.toggle("on", b === s)); }
      if (c) {
        st.colour = c.dataset.colour;
        root.querySelectorAll("[data-colour]").forEach((b) => b.classList.toggle("on", b === c));
        const n = root.querySelector("[data-cname]"); if (n) n.textContent = st.colour;
      }
      if ((s || c) && cb) cb(st);
    });
    return st;
  };
  const validOpts = (root, p, st) => {
    const miss = !st.size ? ".size-opts" : p.colours && !st.colour ? ".colour-opts" : null;
    if (!miss) return true;
    const el = root.querySelector(miss);
    el.classList.remove("shake"); void el.offsetWidth; el.classList.add("shake");
    toast(!st.size ? "Please choose a size" : "Please choose a colour");
    return false;
  };

  /* ---------------- quick add dialog ---------------- */
  const qa = document.getElementById("qa"), qaBg = document.getElementById("qaBg");
  let lastFocus = null;
  const closeQA = () => { qa.classList.remove("open"); qaBg.classList.remove("on"); document.body.style.overflow = ""; lastFocus && lastFocus.focus(); };
  const quickAdd = (id) => {
    const p = find(id); if (!p) return;
    lastFocus = document.activeElement;
    qa.innerHTML = `
      <div class="sheet-handle"></div>
      <button class="qa-x" data-qa-close aria-label="Close"><svg><use href="#i-x"/></svg></button>
      <div class="qa-grid">
        <div class="qa-img"><img src="${imgSrc(p)}" data-ph="Design No · ${p.id}" alt="${esc(p.name)}"></div>
        <div class="qa-body">
          <div class="dn">Design No · ${p.id}</div>
          <h3>${esc(p.name)}</h3>
          <div class="qa-price price">${priceHTML(p)}</div>
          ${optsHTML(p)}
          <button class="btn btn-primary qa-add" type="button"><svg><use href="#i-bag"/></svg>Add to bag</button>
          <a class="qa-more" href="product?id=${p.id}">View full details <svg><use href="#i-arrow"/></svg></a>
        </div>
      </div>`;
    const st = bindOpts(qa, p, (s) => {
      const u = priceFor(p, s.size);
      if (u != null && s.size) qa.querySelector(".qa-price").innerHTML = `${p.compare ? `<s>${fmt(p.compare)}</s>` : ""}${fmt(u)}`;
    });
    qa.querySelector(".qa-add").onclick = () => {
      if (!validOpts(qa, p, st)) return;
      Cart.add(p.id, st.size, st.colour, 1); buzz(); closeQA();
      toast(`Added to your bag — Design No ${p.id}`);
    };
    qa.querySelector("[data-qa-close]").onclick = closeQA;
    qa.classList.add("open"); qaBg.classList.add("on"); document.body.style.overflow = "hidden";
    setTimeout(() => qa.querySelector(".size-opts button").focus(), 50);
  };
  qaBg.onclick = closeQA;
  addEventListener("keydown", (e) => { if (e.key === "Escape") { if (qa.classList.contains("open")) closeQA(); else closeMenu(); } });

  /* global delegation: wishlist hearts + quick add */
  document.addEventListener("click", (e) => {
    const w = e.target.closest("[data-wish]");
    if (w) {
      e.preventDefault();
      const on = Wish.toggle(w.dataset.wish); buzz();
      toast(on ? `Design No ${w.dataset.wish} saved to your wishlist` : "Removed from your wishlist");
      return;
    }
    const q = e.target.closest("[data-quick]");
    if (q) { e.preventDefault(); quickAdd(q.dataset.quick); }
  });

  /* ---------------- reveal + effects ---------------- */
  const reduce = matchMedia("(prefers-reduced-motion: reduce)").matches;
  const fine = matchMedia("(hover:hover) and (pointer:fine)").matches;
  const io = new IntersectionObserver((es) => es.forEach((e) => { if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); } }), { threshold: 0.12 });
  const gio = new IntersectionObserver((es) => es.forEach((e) => {
    if (e.isIntersecting) { e.target.querySelectorAll(".col, .ed-img").forEach((c) => c.classList.add("in")); gio.unobserve(e.target); }
  }), { threshold: 0.1 });

  const enhance = (root = document) => {
    root.querySelectorAll(".sec-head > *, .p-card, .tr-card").forEach((el, i) => {
      el.classList.add("rv-up");
      if (el.classList.contains("p-card")) el.style.transitionDelay = (i % 4) * 0.08 + "s";
    });
    root.querySelectorAll(".rv-up:not(.in)").forEach((el) => {
      if (el.matches(".col, .ed-img")) return;
      io.observe(el);
    });
    root.querySelectorAll(".col-grid, .ed-grid").forEach((g) => gio.observe(g));
    if (!fine || reduce) return;
    root.querySelectorAll(".tr-card, .pr, .rv, .p-img, .why-card").forEach((c) => {
      if (c.dataset.tilt) return; c.dataset.tilt = "1";
      c.classList.add("tilt");
      if (getComputedStyle(c).position === "static") c.style.position = "relative";
      const g = document.createElement("span"); g.className = "glare"; c.append(g);
      c.addEventListener("mousemove", (e) => {
        const r = c.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
        c.style.transition = "transform .1s";
        c.style.transform = `perspective(900px) rotateX(${(0.5 - y) * 7}deg) rotateY(${(x - 0.5) * 7}deg) translateY(-5px)`;
        c.style.setProperty("--gx", x * 100 + "%"); c.style.setProperty("--gy", y * 100 + "%");
      });
      c.addEventListener("mouseleave", () => { c.style.transition = "transform .6s cubic-bezier(.2,.7,.2,1)"; c.style.transform = ""; });
    });
    root.querySelectorAll(".btn-primary, .btn-gold, .wa-float .dot").forEach((b) => {
      if (b.dataset.mag) return; b.dataset.mag = "1";
      b.classList.add("magnetic");
      b.addEventListener("mousemove", (e) => {
        const r = b.getBoundingClientRect();
        b.style.transform = `translate(${(e.clientX - r.left - r.width / 2) * 0.2}px, ${(e.clientY - r.top - r.height / 2) * 0.3}px)`;
      });
      b.addEventListener("mouseleave", () => (b.style.transform = ""));
    });
  };

  /* scroll progress */
  const bar = document.getElementById("progress");
  const prog = () => { const m = document.documentElement.scrollHeight - innerHeight; bar.style.transform = `scaleX(${m > 0 ? scrollY / m : 0})`; };
  addEventListener("scroll", () => requestAnimationFrame(prog), { passive: true }); prog();

  /* cursor ring (desktop) */
  if (fine && !reduce) {
    const cur = document.getElementById("cursor"), dot = document.getElementById("cursorDot");
    let cx = 0, cy = 0, tx = 0, ty = 0;
    addEventListener("mousemove", (e) => { tx = e.clientX; ty = e.clientY; dot.style.transform = `translate(${tx}px, ${ty}px)`; cur.classList.add("on"); dot.classList.add("on"); });
    document.addEventListener("mouseleave", () => { cur.classList.remove("on"); dot.classList.remove("on"); });
    document.addEventListener("mouseover", (e) => cur.classList.toggle("big", !!e.target.closest("a, button, summary, .p-img, .col, label")));
    (function loop() { cx += (tx - cx) * 0.18; cy += (ty - cy) * 0.18; cur.style.transform = `translate(${cx}px, ${cy}px)`; requestAnimationFrame(loop); })();
  }

  /* run after the page script has rendered its content */
  addEventListener("DOMContentLoaded", () => { updateBadges(); enhance(); sweepImages(); });

  window.Store = {
    WA, COLOURS, SIZES, CATS, products, PAGE,
    esc, fmt, waLink, find, imgSrc, sizesOf, priceFor, priceHTML, qs,
    Cart, Wish, onChange, toast, buzz, card, optsHTML, bindOpts, validOpts, quickAdd, enhance, sweepImages
  };
})();
