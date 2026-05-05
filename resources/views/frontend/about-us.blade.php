@extends('frontend.layout')

@section('pageHeading')
  {{ !empty($pageHeading) ? $pageHeading->about_us_title : __('About Us') }}
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo) && !empty($seoInfo->meta_keywords_about_page))
    {{ $seoInfo->meta_keywords_about_page }}
  @else
    IDEAH, Indo Dutch Entrepreneurs Association Holland, business networking Netherlands, international trade, entrepreneurs, Amstelveen
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo) && !empty($seoInfo->meta_description_about_page))
    {{ $seoInfo->meta_description_about_page }}
  @else
    IDEAH connects Dutch, Asian, and Surinamese entrepreneurs for partnerships, mentorship, and global expansion—since 2017.
  @endif
@endsection

@section('content')
  <style>
    .about-ideah-page {
      --ideah-accent: var(--color-primary);
      --ideah-accent-soft: rgba(var(--color-primary-rgb), 0.1);
      --ideah-accent-mid: rgba(var(--color-primary-rgb), 0.18);
      --ideah-surface: var(--color-white);
      --ideah-muted: var(--color-medium);
    }

    .about-ideah-eyebrow {
      display: inline-block;
      font-size: var(--font-sm);
      font-weight: var(--font-semi-bold);
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--ideah-accent);
      margin-bottom: 0.75rem;
    }

    .about-ideah-hero {
      padding-top: 2.5rem;
      padding-bottom: 3rem;
      background: linear-gradient(180deg, var(--bg-light) 0%, var(--color-white) 55%);
    }

    .about-ideah-hero .title {
      font-size: clamp(1.65rem, 4vw, 2.35rem);
      line-height: 1.25;
      letter-spacing: -0.02em;
    }

    .about-ideah-hero .text {
      line-height: 1.75;
      color: var(--color-dark);
    }

    .about-ideah-image-wrap {
      position: relative;
      border-radius: var(--radius-lg, 12px);
      overflow: hidden;
      box-shadow: var(--shadow-md);
    }

    .about-ideah-image-wrap::before {
      content: "";
      position: absolute;
      inset: -3px;
      border-radius: calc(var(--radius-lg, 12px) + 3px);
      background: linear-gradient(135deg, var(--ideah-accent) 0%, rgba(var(--color-primary-rgb), 0.35) 50%, transparent 70%);
      z-index: -1;
      opacity: 0.85;
    }

    .about-ideah-hero-img {
      display: block;
      width: 100%;
      height: auto;
      min-height: 260px;
      max-height: 420px;
      object-fit: cover;
      aspect-ratio: 4/3;
    }

    .about-ideah-caption {
      margin-top: 1rem;
      text-align: center;
      font-size: var(--font-sm);
      color: var(--ideah-muted);
      font-style: italic;
    }

    .about-ideah-stats {
      padding: 2.5rem 0 3rem;
      background: var(--color-white);
      border-block: 1px solid var(--border-color);
    }

    .about-ideah-stat-card {
      text-align: center;
      padding: 1.75rem 1rem;
      border-radius: var(--radius-lg, 12px);
      background: var(--bg-light);
      border: 1px solid var(--border-color);
      height: 100%;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .about-ideah-stat-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-sm);
    }

    .about-ideah-stat-card .stat-num {
      font-family: var(--font-heading);
      font-size: clamp(1.85rem, 4vw, 2.5rem);
      font-weight: var(--font-bold);
      color: var(--ideah-accent);
      line-height: 1.1;
      margin-bottom: 0.35rem;
      letter-spacing: -0.03em;
    }

    .about-ideah-stat-card .card-text {
      color: var(--color-dark);
      font-weight: var(--font-medium);
    }

    .about-ideah-section {
      padding: 3.5rem 0;
    }

    .about-ideah-section--alt {
      background: var(--bg-light);
    }

    .about-ideah-panel {
      background: var(--ideah-surface);
      border-radius: var(--radius-lg, 12px);
      padding: 1.75rem 1.5rem;
      border: 1px solid var(--border-color);
      height: 100%;
      box-shadow: 0 2px 24px rgba(0, 0, 0, 0.04);
    }

    .about-ideah-section--alt .about-ideah-panel {
      background: var(--color-white);
    }

    .about-ideah-mission-card {
      border-radius: var(--radius-lg, 12px);
      border: 1px solid var(--border-color);
      background: var(--color-white);
      height: 100%;
      overflow: hidden;
      transition: box-shadow 0.3s ease, border-color 0.3s ease;
    }

    .about-ideah-mission-card:hover {
      box-shadow: var(--shadow-md);
      border-color: rgba(var(--color-primary-rgb), 0.35);
    }

    .about-ideah-mission-card .card-accent {
      height: 4px;
      background: linear-gradient(90deg, var(--ideah-accent), rgba(var(--color-primary-rgb), 0.5));
    }

    .about-ideah-mission-card .card-content {
      padding: 1.5rem 1.35rem 1.65rem;
    }

    .about-ideah-mission-card .card-title {
      font-size: 1.15rem;
      margin-bottom: 0.85rem;
    }

    .about-ideah-mission-card .text {
      line-height: 1.7;
    }

    .about-ideah-section-title .title {
      position: relative;
      display: inline-block;
      margin-bottom: 0.5rem;
    }

    .about-ideah-section-title.title-center .title::after {
      content: "";
      display: block;
      width: 48px;
      height: 3px;
      margin: 0.65rem auto 0;
      border-radius: 2px;
      background: var(--ideah-accent);
    }

    .about-ideah-feature {
      display: flex;
      gap: 1rem;
      align-items: flex-start;
      padding: 1.15rem 1.1rem;
      border-radius: var(--radius-md, 8px);
      background: var(--color-white);
      border: 1px solid var(--border-color);
      height: 100%;
      transition: background 0.2s ease, border-color 0.2s ease;
    }

    .about-ideah-feature:hover {
      background: var(--ideah-accent-soft);
      border-color: rgba(var(--color-primary-rgb), 0.25);
    }

    .about-ideah-feature .feature-icon {
      flex-shrink: 0;
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: var(--radius-md, 8px);
      background: var(--ideah-accent-mid);
      color: var(--ideah-accent);
      font-size: 1.25rem;
    }

    .about-ideah-feature .text {
      margin: 0;
      line-height: 1.65;
      padding-top: 0.15rem;
    }

    .about-ideah-split-card {
      background: var(--color-white);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-lg, 12px);
      padding: 1.75rem 1.5rem;
      height: 100%;
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.03);
    }

    .about-ideah-section--alt .about-ideah-split-card {
      background: var(--bg-light);
    }

    .about-ideah-list li {
      padding: 0.45rem 0;
      padding-inline-start: 0.25rem;
      border-bottom: 1px dashed var(--border-color);
      line-height: 1.6;
    }

    .about-ideah-list li:last-child {
      border-bottom: none;
    }

    .about-ideah-timeline {
      position: relative;
      padding-inline-start: 0;
    }

    .about-ideah-timeline .timeline-item {
      position: relative;
      padding: 1.25rem 1.35rem 1.35rem 2.25rem;
      margin-bottom: 1rem;
      border-left: none;
      background: var(--color-white);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md, 8px);
      box-shadow: 0 2px 16px rgba(0, 0, 0, 0.04);
    }

    .about-ideah-section--alt .about-ideah-timeline .timeline-item {
      background: var(--bg-light);
    }

    .about-ideah-timeline .timeline-item::before {
      content: "";
      position: absolute;
      left: 0.85rem;
      top: 1.5rem;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--ideah-accent);
      box-shadow: 0 0 0 4px var(--ideah-accent-soft);
    }

    .about-ideah-timeline .timeline-item::after {
      content: "";
      position: absolute;
      left: 1.15rem;
      top: 2.5rem;
      bottom: -1.15rem;
      width: 2px;
      background: linear-gradient(180deg, var(--ideah-accent-soft), transparent);
    }

    .about-ideah-timeline .timeline-item:last-child::after {
      display: none;
    }

    .about-ideah-timeline .year {
      font-family: var(--font-heading);
      font-size: 0.95rem;
      font-weight: var(--font-bold);
      letter-spacing: 0.04em;
      color: var(--ideah-accent);
      margin-bottom: 0.4rem;
    }

    .about-ideah-quote {
      position: relative;
      padding: 2.25rem 2rem 2rem 2.5rem;
      background: linear-gradient(135deg, var(--color-white) 0%, var(--bg-light) 100%);
      border-radius: var(--radius-lg, 12px);
      border: 1px solid var(--border-color);
      box-shadow: var(--shadow-md);
    }

    .about-ideah-quote::before {
      content: "\201C";
      position: absolute;
      top: 0.5rem;
      left: 1rem;
      font-family: Georgia, serif;
      font-size: 4rem;
      line-height: 1;
      color: rgba(var(--color-primary-rgb), 0.2);
      pointer-events: none;
    }

    .about-ideah-quote .quote-text {
      position: relative;
      z-index: 1;
      font-size: 1.1rem;
      line-height: 1.75;
      margin-bottom: 1.25rem;
    }

    .about-ideah-quote .quote-by {
      font-size: var(--font-lg);
    }

    .about-ideah-cta {
      border-radius: var(--radius-lg, 12px);
      padding: 2.25rem 1.75rem;
      text-align: center;
      background: linear-gradient(135deg, rgba(var(--color-primary-rgb), 0.12) 0%, var(--color-white) 45%, var(--bg-light) 100%);
      border: 1px solid rgba(var(--color-primary-rgb), 0.2);
      box-shadow: var(--shadow-sm);
    }

    .about-ideah-cta .title {
      margin-bottom: 0.75rem;
    }

    .about-ideah-cta .text {
      max-width: 36rem;
      margin-left: auto;
      margin-right: auto;
      line-height: 1.7;
    }

    .about-ideah-cta .address-line {
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid var(--border-color);
      font-size: var(--font-sm);
      color: var(--ideah-muted);
    }

    @media (max-width: 575.98px) {
      .about-ideah-hero {
        padding-top: 1.5rem;
      }

      .about-ideah-quote::before {
        font-size: 3rem;
        top: 0.35rem;
      }
    }
  </style>

  <div class="about-ideah-page">
    @includeIf('frontend.partials.breadcrumb', [
        'breadcrumb' => $bgImg->breadcrumb,
        'title' => !empty($pageHeading) ? $pageHeading->about_us_title : __('About Us'),
    ])

    <section class="about-ideah-hero">
      <div class="container">
        <div class="row align-items-center gx-xl-5 gy-5">
          <div class="col-lg-6 order-lg-1" data-aos="fade-up">
            <span class="about-ideah-eyebrow">{{ __('Who We Are') }}</span>
            <h2 class="title mb-20">{{ __('IDEAH — Connections since 2017') }}</h2>
            <p class="text mb-15">
              {{ __('IDEAH (Indo Dutch Entrepreneurs Association Holland) is a growing business association for Dutch, Asian, and Surinamese entrepreneurs. Established in the Netherlands, it is a collaborative platform where importers, exporters, wholesalers, and distributors connect and grow together.') }}
            </p>
            <p class="text mb-0">
              {{ __('We offer a platform for business growth, partnerships, and global expansion. Our community includes 200+ members across sectors such as medical, garments, IT, hospitality, entertainment, and more. IDEAH promotes collaboration, mentorship, and shared opportunities across borders.') }}
            </p>
          </div>
          <div class="col-lg-6 order-lg-2" data-aos="fade-left">
            <figure class="mb-0">
              <div class="about-ideah-image-wrap">
                <img class="about-ideah-hero-img lazyload blur-up"
                  src="{{ asset('assets/img/about-ideah-network.jpg') }}"
                  alt="{{ __('Business professionals collaborating') }}">
              </div>
              <figcaption class="about-ideah-caption">
                {{ __('Stronger networks through trust and shared ambition.') }}
              </figcaption>
            </figure>
          </div>
        </div>
      </div>
    </section>

    <section class="about-ideah-stats">
      <div class="container">
        <div class="row g-3 g-md-4 justify-content-center">
          <div class="col-6 col-md-3">
            <div class="about-ideah-stat-card" data-aos="fade-up">
              <div class="stat-num">200+</div>
              <p class="card-text font-lg mb-0">{{ __('Members') }}</p>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="about-ideah-stat-card" data-aos="fade-up" data-aos-delay="50">
              <div class="stat-num">6+</div>
              <p class="card-text font-lg mb-0">{{ __('Countries') }}</p>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="about-ideah-stat-card" data-aos="fade-up" data-aos-delay="100">
              <div class="stat-num">7+</div>
              <p class="card-text font-lg mb-0">{{ __('Years experience') }}</p>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="about-ideah-stat-card" data-aos="fade-up" data-aos-delay="150">
              <div class="stat-num">50+</div>
              <p class="card-text font-lg mb-0">{{ __('Networking events') }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="about-ideah-section about-ideah-section--alt">
      <div class="container">
        <div class="row gx-xl-5 gy-4">
          <div class="col-lg-6" data-aos="fade-up">
            <div class="about-ideah-panel">
              <div class="section-title title-inline mb-20">
                <h3 class="title mb-0">{{ __('Core purpose') }}</h3>
              </div>
              <ul class="list-unstyled mb-0 about-ideah-list">
                <li><i class="fal fa-check-circle color-primary me-2"></i>{{ __('Strong business networking') }}</li>
                <li><i class="fal fa-check-circle color-primary me-2"></i>{{ __('Cross-border collaboration') }}</li>
                <li class="border-0 pb-0"><i class="fal fa-check-circle color-primary me-2"></i>{{ __('Support for global entrepreneurs') }}</li>
              </ul>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="80">
            <div class="about-ideah-panel">
              <div class="section-title title-inline mb-20">
                <h3 class="title mb-0">{{ __('Expansion path') }}</h3>
              </div>
              <ul class="list-unstyled mb-0 about-ideah-list">
                <li><i class="fal fa-arrow-right color-primary me-2"></i>{{ __('Indo-European business leadership') }}</li>
                <li><i class="fal fa-arrow-right color-primary me-2"></i>{{ __('Growth through cultural exchange') }}</li>
                <li class="border-0 pb-0"><i class="fal fa-arrow-right color-primary me-2"></i>{{ __('Presence in emerging markets') }}</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="row gx-xl-5 pt-40 mt-10">
          <div class="col-lg-4 mb-30 mb-lg-0" data-aos="fade-up">
            <article class="about-ideah-mission-card">
              <div class="card-accent"></div>
              <div class="card-content">
                <h4 class="card-title">{{ __('Our purpose') }}</h4>
                <p class="text mb-0">
                  {{ __('Reduce the gap between international markets by connecting entrepreneurs, enabling partnerships, and supporting smooth entry into new markets—with reliable insights, network opportunities, and access to resources, contacts, and logistics.') }}
                </p>
              </div>
            </article>
          </div>
          <div class="col-lg-4 mb-30 mb-lg-0" data-aos="fade-up" data-aos-delay="60">
            <article class="about-ideah-mission-card">
              <div class="card-accent"></div>
              <div class="card-content">
                <h4 class="card-title">{{ __('Our mission') }}</h4>
                <p class="text mb-0">
                  {{ __('Create a strong business network across regions. Provide a platform to connect, share ideas and opportunities, and offer the tools, resources, and connections entrepreneurs need to succeed in international trade.') }}
                </p>
              </div>
            </article>
          </div>
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="120">
            <article class="about-ideah-mission-card">
              <div class="card-accent"></div>
              <div class="card-content">
                <h4 class="card-title">{{ __('Our vision') }}</h4>
                <p class="text mb-0">
                  {{ __('Become the leading Indo-European entrepreneurial network—growth through cooperation, innovation, and cultural exchange. Long-term presence in emerging markets such as Suriname, Morocco, and Southeast Asia.') }}
                </p>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="about-ideah-section">
      <div class="container">
        <div class="section-title title-center mb-40 about-ideah-section-title" data-aos="fade-up">
          <h2 class="title mb-10">{{ __('What we do') }}</h2>
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <p class="text mb-0 text-center color-medium">
                {{ __('IDEAH provides guidance, contacts, and support for entrepreneurs and businesses expanding internationally.') }}
              </p>
            </div>
          </div>
        </div>
        <div class="row gx-xl-4 gy-3">
          <div class="col-md-6 col-lg-4" data-aos="fade-up">
            <div class="about-ideah-feature">
              <span class="feature-icon"><i class="fal fa-users"></i></span>
              <p class="text">{{ __('Organizing business networking events and meetups') }}</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="40">
            <div class="about-ideah-feature">
              <span class="feature-icon"><i class="fal fa-chart-line"></i></span>
              <p class="text">{{ __('Sharing valuable market insights and trade logistics') }}</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="80">
            <div class="about-ideah-feature">
              <span class="feature-icon"><i class="fal fa-handshake"></i></span>
              <p class="text">{{ __('Promoting cross-border collaboration and partnerships') }}</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4" data-aos="fade-up">
            <div class="about-ideah-feature">
              <span class="feature-icon"><i class="fal fa-lightbulb-on"></i></span>
              <p class="text">{{ __('Guidance and mentorship for startups and established companies') }}</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="40">
            <div class="about-ideah-feature">
              <span class="feature-icon"><i class="fal fa-bullhorn"></i></span>
              <p class="text">{{ __('A platform to support and promote each other\'s businesses') }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="about-ideah-section about-ideah-section--alt">
      <div class="container">
        <div class="row gx-xl-5 gy-4">
          <div class="col-lg-6" data-aos="fade-up">
            <div class="about-ideah-split-card">
              <div class="section-title title-inline mb-20">
                <h3 class="title mb-0">{{ __('Achievements') }}</h3>
              </div>
              <ul class="list-unstyled mb-0 about-ideah-list">
                <li><i class="fal fa-check me-2 color-primary"></i>{{ __('200+ members within 7 years') }}</li>
                <li><i class="fal fa-check me-2 color-primary"></i>{{ __('Cross-border business setups supported') }}</li>
                <li><i class="fal fa-check me-2 color-primary"></i>{{ __('Networking events and seminars in the Netherlands') }}</li>
                <li><i class="fal fa-check me-2 color-primary"></i>{{ __('Foundations to expand into Suriname, Morocco, and other regions') }}</li>
                <li class="border-0 pb-0"><i class="fal fa-check me-2 color-primary"></i>{{ __('Experts from healthcare, fashion, IT, hospitality, and more') }}</li>
              </ul>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="60">
            <div class="about-ideah-split-card">
              <div class="section-title title-inline mb-20">
                <h3 class="title mb-0">{{ __('Why join IDEAH?') }}</h3>
              </div>
              <ul class="list-unstyled mb-0 about-ideah-list">
                <li><i class="fal fa-star me-2 color-primary"></i>{{ __('Trusted professionals across industries') }}</li>
                <li><i class="fal fa-star me-2 color-primary"></i>{{ __('Opportunities in Europe, India, and emerging markets') }}</li>
                <li><i class="fal fa-star me-2 color-primary"></i>{{ __('Market guidance, mentorship, and trade support') }}</li>
                <li><i class="fal fa-star me-2 color-primary"></i>{{ __('Workshops on trade laws, taxation, and logistics') }}</li>
                <li><i class="fal fa-star me-2 color-primary"></i>{{ __('Networking events and verified international contacts') }}</li>
                <li class="border-0 pb-0"><i class="fal fa-star me-2 color-primary"></i>{{ __('Visibility through IDEAH channels and trade shows') }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="about-ideah-section pb-80">
      <div class="container">
        <div class="section-title title-center mb-40 about-ideah-section-title" data-aos="fade-up">
          <h2 class="title mb-10">{{ __('Our journey so far') }}</h2>
        </div>
        <div class="row justify-content-center">
          <div class="col-lg-9 about-ideah-timeline">
            <div class="timeline-item" data-aos="fade-up">
              <span class="year">2017</span>
              <p class="text mb-0">{{ __('IDEAH began in Amstelveen, Netherlands—a platform for entrepreneurs to connect and grow.') }}</p>
            </div>
            <div class="timeline-item" data-aos="fade-up">
              <span class="year">2018–2020</span>
              <p class="text mb-0">{{ __('The community grew to 100 members; local networking events helped members share opportunities.') }}</p>
            </div>
            <div class="timeline-item" data-aos="fade-up">
              <span class="year">2021–2023</span>
              <p class="text mb-0">{{ __('More entrepreneurs joined, especially from Asian and Surinamese backgrounds—richer, more diverse networks.') }}</p>
            </div>
            <div class="timeline-item" data-aos="fade-up">
              <span class="year">2024</span>
              <p class="text mb-0">{{ __('Strategic partnerships to expand into India, Suriname, and Morocco.') }}</p>
            </div>
            <div class="timeline-item mb-0" data-aos="fade-up">
              <span class="year">2025</span>
              <p class="text mb-0">{{ __('200+ members; international business forums and expos for members worldwide.') }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="about-ideah-section about-ideah-section--alt pt-0">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-10 col-xl-9" data-aos="fade-up">
            <blockquote class="about-ideah-quote mb-0">
              <p class="quote-text fst-italic mb-0">
                {{ __('IDEAH was born out of a simple vision: to create a space where Indian and Dutch entrepreneurs could help each other thrive globally. Our journey has just begun, and the possibilities at IDEAH are endless. I invite you to be a part of this exciting network.') }}
              </p>
              <p class="quote-by mb-0 fw-bold mt-20">
                &mdash; {{ __('Vijay Sharma') }}, {{ __('Founder, IDEAH') }}
              </p>
            </blockquote>
          </div>
        </div>
      </div>
    </section>

    <section class="about-ideah-section pt-0 pb-100">
      <div class="container">
        <div class="about-ideah-cta" data-aos="fade-up">
          <h3 class="title">{{ __('About IDEAH') }}</h3>
          <p class="text mb-0">
            {{ __('IDEAH is a global business network connecting Indo-Dutch, Asian, and Surinamese entrepreneurs through partnerships, mentorship, and international trade.') }}
          </p>
          <p class="address-line mb-0">
            KRINGLOOP 167, 1186GW {{ __('Amstelveen') }}, {{ __('Netherlands') }} &mdash; KVK {{ __('Number') }}: 70995265
          </p>
        </div>
      </div>
    </section>
  </div>
@endsection
