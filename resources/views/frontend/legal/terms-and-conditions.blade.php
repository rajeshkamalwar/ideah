@extends('frontend.layout')

@section('pageHeading')
  {{ __('Terms & Conditions') }}
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo) && !empty($seoInfo->meta_keyword_blog))
    {{ $seoInfo->meta_keyword_blog }}
  @else
    IDEAH, terms and conditions, membership, networking, community rules
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo) && !empty($seoInfo->meta_description_blog))
    {{ $seoInfo->meta_description_blog }}
  @else
    {{ __('Terms and conditions for members of the IDEAH business community, including membership, events, and use of our platform.') }}
  @endif
@endsection

@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => __('Terms & Conditions'),
  ])

  <section class="blog-area blog-1 ptb-100">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
          <div class="tinymce-content">
            <p class="lead">
              {{ __('If you are a member of this business community, you agree to follow these rules of IDEAH. These terms help us provide a safe, respectful, and professional space for every entrepreneur.') }}
            </p>

            <h2>{{ __('Membership') }}</h2>
            <ul>
              <li>{{ __('To join the IDEAH community, complete the membership form with correct details about you and your business, and pay the applicable membership fee.') }}</li>
              <li>{{ __('Membership applications are reviewed; approval depends on genuine interest in business networking and collaboration.') }}</li>
              <li>{{ __('As a member, you agree to respect fellow entrepreneurs, uphold community values, and support the network.') }}</li>
            </ul>

            <h2>{{ __('Networking events & programs') }}</h2>
            <p>
              {{ __('IDEAH organises events such as meetups and seminars. Members are invited to connect and learn. We expect positive, respectful participation. You may use these opportunities to grow your business. IDEAH may deny participation if conduct rules are violated.') }}
            </p>

            <h2>{{ __('Use of platform') }}</h2>
            <p>
              {{ __('This platform supports business growth through sharing ideas and networking. Members are expected to be honest and transparent. Do not share false, misleading, or spam content. The platform must not be used for illegal or harmful activities.') }}
            </p>

            <h2>{{ __('Content and promotions') }}</h2>
            <p>
              {{ __('If you promote your business, products, or services through IDEAH channels (such as social media, newsletters, or events), your content must be accurate and up to date. Promotions should be respectful and relevant to this community.') }}
            </p>
            <p>
              {{ __('IDEAH may use photos or videos from events for promotional purposes (for example on our website or social media). If you do not wish to be featured, contact us in advance. We respect the privacy of our members.') }}
            </p>

            <h2>{{ __('Cancellation and refund policy') }}</h2>
            <p>
              {{ __('Membership fees are non-refundable. If you wish to discontinue membership, notify us in writing by email. The process is simple and transparent.') }}
            </p>

            <h2>{{ __('Changes to terms') }}</h2>
            <p>
              {{ __('IDEAH may update these terms from time to time to improve the experience for members. When we make material changes, we will inform you in advance where appropriate.') }}
            </p>

            <h2>{{ __('Contact us') }}</h2>
            <p class="mb-10">
              {{ __('For questions about these terms, contact:') }}
            </p>
            <p class="mb-0">
              <strong>{{ __('Vijay Sharma') }}</strong>, {{ __('Founder') }}, IDEAH Hub<br>
              <i class="fal fa-phone-alt me-1"></i>
              @if (!empty($basicInfo->contact_number))
                <a href="tel:{{ preg_replace('/\s+/', '', $basicInfo->contact_number) }}">{{ $basicInfo->contact_number }}</a>
              @endif
              <br>
              <i class="fal fa-envelope me-1"></i>
              @if (!empty($basicInfo->email_address))
                <a href="mailto:{{ $basicInfo->email_address }}">{{ $basicInfo->email_address }}</a>
              @endif
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
