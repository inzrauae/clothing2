/* dooo — home page */
(() => {
  const S = window.Store;
  const { esc, fmt, imgSrc, COLOURS } = S;

  /* signature trousers */
  const sig = S.products.filter((p) => p.signature);
  document.getElementById("trGrid").innerHTML = sig.map((t, i) => `
  <article class="tr-card d${i % 4}">
    <a class="tr-img" href="product.html?id=${t.id}"><span class="tag">${esc(t.tag)}</span><img src="${imgSrc(t)}" data-ph="Design No · ${t.id}" alt="${esc(t.name)}" loading="lazy" decoding="async"></a>
    <div class="tr-body">
      <div class="dn">Design No · ${t.id}</div>
      <h3><a href="product.html?id=${t.id}">${esc(t.name)}</a></h3>
      <div class="price"><s>${fmt(t.compare)}</s>${fmt(t.price)}</div>
      <div class="sizes">Size 28 to 46</div>
      <div class="swatches">${t.colours.map((c) => `<i style="background:${COLOURS[c]}" title="${esc(c)}"></i>`).join("")}</div>
      <div class="card-actions">
        <a class="ca-1" href="product.html?id=${t.id}">View Details</a>
        <a class="ca-2" href="#" data-quick="${t.id}">Add to Bag</a>
      </div>
      <div class="card-trust"><span><svg><use href="#i-box"/></svg>Inspect first</span><span><svg><use href="#i-cash"/></svg>Pay on delivery</span></div>
    </div>
  </article>`).join("");

  /* new arrivals (8 per filter) */
  const pGrid = document.getElementById("pGrid");
  const more = document.getElementById("arrivalsMore");
  const rest = S.products.filter((p) => !p.signature);
  const setFilter = (f) => {
    document.querySelectorAll("#filters button").forEach((b) => b.classList.toggle("on", b.dataset.f === f));
    const list = (f === "All" ? rest : S.products.filter((p) => p.cats.includes(f))).slice(0, 8);
    pGrid.innerHTML = list.map(S.card).join("");
    more.href = f === "All" ? "shop.html" : `shop.html?cat=${f}`;
    more.firstChild.textContent = f === "All" ? "View all designs " : `View all ${f.toLowerCase()} `;
    S.enhance(pGrid);
  };
  setFilter("All");
  document.getElementById("filters").addEventListener("click", (e) => { if (e.target.dataset.f) setFilter(e.target.dataset.f); });

  /* sizes */
  const sz = document.getElementById("sizesRow");
  sz.innerHTML = S.SIZES.waist.map((s, i) => `<span style="--i:${i}">${s}</span>`).join("");
  new IntersectionObserver((es, o) => es.forEach((e) => { if (e.isIntersecting) { sz.classList.add("in"); o.disconnect(); } }), { threshold: 0.3 }).observe(sz);

  /* mobile: swipe carousels with dots */
  const mq = matchMedia("(max-width:760px)");
  document.querySelectorAll(".tr-grid, .col-grid, .pr-cards, .rv-grid").forEach((row) => {
    row.classList.add("swipe");
    const dots = document.createElement("div"); dots.className = "swipe-dots";
    dots.innerHTML = [...row.children].map((_, i) => `<i${i ? "" : ' class="on"'}></i>`).join("");
    row.after(dots);
    row.addEventListener("scroll", () => {
      const mid = row.scrollLeft + row.clientWidth / 2; let best = 0, bd = 1e9;
      [...row.children].forEach((k, i) => { const d = Math.abs(k.offsetLeft - row.offsetLeft + k.offsetWidth / 2 - mid); if (d < bd) { bd = d; best = i; } });
      [...dots.children].forEach((d, i) => d.classList.toggle("on", i === best));
    }, { passive: true });
  });
  /* mobile: sticky filter chips sit right above the grid */
  const filters = document.getElementById("filters"), home = filters.parentElement;
  const placeFilters = () => (mq.matches ? pGrid.before(filters) : home.append(filters));
  placeFilters(); mq.addEventListener("change", placeFilters);

  /* ---------- animated hero ---------- */
  const reduce = matchMedia("(prefers-reduced-motion: reduce)").matches;
  const fine = matchMedia("(hover:hover) and (pointer:fine)").matches;
  const h1 = document.querySelector(".hero h1");
  const wrapWords = (node) => [...node.childNodes].forEach((n) => {
    if (n.nodeType === 3) {
      const frag = document.createDocumentFragment();
      n.textContent.split(/(\s+)/).forEach((t) => {
        if (!t) return;
        if (/^\s+$/.test(t)) { frag.append(t); return; }
        const w = document.createElement("span"); w.className = "w";
        const s = document.createElement("span"); s.textContent = t; w.append(s); frag.append(w);
      });
      n.replaceWith(frag);
    } else if (n.nodeType === 1 && n.tagName !== "BR") wrapWords(n);
  });
  wrapWords(h1);
  h1.classList.remove("rv-up"); h1.classList.add("split");
  h1.querySelectorAll(".w>span").forEach((s, i) => (s.style.transitionDelay = i * 0.09 + "s"));

  /* loader — shown once per visit */
  const loader = document.getElementById("loader");
  let seen = false; try { seen = sessionStorage.getItem("dooo-loaded") === "1"; sessionStorage.setItem("dooo-loaded", "1"); } catch (e) {}
  const start = () => { loader.classList.add("done"); setTimeout(() => h1.classList.add("go"), reduce || seen ? 0 : 350); };
  if (reduce || seen) { loader.style.display = "none"; start(); }
  else {
    let fired = false; const go = () => { if (!fired) { fired = true; start(); } };
    addEventListener("load", () => setTimeout(go, mq.matches ? 300 : 900));
    setTimeout(go, mq.matches ? 1500 : 2600);
  }

  /* count-up numbers */
  const cio = new IntersectionObserver((es) => es.forEach((e) => {
    if (!e.isIntersecting) return; cio.unobserve(e.target);
    const el = e.target, m = el.textContent.trim().match(/^(\d+)(.*)$/); if (!m || reduce) return;
    const end = +m[1], suf = m[2], t0 = performance.now(), dur = 1600;
    const tick = (t) => { const p = Math.min(1, (t - t0) / dur); el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))) + suf; if (p < 1) requestAnimationFrame(tick); };
    el.textContent = "0" + suf; requestAnimationFrame(tick);
  }), { threshold: 0.6 });
  document.querySelectorAll(".badge b, .rb-stats b, .rv-score b").forEach((c) => cio.observe(c));

  /* parallax + sparkles */
  const hero = document.querySelector(".hero"), frame = document.querySelector(".hero-frame"), copy = document.querySelector(".hero-copy");
  let mx = 0, my = 0, ticking = false;
  const render = () => {
    ticking = false;
    if (reduce || scrollY > innerHeight * 1.2) return;
    frame.style.transform = `translate3d(${mx * 14}px, ${scrollY * -0.08 + my * 10}px, 0)`;
    copy.style.transform = `translate3d(${mx * -6}px, ${scrollY * 0.12}px, 0)`;
  };
  const req = () => { if (!ticking) { ticking = true; requestAnimationFrame(render); } };
  if (!mq.matches) addEventListener("scroll", req, { passive: true });
  if (fine && !reduce) hero.addEventListener("mousemove", (e) => {
    const r = hero.getBoundingClientRect(); mx = (e.clientX - r.left) / r.width - 0.5; my = (e.clientY - r.top) / r.height - 0.5; req();
  });
  if (!reduce) for (let i = 0; i < 16; i++) {
    const s = document.createElement("i"); s.className = "spark";
    s.style.left = Math.random() * 100 + "%"; s.style.top = 30 + Math.random() * 65 + "%";
    s.style.setProperty("--d", 6 + Math.random() * 6 + "s");
    s.style.setProperty("--dl", Math.random() * -12 + "s");
    s.style.setProperty("--s", 8 + Math.random() * 10 + "px");
    hero.append(s);
  }
})();
