@extends('frontend.layout')

@section('pageHeading')
  @if (!empty($pageHeading))
    {{ $pageHeading->pricing_page_title }}
  @else
    {{ __('Pricing') }}
  @endif
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_pricing }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_pricing }}
  @endif
@endsection

@section('content')
  <!-- Page title start-->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->pricing_page_title : __('Pricing'),
  ])
  <!-- Page title end-->

  <section class="pricing-area pt-100 pb-75">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="section-title title-center mb-40" data-aos="fade-up">
            <h2 class="title">{{ $packageSecInfo ? $packageSecInfo->title : 'Most Affordable Package' }}</h2>
          </div>
          <div class="tabs-navigation tabs-navigation-2 text-center mb-40" data-aos="fade-up">
            <ul class="nav nav-tabs rounded-pill bg-light" data-hover="fancyHover">
              @php
                $totalTerms = count($terms);
                $middleTerm = intdiv($totalTerms, 2);
              @endphp
              @foreach ($terms as $index => $term)
                <li class="nav-item {{ $index == $middleTerm ? 'active' : '' }}">
                  <button class="nav-link hover-effect rounded-pill {{ $index == $middleTerm ? 'active' : '' }}"
                    data-bs-toggle="tab" data-bs-target="#{{ strtolower($term) }}" type="button">
                    {{ __($term) }}
                  </button>
                </li>
              @endforeach
            </ul>
          </div>
        </div>
        <div class="col-12">
          <div class="tab-content">
            @foreach ($terms as $index => $term)
              <div class="tab-pane fade {{ $index == $middleTerm ? 'show active' : '' }}" id="{{ strtolower($term) }}">
                <div class="row justify-content-center">
                  @php
                    $packages = \App\Models\Package::where('status', '1')->where('term', strtolower($term))->get();
                    $totalItems = count($packages);
                    $middleIndex = intdiv($totalItems, 2);
                  @endphp
                  @foreach ($packages as $index => $package)
                    @php
                      $permissions = $package->features;
                      if (!empty($package->features)) {
                          $permissions = json_decode($permissions, true);
                      }
                    @endphp
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                      <div class="pricing-item radius-lg {{ $package->recommended ? 'active' : '' }} mb-30">
                        <div class="d-flex align-items-center">
                          <div class="icon"><i class="{{ $package->icon }}"></i></div>
                          <div class="label">
                            <h3> {{ __($package->title) }}</h3>
                            @if ($package->recommended == '1')
                              <span>{{ __('Popular') }}</span>
                            @endif

                          </div>
                        </div>
                        <p class="text"></p>
                        <div class="d-flex align-items-center">
                          <span class="price">{{ symbolPrice($package->price) }}</span>
                          @if ($package->term == 'monthly')
                            <span class="period">/ {{ __('Monthly') }}</span>
                          @elseif($package->term == 'yearly')
                            <span class="period">/ {{ __('Yearly') }}</span>
                          @elseif($package->term == 'lifetime')
                            <span class="period">/ {{ __('Lifetime') }}</span>
                          @endif
                        </div>
                        <h5>{{ __('What\'s Included') }}</h5>
                        <ul class="item-list list-unstyled p-0 pricing-list">

                          <li><i class="fal fa-check"></i>
                            @if ($package->number_of_listing == 999999)
                              {{ __('Listing (Unlimited)') }}
                            @elseif($package->number_of_listing == 1)
                              {{ __('Listing') }} ({{ $package->number_of_listing }})
                            @else
                              {{ __('Listings') }} ({{ $package->number_of_listing }})
                            @endif
                          </li>

                          <li><i class="fal fa-check"></i>
                            @if ($package->number_of_images_per_listing == 999999)
                              {{ __('Images Per Listing (Unlimited)') }}
                            @elseif($package->number_of_images_per_listing == 1)
                              {{ __('Image Per Listing') }} ({{ $package->number_of_images_per_listing }})
                            @else
                              {{ __('Images Per Listings') }} ({{ $package->number_of_images_per_listing }})
                            @endif
                          </li>
                          <li>
                            <i
                              class=" @if (is_array($permissions) && in_array('Listing Enquiry Form', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                            {{ __('Enquiry Form') }}
                          </li>

                          <li>
                            <i
                              class=" @if (is_array($permissions) && in_array('Video', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                            {{ __('Video') }}
                          </li>

                          <li><i
                              class=" @if (is_array($permissions) && in_array('Amenities', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>

                            @if (is_array($permissions) && in_array('Amenities', $permissions))
                              @if ($package->number_of_amenities_per_listing == 999999)
                                {{ __('Amenities Per Listing(Unlimited)') }}
                              @elseif($package->number_of_amenities_per_listing == 1)
                                {{ __('Amenitie Per Listing') }} ({{ $package->number_of_amenities_per_listing }})
                              @else
                                {{ __('Amenities Per Listing') }}
                                ({{ $package->number_of_amenities_per_listing }})
                              @endif
                            @else
                              {{ __('Amenities Per Listing') }}
                            @endif
                          </li>

                          <li><i
                              class=" @if (is_array($permissions) && in_array('Feature', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>

                            @if (is_array($permissions) && in_array('Feature', $permissions))
                              @if ($package->number_of_additional_specification == 999999)
                                {{ __('Feature Per Listing (Unlimited)') }}
                              @elseif($package->number_of_additional_specification == 1)
                                {{ __('Feature Per Listing') }}
                                ({{ $package->number_of_additional_specification }})
                              @else
                                {{ __('Features Per Listing') }}
                                ({{ $package->number_of_additional_specification }})
                              @endif
                            @else
                              {{ __('Feature Per Listing') }}
                            @endif
                          </li>
                          <li><i
                              class=" @if (is_array($permissions) && in_array('Social Links', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>

                            @if (is_array($permissions) && in_array('Social Links', $permissions))
                              @if ($package->number_of_social_links == 999999)
                                {{ __('Social Links Per Listing(Unlimited)') }}
                              @elseif($package->number_of_social_links == 1)
                                {{ __('Social Link Per Listing') }} ({{ $package->number_of_social_links }})
                              @else
                                {{ __('Social Links Per Listing') }} ({{ $package->number_of_social_links }})
                              @endif
                            @else
                              {{ __('Social Link Per Listing') }}
                            @endif
                          </li>
                          <li><i
                              class=" @if (is_array($permissions) && in_array('FAQ', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                            @if (is_array($permissions) && in_array('FAQ', $permissions))
                              @if ($package->number_of_faq == 999999)
                                {{ __('FAQ Per Listing(Unlimited)') }}
                              @elseif($package->number_of_faq == 1)
                                {{ __('FAQ Per Listing') }} ({{ $package->number_of_faq }})
                              @else
                                {{ __('FAQs Per Listing') }} ({{ $package->number_of_faq }})
                              @endif
                            @else
                              {{ __('FAQ Per Listing') }}
                            @endif
                          </li>

                          <li><i
                              class=" @if (is_array($permissions) && in_array('Business Hours', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                            {{ __('Business Hours') }}
                          </li>
                          <li><i
                              class=" @if (is_array($permissions) && in_array('Products', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                            @if (is_array($permissions) && in_array('Products', $permissions))
                              @if ($package->number_of_products == 999999)
                                {{ __('Products (Unlimited)') }}
                              @elseif($package->number_of_products == 1)
                                {{ __('Product') . ' ' }} ({{ $package->number_of_products }})
                              @else
                                {{ __('Products') . ' ' }} ({{ $package->number_of_products }})
                              @endif
                            @else
                              {{ __('Products') }}
                            @endif
                          </li>

                          @if (is_array($permissions) && in_array('Products', $permissions))
                            <li><i class="fal fa-check"></i>
                              @if ($package->number_of_images_per_products == 999999)
                                {{ __('Product Image Per Product (Unlimited)') }}
                              @elseif($package->number_of_images_per_products == 1)
                                {{ __('Product Image Per Product') }}
                                ({{ $package->number_of_images_per_products }})
                              @else
                                {{ __('Product Images Per Product') }}
                                ({{ $package->number_of_images_per_products }})
                              @endif

                            </li>
                          @else
                            <li><i class="fal fa-times not-active"></i>
                              {{ __('Product Image Per Product') }}</li>
                          @endif


                          @if (is_array($permissions) && in_array('Products', $permissions))
                            <li><i
                                class=" @if (is_array($permissions) && in_array('Product Enquiry Form', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                              {{ __('Product Enquiry Form') }} </li>
                          @else
                            <li><i class="fal fa-times not-active"></i>
                              {{ __('Product Enquiry Form') }}</li>
                          @endif
                          <li>
                            <i
                              class=" @if (is_array($permissions) && in_array('Messenger', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                            {{ __('Messenger') }}
                          </li>
                          <li>
                            <i
                              class=" @if (is_array($permissions) && in_array('WhatsApp', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                            {{ __('WhatsApp') }}
                          </li>
                          <li>
                            <i
                              class=" @if (is_array($permissions) && in_array('Telegram', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                            {{ __('Telegram') }}
                          </li>
                          <li>
                            <i
                              class=" @if (is_array($permissions) && in_array('Tawk.To', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                            {{ __('Tawk.To') }}
                          </li>


                          @if (!is_null($package->custom_features))
                            @php
                              $features = explode("\n", $package->custom_features);
                            @endphp
                            @if (count($features) > 0)
                              @foreach ($features as $key => $value)
                                <li><i class="fal fa-check"></i>{{ __($value) }}
                                </li>
                              @endforeach
                            @endif
                          @endif

                        </ul>
                        @auth('vendor')
                          <a href="{{ route('vendor.plan.extend.checkout', $package->id) }}" class="btn btn-outline btn-lg"
                            title="Purchase" target="_self">{{ __('Purchase') }}</a>
                        @endauth
                        @guest('vendor')
                          <a href="{{ route('vendor.login', ['redirectPath' => 'buy_plan', 'package' => $package->id]) }}"
                            class="btn btn-outline btn-lg" title="Purchase" target="_self">{{ __('Purchase') }}</a>
                        @endguest
                      </div>
                    </div>
                  @endforeach

                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    <!-- Bg Shape -->
    <div class="shape">
      <img class="shape-1" src="{{ asset('assets/front/images/shape/shape-4.svg') }}" alt="Shape">
      <img class="shape-2" src="{{ asset('assets/front/images/shape/shape-3.svg') }}" alt="Shape">
      <img class="shape-3" src="{{ asset('assets/front/images/shape/shape-5.svg') }}" alt="Shape">
      <img class="shape-4" src="{{ asset('assets/front/images/shape/shape-6.svg') }}" alt="Shape">
    </div>
  </section>
@endsection
