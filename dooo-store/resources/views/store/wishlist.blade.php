@extends('layouts.store')

@section('page', 'wishlist')
@section('title', 'Wishlist — dooo')
@section('noindex', '1')

@section('content')
<section class="page-hero">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>Wishlist</span></nav>
    <h1>Saved <em>for you</em></h1>
    <p class="lead">Pieces you've fallen for. Add them to your bag or share the list with us on WhatsApp.</p>
  </div>
</section>

<section class="shop">
  <div class="wrap">
    <div class="shop-meta" id="wishMeta"></div>
    <div class="p-grid" id="wishGrid"></div>
    <div class="empty" id="wishEmpty" hidden>
      <svg><use href="#i-heart"/></svg>
      <h3>Nothing saved yet</h3>
      <p>Tap the ♡ on any piece to keep it here for later.</p>
      <a href="{{ url('shop') }}" class="btn btn-primary">Explore the collection</a>
    </div>
  </div>
</section>
@endsection

@push('scripts')
@verbatim
<script>
(() => {
  const S = Store;
  const render = () => {
    const list = S.Wish.list();
    document.getElementById("wishEmpty").hidden = list.length > 0;
    document.getElementById("wishMeta").innerHTML = list.length
      ? `<span><b>${list.length}</b> saved ${list.length === 1 ? "piece" : "pieces"}</span>
         <a class="clear-link" target="_blank" rel="noopener" href="${S.waLink(`Hi dooo! I'd like to ask about these designs: ${list.map((p) => `No ${p.id} (${p.name})`).join(", ")}`)}">Ask about these on WhatsApp</a>`
      : "";
    const grid = document.getElementById("wishGrid");
    grid.innerHTML = list.map(S.card).join("");
    S.enhance(grid);
    grid.querySelectorAll(".rv-up").forEach((el) => el.classList.add("in"));
  };
  S.onChange(render);
  render();
})();
</script>
@endverbatim
@endpush
