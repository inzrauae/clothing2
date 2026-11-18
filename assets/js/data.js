/* =========================================================
   dooo — product catalogue
   Edit this file to add products or change prices.
   - price: number in LKR, or null to show "Price on request"
   - priceLarge: optional price for waist sizes 38"+ (trousers)
   - compare: optional "was" price shown struck through
   - from: true shows "From Rs. …"
   - sizes: "waist" (28–46) or "alpha" (M–3XL)
   - colours: list of colour names (see COLOURS) or null = as shown
   - img: file name inside images/premium/ (.webp)
   ========================================================= */
window.DOOO = (() => {
  const COLOURS = {
    "Navy": "#1f2a4a", "Ivory": "#efe8d6", "Black": "#111111", "Honey Beige": "#c9a77c",
    "Dark Brown": "#4a3326", "Grey": "#7d7f82", "Olive Green": "#5b6440", "Dark Burgundy": "#5a1d2a"
  };
  const SIG_COLOURS = Object.keys(COLOURS);
  const SIZES = {
    waist: ["28", "30", "32", "34", "36", "38", "40", "42", "44", "46"],
    alpha: ["M", "L", "XL", "2XL", "3XL"]
  };
  const CATS = ["Trousers", "Linen", "Frocks", "Sets", "Tops"];

  const sig = { price: 1840, priceLarge: 2040, compare: 2270, sizes: "waist", colours: SIG_COLOURS, signature: true };

  const products = [
    { id: "02", name: "The Boot Cut Luxe", cats: ["Trousers"], img: "d02-bootcut.webp", tag: "Bestseller", ...sig,
      desc: "Our bestselling boot-cut formal trouser with slant pockets — tailored to sculpt and made to move, from the office to evening plans." },
    { id: "03", name: "The Executive High Waist", cats: ["Trousers"], img: "d03-highwaist.webp", tag: "Office", ...sig,
      desc: "A high-waist office pant with side pockets that lengthens the leg and keeps its shape all day." },
    { id: "04", name: "The Tailored Pin Tuck", cats: ["Trousers"], img: "d04-pintuck.webp", tag: "Tailored", ...sig,
      desc: "A high-waist boot cut finished with a sharp pin-tuck crease for a polished, tailored line." },
    { id: "05", name: "The Wide Leg Statement", cats: ["Trousers"], img: "d05-wideleg.webp", tag: "New", ...sig,
      desc: "A high-waist wide-leg trouser with side pockets — fluid, elegant and effortlessly comfortable." },

    { id: "38", name: "Linen Top — Black & White", cats: ["Tops", "Linen"], img: "d38-linen-top.webp", tag: "New", sizes: "alpha", price: null,
      desc: "A modern split-colour linen top in black and white. Breathable, light and easy to style with ivory or black trousers." },
    { id: "37", name: "Super Cutlon Top", cats: ["Tops"], img: "d37-cutlon-top.webp", tag: "New", sizes: "alpha", price: 1490, from: true,
      desc: "A soft, easy-care cutlon top that keeps its shape — a versatile everyday essential." },
    { id: "36", name: "Linen Top with Wide Leg Pants", cats: ["Sets", "Linen"], img: "d36-linen-wideleg.webp", tag: "New", sizes: "alpha", price: null,
      desc: "A coordinated linen top and wide-leg pant set. Breezy, relaxed and put-together in one easy choice." },
    { id: "35", name: "Sleeveless Modern Linen Frock", cats: ["Frocks", "Linen"], img: "d35-linen-frock.webp", sizes: "alpha", price: null,
      desc: "A clean, sleeveless linen frock with a modern silhouette — cool and comfortable for warm days." },
    { id: "34", name: "Cutlon Kurtha Top with Wide Leg Pants", cats: ["Sets"], img: "d34-kurtha-set.webp", sizes: "alpha", price: 4690, from: true,
      desc: "A cutlon kurtha top paired with wide-leg pants — graceful, modest and ready for any occasion." },
    { id: "33", name: "Waistcoat with Wide Leg Pants", cats: ["Sets", "Trousers"], img: "d33-waistcoat-set.webp", sizes: "alpha", price: null,
      desc: "A tailored waistcoat with matching wide-leg pants. A confident, polished set for work and events." },
    { id: "31", name: "Linen Long Kurtha with Wide Leg Pants", cats: ["Sets", "Linen"], img: "d31-linen-kurtha.webp", sizes: "alpha", price: null,
      desc: "A flowing long linen kurtha with wide-leg pants — light, elegant and beautifully breathable." },
    { id: "30", name: "Linen Shirt Blouse with Linen Pant", cats: ["Sets", "Linen"], img: "d30-linen-set.webp", sizes: "alpha", price: null,
      desc: "A linen shirt blouse with a matching linen pant. Relaxed tailoring for easy, elegant days." },
    { id: "29", name: "Blue & Red Check Casual Frock", cats: ["Frocks"], img: "d29-check-frock.webp", sizes: "alpha", price: 1990, from: true,
      desc: "A belted casual frock in a navy, white and red check — easy elegance for everyday wear." },
    { id: "28", name: "Casual Linen Frock — Sky Blue", cats: ["Frocks", "Linen"], img: "d28-blue-frock.webp", sizes: "alpha", price: null,
      desc: "A fresh sky-blue linen frock with a relaxed, flattering fit." },
    { id: "26", name: "Linen Crop Top with Pants", cats: ["Sets", "Linen"], img: "d26-linen-crop.webp", sizes: "alpha", price: null,
      desc: "A linen crop top and pant set — modern, breezy and effortlessly coordinated." },
    { id: "25", name: "Casual Linen Frock — Sand", cats: ["Frocks", "Linen"], img: "d25-linen-frock.webp", sizes: "alpha", price: null,
      desc: "A soft sand-toned linen frock — a timeless neutral that pairs with everything." },
    { id: "24", name: "High Waist Bandless Pin Tuck Pant", cats: ["Trousers"], img: "d24-pintuck-pant.webp", sizes: "waist", price: null,
      desc: "A high-waist bandless trouser with a pin-tuck crease for a smooth, streamlined finish." },
    { id: "22", name: "Casual Frock — Plum", cats: ["Frocks"], img: "d22-casual-frock.webp", sizes: "alpha", price: null,
      desc: "A rich plum casual frock with an easy, flattering shape." },
    { id: "21", name: "Casual Frock — Coral", cats: ["Frocks"], img: "d21-casual-frock.webp", sizes: "alpha", price: null,
      desc: "A bright coral casual frock — cheerful, comfortable and easy to wear." },
    { id: "19", name: "Sleeveless Linen Frock", cats: ["Frocks", "Linen"], img: "d19-sleeveless-frock.webp", sizes: "alpha", price: null,
      desc: "A sleeveless linen frock cut for comfort and cool, breathable wear." },
    { id: "18", name: "Three Quarter Sleeve Linen Frock", cats: ["Frocks", "Linen"], img: "d18-linen-frock.webp", sizes: "alpha", price: null,
      desc: "A graceful linen frock with three-quarter sleeves — elegant coverage with breathable comfort." },
    { id: "17", name: "Mid Waist Casual Short", cats: ["Trousers"], img: "d17-shorts.webp", sizes: "waist", price: null,
      desc: "A mid-waist casual short for relaxed weekends and warm days." },
    { id: "15", name: "High Waist Super Wide Leg Pant", cats: ["Trousers"], img: "d15-wideleg.webp", sizes: "waist", price: null,
      desc: "A dramatic high-waist super wide-leg pant with beautiful movement." },
    { id: "14", name: "High Waist Slant Pocket Office Pant", cats: ["Trousers"], img: "d14-office-pant.webp", sizes: "waist", price: null,
      desc: "A high-waist office pant with slant pockets — sharp, structured and comfortable all day." },
    { id: "13", name: "Mid Waist Casual Pant with Belt", cats: ["Trousers"], img: "d13-casual-pant.webp", sizes: "waist", price: null,
      desc: "A mid-waist casual pant finished with a belt for an easy, smart-casual look." },
    { id: "12", name: "Mid Waist Pencil Cut Office Pant", cats: ["Trousers"], img: "d12-pencil-pant.webp", sizes: "waist", price: null,
      desc: "A mid-waist pencil-cut office pant with a slim, polished line." }
  ];

  return { COLOURS, SIZES, CATS, products };
})();
