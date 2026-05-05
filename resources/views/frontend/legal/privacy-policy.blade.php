@extends('frontend.layout')

@section('pageHeading')
  {{ __('Privacy Policy') }}
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo) && !empty($seoInfo->meta_keyword_blog))
    {{ $seoInfo->meta_keyword_blog }}
  @else
    IDEAH, privacy policy, data protection, members, Netherlands
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo) && !empty($seoInfo->meta_description_blog))
    {{ $seoInfo->meta_description_blog }}
  @else
    {{ __('How IDEAH collects, uses, and protects your personal information as a member of our business community.') }}
  @endif
@endsection

@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => __('Privacy Policy'),
  ])

  <section class="blog-area blog-1 ptb-100">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
          <div class="tinymce-content">
            <p class="lead">
              {{ __('IDEAH respects the privacy of our members and customers, and we want you to feel comfortable and confident in this community. This is a growing business community in which every member is respected. Whether you are attending our events, networking with members, or sharing your business ideas, we treat your personal information with care.') }}
            </p>

            <h2>{{ __('Information we collect and use') }}</h2>
            <p>
              {{ __('When you join the IDEAH community we collect basic details such as your full name, email address, phone number, and information related to your company or business (for example firm name, industry, country). We may also collect photos or videos from our events, with your consent.') }}
            </p>
            <p>
              {{ __('We use this information for legitimate purposes only: to communicate with you, share important updates, events, seminars, and networking meetups; to help connect you with professionals in your field and potential partners; for collaboration among members; and to send newsletters, invitations, and announcements by email or WhatsApp where appropriate.') }}
            </p>

            <h2>{{ __('Sharing of information') }}</h2>
            <p>
              {{ __('IDEAH is a business-growing community. We do not sell or rent your information. We may share basic business-related information within our member network to help build connections and promote partnerships.') }}
            </p>
            <p>
              {{ __('If you attend business events or meetups, your photo or business name may appear on our website or social media to highlight community participation. You may request removal if you wish.') }}
            </p>

            <h2>{{ __('Data security and your rights') }}</h2>
            <p>
              {{ __('We take the security of member data seriously and use appropriate measures so your data is not disclosed without authorization. Access is limited to trusted IDEAH team members when necessary.') }}
            </p>
            <p>
              {{ __('You remain in control: you may update your contact or business details by contacting us by email. You may opt out of emails or WhatsApp messages, and you may request removal of your data when you leave the community.') }}
            </p>

            <h2>{{ __('Contact us') }}</h2>
            <p class="mb-10">
              {{ __('If you have questions about how we handle your data, you can reach us at:') }}
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
