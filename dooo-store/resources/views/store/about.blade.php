@extends('layouts.store')

@section('page', 'about')
@section('title', 'About Us — dooo')
@section('description', 'dooo is a women\'s fashion brand by DEEMIND VENTURES (PVT) LTD, a member of the Convergence Group of Companies. Premium ladies\' wear crafted with care.')

@section('content')
<section class="page-hero">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>About Us</span></nav>
    <h1>About <em>dooo</em></h1>
    <p class="lead">Elegant, high-quality women's fashion that combines style, comfort and confidence.</p>
  </div>
</section>

<!-- About Us -->
<section id="about">
  <div class="wrap">
    <div class="about-intro">
      <div class="rv-up">
        <span class="eyebrow">About Us</span>
        <p class="big" style="margin-top:16px">Over <em>14 years</em> of experience, dedication and innovation, now woven into <em>women's fashion.</em></p>
        <div class="group-card">
          <svg><use href="#i-reg"/></svg>
          <div><b>DEEMIND VENTURES (PVT) LTD</b><span>A proud member of the Convergence Group of Companies, Sri Lanka &amp; Singapore</span></div>
        </div>
      </div>
      <div class="body rv-up d1">
        <p>At Convergence Group of Companies, we proudly bring over 14 years of experience in delivering comprehensive IT solutions and services to globally and locally established leading companies across Sri Lanka and Singapore. Through our dedication, innovation, and commitment to excellence, we have grown to become one of Sri Lanka's emerging and trusted names in the IT industry.</p>
        <p>Beyond the IT sector, our business journey has expanded into multiple industries including tourism, international logistics, and apparel manufacturing, establishing several successful ventures under the Convergence Group umbrella.</p>
        <p>Among these ventures, DEEMIND VENTURES (PVT) LTD was founded with a vision to create elegant, high-quality women's fashion. Under our proudly established fashion brand <b>dooo</b>, we have introduced a growing collection of ladies' apparel designed to combine style, comfort, and confidence.</p>
        <p>We are delighted to present our women's fashion creations to you.</p>
      </div>
    </div>
    <div class="stat-row rv-up">
      <div><b>14+</b><small>Years of group experience</small></div>
      <div><b>2</b><small>Countries: Sri Lanka &amp; Singapore</small></div>
      <div><b>38+</b><small>Designs released</small></div>
      <div><b>15K+</b><small>Women follow dooo</small></div>
    </div>
  </div>
</section>

<!-- Our Story -->
<section class="story" id="story">
  <div class="wrap">
    <figure class="story-figure rv-up">
      <img src="{{ asset('images/our-story.jpg') }}" alt="dooo Our Story: a rail of tailored trousers in neutral tones beside folded trousers, fabric swatches, a measuring tape and dooo packaging" width="1536" height="1024" loading="lazy" decoding="async">
    </figure>
    <div class="story-grid">
      <div class="rv-up">
        <span class="eyebrow">Our Story</span>
        <h2 style="margin:14px 0 18px">Every brand begins with <em style="color:var(--gold)">a vision.</em></h2>
        <p class="lead">Ours began with a passion for empowering women through fashion.</p>
        <span class="tagline">Timeless Fashion. Modern You.</span>
      </div>
      <div class="body rv-up d1">
        <p>At dooo, we believe elegance is not simply about what you wear; it's about how you feel. Confidence, comfort, and timeless style are at the heart of everything we create.</p>
        <p>Our journey started under DEEMIND VENTURES (PVT) LTD, a proud member of the Convergence Group of Companies, with a simple goal: to design premium ladies' wear that blends modern fashion with everyday comfort.</p>
        <p>From carefully selected fabrics to thoughtfully crafted fits, every dooo collection is designed to celebrate the beauty, strength, and individuality of women. We pay attention to every detail to ensure our products not only look elegant but also feel effortless to wear.</p>
        <p>As fashion continues to evolve, so do we. Inspired by contemporary trends and the needs of modern women, we are committed to creating styles that are sophisticated, versatile, and confidently feminine.</p>
        <p>Today, dooo continues to grow as a fashion brand built on quality, trust, and elegance, bringing timeless fashion closer to women who value both style and comfort.</p>
        <div class="story-icons">
          <div><svg><use href="#i-leaf"/></svg>Quality<br>fabrics</div>
          <div><svg><use href="#i-ruler"/></svg>Perfect<br>fit</div>
          <div><svg><use href="#i-heart"/></svg>Made for<br>comfort</div>
          <div><svg><use href="#i-users"/></svg>Made for<br>you</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Mission -->
<section class="mission" id="mission">
  <div class="wrap mission-grid">
    <div class="rv-up">
      <span class="eyebrow">Our Mission</span>
      <h2 style="margin-top:14px">Empowering women <em>through fashion.</em></h2>
      <blockquote>“To create elegant, high-quality apparel that combines comfort, confidence, and modern style.”</blockquote>
    </div>
    <ul class="mission-list rv-up d1">
      <li><span><svg><use href="#i-gem"/></svg></span><div><b>Premium in every piece</b>Ladies' wear crafted with premium materials, attention to detail and exceptional quality standards, so every customer experiences both beauty and comfort.</div></li>
      <li><span><svg><use href="#i-spark"/></svg></span><div><b>Timeless &amp; versatile</b>Fashionable, sophisticated designs that reflect evolving trends while remaining timeless and versatile for everyday life.</div></li>
      <li><span><svg><use href="#i-chat"/></svg></span><div><b>Relationships built on trust</b>Lasting relationships with our customers through trust, reliability and outstanding service, making dooo a brand women can always feel confident choosing.</div></li>
      <li><span><svg><use href="#i-users"/></svg></span><div><b>Celebrating every woman</b>Through innovation, creativity and dedication, we aim to become a trusted, inspiring brand that celebrates the individuality, strength and elegance of every woman.</div></li>
    </ul>
  </div>
</section>

<!-- Why choose us -->
<section id="why">
  <div class="wrap">
    <div class="sec-head">
      <div><span class="eyebrow">Why Choose Us</span><h2>More than just fashion</h2></div>
      <p class="lead">At dooo, we are dedicated to creating confidence, comfort and timeless elegance for every woman.</p>
    </div>
    <div class="why-grid">
      <div class="why-card rv-up"><span class="num">01</span><svg><use href="#i-gem"/></svg><h4>Premium Quality</h4><p>We carefully select high-quality fabrics and maintain strict production standards to ensure every piece delivers comfort, durability, and style.</p></div>
      <div class="why-card rv-up d1"><span class="num">02</span><svg><use href="#i-spark"/></svg><h4>Elegant &amp; Modern Designs</h4><p>Our collections are inspired by modern fashion trends while maintaining a timeless and sophisticated look that fits every lifestyle.</p></div>
      <div class="why-card rv-up d2"><span class="num">03</span><svg><use href="#i-ruler"/></svg><h4>Perfect Fit &amp; Comfort</h4><p>Every design is created with attention to detail, offering flattering fits that make women feel confident and comfortable throughout the day.</p></div>
      <div class="why-card rv-up"><span class="num">04</span><svg><use href="#i-reg"/></svg><h4>Trusted Brand</h4><p>As a venture under DEEMIND VENTURES (PVT) LTD and the Convergence Group of Companies, we are committed to professionalism, reliability, and customer satisfaction.</p></div>
      <div class="why-card rv-up d1"><span class="num">05</span><svg><use href="#i-heart"/></svg><h4>Customer-Focused Experience</h4><p>Your satisfaction is our priority. From product quality to customer service, we aim to provide a smooth and enjoyable shopping experience.</p></div>
      <div class="why-card rv-up d2"><span class="num">06</span><svg><use href="#i-hanger"/></svg><h4>Continuously Evolving Fashion</h4><p>We constantly introduce fresh styles and new collections based on customer preferences and evolving fashion trends.</p></div>
    </div>
    <div class="closing rv-up">
      <p>At dooo, we don't just create ladies' wear. We create fashion that empowers women to feel <em>elegant, confident, and beautifully themselves.</em></p>
      <div style="margin-top:30px;display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="btn btn-primary" href="{{ url('shop') }}">Shop the collection</a>
        <a class="btn btn-ghost" href="{{ url('contact') }}">Get in touch</a>
      </div>
    </div>
  </div>
</section>

<div class="ribbon dark" aria-hidden="true"><div class="rt">
  <span>dooo</span><span class="o">Fashionably Elegant</span><span>Timeless Fashion</span><span class="o">Modern You</span><span>Premium Quality</span>
  <span>dooo</span><span class="o">Fashionably Elegant</span><span>Timeless Fashion</span><span class="o">Modern You</span><span>Premium Quality</span>
</div></div>
@endsection
