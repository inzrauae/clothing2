/* dooo admin — small, dependency-free helpers (Chart.js is loaded only on the dashboard) */
(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  /* sidebar (mobile) */
  const side = $(".side"), scrim = $(".scrim");
  const closeSide = () => { side?.classList.remove("open"); scrim?.classList.remove("on"); };
  $(".burger")?.addEventListener("click", () => { side.classList.add("open"); scrim.classList.add("on"); });
  scrim?.addEventListener("click", closeSide);
  addEventListener("keydown", (e) => { if (e.key === "Escape") { closeSide(); $$(".menu.open").forEach((m) => m.classList.remove("open")); } });

  /* user menu */
  $$("[data-menu]").forEach((btn) => {
    const menu = document.getElementById(btn.dataset.menu);
    btn.addEventListener("click", (e) => { e.stopPropagation(); menu.classList.toggle("open"); btn.setAttribute("aria-expanded", menu.classList.contains("open")); });
  });
  document.addEventListener("click", (e) => { $$(".menu.open").forEach((m) => { if (!m.contains(e.target)) m.classList.remove("open"); }); });

  /* confirm before destructive actions */
  const dlg = $("#confirmDlg");
  let pending = null;
  document.addEventListener("submit", (e) => {
    const f = e.target;
    if (!f.dataset.confirm || f.dataset.confirmed) return;
    e.preventDefault();
    if (!dlg || typeof dlg.showModal !== "function") { if (confirm(f.dataset.confirm)) { f.dataset.confirmed = "1"; f.submit(); } return; }
    pending = f;
    $("#confirmText").textContent = f.dataset.confirm;
    $("#confirmOk").textContent = f.dataset.confirmButton || "Delete";
    dlg.showModal();
  });
  $("#confirmOk")?.addEventListener("click", () => { dlg.close(); if (pending) { pending.dataset.confirmed = "1"; pending.submit(); } });
  $$("[data-close-dialog]").forEach((b) => b.addEventListener("click", () => b.closest("dialog").close()));

  /* open <dialog> by id */
  $$("[data-open]").forEach((b) => b.addEventListener("click", () => document.getElementById(b.dataset.open)?.showModal()));

  /* flash dismiss */
  $$(".flash [data-dismiss]").forEach((b) => b.addEventListener("click", () => b.closest(".flash").remove()));
  setTimeout(() => $$(".flash.ok").forEach((f) => { f.style.transition = "opacity .4s"; f.style.opacity = "0"; setTimeout(() => f.remove(), 400); }), 6000);

  /* auto-submit filter forms */
  $$("[data-autosubmit]").forEach((el) => el.addEventListener("change", () => el.form.submit()));

  /* password show/hide */
  $$(".pw button").forEach((b) => b.addEventListener("click", () => {
    const i = b.parentElement.querySelector("input");
    i.type = i.type === "password" ? "text" : "password";
    b.setAttribute("aria-label", i.type === "password" ? "Show password" : "Hide password");
  }));

  /* image drop-zone preview */
  $$(".drop").forEach((zone) => {
    const input = zone.querySelector('input[type="file"]');
    const show = (file) => {
      if (!file || !file.type.startsWith("image/")) return;
      const url = URL.createObjectURL(file);
      zone.querySelector(".ph")?.remove();
      let img = zone.querySelector("img");
      if (!img) { img = document.createElement("img"); zone.prepend(img); }
      img.src = url; img.alt = "Preview";
      const rm = document.getElementById("remove_image"); if (rm) rm.checked = false;
    };
    input.addEventListener("change", () => show(input.files[0]));
    ["dragenter", "dragover"].forEach((ev) => zone.addEventListener(ev, () => zone.classList.add("over")));
    ["dragleave", "drop"].forEach((ev) => zone.addEventListener(ev, () => zone.classList.remove("over")));
  });

  /* show/hide "large size price" when size type changes */
  const sizeType = $("#size_type");
  const toggleLarge = () => $$("[data-waist-only]").forEach((el) => (el.hidden = sizeType.value !== "waist"));
  if (sizeType) { sizeType.addEventListener("change", toggleLarge); toggleLarge(); }

  /* live line totals on order item editor */
  $$("[data-line]").forEach((row) => {
    const price = row.querySelector("[data-price]"), qty = row.querySelector("[data-qty]"), out = row.querySelector("[data-total]");
    const fmt = (n) => "Rs. " + Number(n).toLocaleString("en-US");
    const calc = () => { out.textContent = price.value === "" ? "To confirm" : fmt((+price.value || 0) * (+qty.value || 0)); };
    price.addEventListener("input", calc); qty.addEventListener("input", calc);
  });

  /* copy to clipboard */
  $$("[data-copy]").forEach((b) => b.addEventListener("click", () => {
    navigator.clipboard?.writeText(b.dataset.copy).then(() => toast("Copied to clipboard"));
  }));

  /* toast */
  const toast = (msg) => {
    let t = $(".toast"); if (!t) { t = document.createElement("div"); t.className = "toast"; document.body.append(t); }
    t.textContent = msg; t.classList.add("show"); clearTimeout(t._h); t._h = setTimeout(() => t.classList.remove("show"), 2200);
  };

  /* ---------- dashboard charts ---------- */
  const data = window.DASH;
  if (data && window.Chart) {
    const gold = "#c6a35a", green = "#0f4a32";
    Chart.defaults.font.family = '"Jost", system-ui, sans-serif';
    Chart.defaults.color = "#6b6b63";
    const money = (v) => "Rs. " + Number(v).toLocaleString("en-US");
    const ctx = document.getElementById("salesChart");
    if (ctx) {
      const g = ctx.getContext("2d").createLinearGradient(0, 0, 0, 300);
      g.addColorStop(0, "rgba(15,74,50,.28)"); g.addColorStop(1, "rgba(15,74,50,0)");
      const chart = new Chart(ctx, {
        data: {
          labels: data.labels,
          datasets: [
            { type: "line", label: "Revenue", data: data.revenue, borderColor: green, backgroundColor: g, fill: true, tension: .38, borderWidth: 2.4, pointRadius: 0, pointHoverRadius: 5, pointBackgroundColor: green, yAxisID: "y" },
            { type: "bar", label: "Orders", data: data.orders, backgroundColor: "rgba(198,163,90,.55)", hoverBackgroundColor: gold, borderRadius: 5, maxBarThickness: 14, yAxisID: "y1" }
          ]
        },
        options: {
          responsive: true, maintainAspectRatio: false, interaction: { mode: "index", intersect: false },
          plugins: { legend: { display: false }, tooltip: { backgroundColor: "#0a3524", titleColor: "#e6cf96", padding: 12, cornerRadius: 10, callbacks: { label: (c) => c.dataset.label === "Revenue" ? " Revenue: " + money(c.parsed.y) : " Orders: " + c.parsed.y } } },
          scales: {
            x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
            y: { position: "left", grid: { color: "#f1ece1" }, border: { display: false }, ticks: { callback: (v) => v >= 1000 ? (v / 1000) + "k" : v } },
            y1: { position: "right", grid: { display: false }, border: { display: false }, ticks: { precision: 0 }, suggestedMax: Math.max(4, ...data.orders) + 1 }
          }
        }
      });
      $$("[data-range]").forEach((b) => b.addEventListener("click", () => {
        $$("[data-range]").forEach((x) => x.classList.toggle("on", x === b));
        const n = +b.dataset.range;
        chart.data.labels = data.labels.slice(-n); chart.data.datasets[0].data = data.revenue.slice(-n); chart.data.datasets[1].data = data.orders.slice(-n);
        chart.update();
      }));
    }
    const sctx = document.getElementById("statusChart");
    if (sctx && data.status) {
      new Chart(sctx, {
        type: "doughnut",
        data: { labels: data.status.labels, datasets: [{ data: data.status.values, backgroundColor: data.status.colors, borderWidth: 3, borderColor: "#fff", hoverOffset: 6 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: "70%", plugins: { legend: { display: false }, tooltip: { backgroundColor: "#0a3524", padding: 10, cornerRadius: 10 } } }
      });
    }
  }
})();
