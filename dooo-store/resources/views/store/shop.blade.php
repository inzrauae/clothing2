@extends('layouts.store')

@section('page', 'shop')
@section('title', 'Shop Ladies\' Wear — dooo')
@section('description', 'Shop dooo ladies trousers, linen frocks, tops and co-ord sets. Sizes 28 to 46 and M to 3XL. Fast islandwide delivery and inspect before you pay.')

@section('content')
<section class="page-hero">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span id="crumbCat">Shop</span></nav>
    <h1 id="shopTitle">The <em>Collection</em></h1>
    <p class="lead" id="shopLead">Elegant, high-quality ladies' wear: trousers, linen frocks, tops and co-ord sets, crafted for comfort and confidence.</p>
  </div>
</section>

<section class="shop">
  <div class="wrap">
    <div class="shop-bar">
      <div class="search-box">
        <svg><use href="#i-search"/></svg>
        <input class="input" id="search" type="search" placeholder="Search by name or design no…" aria-label="Search products" autocomplete="off">
      </div>
      <div class="shop-tools">
        <label for="sort">Sort</label>
        <select class="input" id="sort">
          <option value="featured">Featured</option>
          <option value="new">Newest designs</option>
          <option value="low">Price: low to high</option>
          <option value="high">Price: high to low</option>
          <option value="name">Name A–Z</option>
        </select>
      </div>
    </div>
    <div class="filters" id="shopFilters" role="tablist" aria-label="Categories"></div>
    <div class="shop-meta">
      <span id="resultCount"></span>
      <a href="{{ url('shop') }}" class="clear-link" id="clearAll" hidden>Clear filters</a>
    </div>
    <div class="p-grid" id="shopGrid"></div>
    <div class="empty" id="shopEmpty" hidden>
      <svg><use href="#i-search"/></svg>
      <h3>No pieces found</h3>
      <p>Try a different search, or ask us on WhatsApp. We'll help you find the right style.</p>
      <a href="{{ url('shop') }}" class="btn btn-ghost">See all designs</a>
    </div>
  </div>
</section>

<div class="cta">
  <div class="wrap">
    <span class="eyebrow">Can't decide?</span>
    <h2>Our stylists are <em>online 24/7.</em></h2>
    <p>Send us a message for size advice, colours or styling ideas. Fast responses, friendly support.</p>
    <div class="cta-actions">
      <a class="btn btn-gold" href="{{ wa_link() }}" target="_blank" rel="noopener"><svg><use href="#i-wa"/></svg>Chat on WhatsApp</a>
      <a class="btn btn-line" href="{{ url('shipping') }}"><svg><use href="#i-truck"/></svg>Shipping &amp; returns</a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ csp_nonce() }}">
@verbatim
(() => {
  const S = Store;
  const grid = document.getElementById("shopGrid"), search = document.getElementById("search"), sort = document.getElementById("sort");
  const cats = ["All", ...S.CATS];
  const LEADS = {
    All: "Elegant, high-quality ladies' wear: trousers, linen frocks, tops and co-ord sets, crafted for comfort and confidence.",
    Trousers: "Tailored to sculpt, made to move. Office, casual, high-waist and wide-leg fits in sizes 28 to 46.",
    Linen: "Breathable, light and effortlessly elegant. Linen frocks, tops and sets for warm days.",
    Frocks: "Easy elegance for every day, from casual checks to modern sleeveless linen.",
    Sets: "Coordinated kurtha, waistcoat and linen sets. One easy choice, beautifully put together.",
    Tops: "Versatile tops and blouses that pair perfectly with your favourite dooo trousers."
  };
  const state = {
    cat: cats.includes(S.qs("cat")) ? S.qs("cat") : "All",
    q: S.qs("q") || "",
    sort: ["featured", "new", "low", "high", "name"].includes(S.qs("sort")) ? S.qs("sort") : "featured"
  };
  search.value = state.q; sort.value = state.sort;

  document.getElementById("shopFilters").innerHTML = cats.map((c) => `<button data-f="${c}" role="tab">${c}</button>`).join("");

  const sync = () => {
    const p = new URLSearchParams();
    if (state.cat !== "All") p.set("cat", state.cat);
    if (state.q) p.set("q", state.q);
    if (state.sort !== "featured") p.set("sort", state.sort);
    history.replaceState(null, "", "shop" + (p.toString() ? "?" + p : ""));
  };

  const render = () => {
    const q = state.q.trim().toLowerCase();
    let list = S.products.filter((p) => (state.cat === "All" || p.cats.includes(state.cat)) &&
      (!q || p.name.toLowerCase().includes(q) || p.id === q.replace(/\D/g, "").padStart(2, "0") || p.cats.join(" ").toLowerCase().includes(q)));
    const price = (p) => (p.price == null ? Infinity : p.price);
    if (state.sort === "new") list = [...list].sort((a, b) => +b.id - +a.id);
    if (state.sort === "low") list = [...list].sort((a, b) => price(a) - price(b));
    if (state.sort === "high") list = [...list].sort((a, b) => (b.price ?? -1) - (a.price ?? -1));
    if (state.sort === "name") list = [...list].sort((a, b) => a.name.localeCompare(b.name));

    document.querySelectorAll("#shopFilters button").forEach((b) => { b.classList.toggle("on", b.dataset.f === state.cat); b.setAttribute("aria-selected", b.dataset.f === state.cat); });
    document.getElementById("shopTitle").innerHTML = state.cat === "All" ? "The <em>Collection</em>" : `${state.cat} <em>by dooo</em>`;
    document.getElementById("shopLead").textContent = LEADS[state.cat];
    document.getElementById("crumbCat").textContent = state.cat === "All" ? "Shop" : state.cat;
    document.title = (state.cat === "All" ? "Shop Ladies' Wear" : state.cat) + " — dooo";
    document.getElementById("resultCount").innerHTML = `Showing <b>${list.length}</b> ${list.length === 1 ? "piece" : "pieces"}${q ? ` for “${S.esc(state.q)}”` : ""}`;
    document.getElementById("clearAll").hidden = state.cat === "All" && !q && state.sort === "featured";
    document.getElementById("shopEmpty").hidden = list.length > 0;
    grid.innerHTML = list.map(S.card).join("");
    S.enhance(grid);
    sync();
  };

  document.getElementById("shopFilters").addEventListener("click", (e) => {
    const b = e.target.closest("[data-f]"); if (!b) return;
    state.cat = b.dataset.f; render();
    b.scrollIntoView({ inline: "center", block: "nearest", behavior: "smooth" });
  });
  let t; search.addEventListener("input", () => { clearTimeout(t); t = setTimeout(() => { state.q = search.value; render(); }, 160); });
  sort.addEventListener("change", () => { state.sort = sort.value; render(); });
  const wantSearch = location.hash === "#search";
  render();
  if (wantSearch) setTimeout(() => search.focus(), 100);
  addEventListener("hashchange", () => { if (location.hash === "#search") { search.focus(); history.replaceState(null, "", location.pathname + location.search); } });
})();
@endverbatim
</script>
@endpush
