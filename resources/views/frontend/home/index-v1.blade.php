@extends('frontend.layout')

@section('pageHeading')
    {{ __('Home') }}
@endsection
@section('metaKeywords')
    @if (!empty($seoInfo))
        {{ $seoInfo->meta_keyword_home }}
    @endif
@endsection

@section('metaDescription')
    @if (!empty($seoInfo))
        {{ $seoInfo->meta_description_home }}
    @endif
@endsection

@section('content')
    <!-- Home-area start-->
    <section class="hero-banner hero-banner-1 header-next border-top pb-60">
        <div class="container container-lg-fluid px-lg-0">
            <div class="row align-items-center" data-aos="fade-up">
                <div class="col-lg-7">
                    <div class="fluid-left">
                        <div class="content-left mb-40">
                            <div class="content">
                                <h1 class="title mb-20" data-aos="fade-up">
                                    {{ !empty($heroSection->title) ? $heroSection->title : 'Are You Looking For A business?' }}
                                </h1>
                                <div class="text mb-30 hero-section-text" data-aos="fade-up" data-aos-delay="100">
                                    @if (!empty($heroSection->text))
                                        {!! $heroSection->text !!}
                                    @endif
                                </div>
                            </div>
                            <div
                                class="hero-banner-search-row d-flex flex-column flex-xl-row align-items-xl-center gap-3 gap-xl-4 mb-20">
                                <div class="banner-filter-form flex-grow-1 min-w-0">
                                    <div class="form-wrapper radius-xl">
                                    <form action="{{ route('frontend.listings') }}" id="searchForm2" method="GET">
                                        <div class="row align-items-center gx-xl-3">
                                            <div class="col-lg-3 col-md-6">
                                                <div class="input-group border-end">
                                                    <label for="search"><i class="ico-shopping-mall"></i></label>
                                                    <input type="text" name="title" id="title" class="form-control"
                                                        placeholder="{{ __('I’m Looking for') }}">
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-6">
                                                <div class="input-group border-end">
                                                    <label for="category"><i class="ico-category"></i></label>
                                                    @include('frontend.home.partials.hero-category-select')
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <div
                                                    class="input-group input-group--hero-location flex-nowrap align-items-center border-end">
                                                    <label for="hero_country"
                                                        class="hero-location-icon flex-shrink-0 d-flex align-items-center mb-0"><i
                                                            class="ico-location-pin"></i></label>
                                                    @include('frontend.home.partials.hero-location-selects')
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-6">
                                                <button type="button" id="searchBtn2"
                                                    class="btn btn-lg btn-primary icon-start w-100">
                                                    <i class="fal fa-search"></i>
                                                    <span class="d-lg-none">
                                                        {{ __('Search Now') }}
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                                @include('frontend.home.partials.hero-ask-ai-button')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="banner-img mb-40">
                        @if ($heroSectionImage)
                            <img class="lazyload blur-up"
                                data-src="{{ asset('assets/img/hero-section/' . $heroSectionImage) }}"alt="Banner">
                        @else
                            <img class="lazyload blur-up" data-src="{{ asset('assets/img/noimage.jpg') }}" alt="Banner">
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- Bg Shape -->
        <div class="shape">
            <img class="shape-1" src="{{ asset('assets/front/images/shape/shape-1.svg') }}" alt="Shape">
            <img class="shape-2" src="{{ asset('assets/front/images/shape/shape-2.svg') }}" alt="Shape">
            <img class="shape-3" src="{{ asset('assets/front/images/shape/shape-3.svg') }}" alt="Shape">
            <img class="shape-4" src="{{ asset('assets/front/images/shape/shape-4.svg') }}" alt="Shape">
            <img class="shape-5" src="{{ asset('assets/front/images/shape/shape-5.svg') }}" alt="Shape">
        </div>
    </section>
    @include('frontend.partials.ask-ai-modal')
    <!-- Home-area end -->

    <!-- Category-area start -->
    @if ($secInfo->category_section_status == 1)
        <section class="category-area category-1 pb-100">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title title-center mb-20" data-aos="fade-up">
                            <h2 class="title"> {{ $catgorySecInfo ? $catgorySecInfo->title : 'CATEGORIES' }}</h2>
                        </div>
                    </div>
                    <div class="col-12">
                        @if (count($categories) < 1)
                            <div class="text-center">
                                <h3 class="mb-0">{{ __('NO CATEGORY FOUND') . '!' }}</h3>
                            </div>
                        @else
                            <div class="swiper pt-30" id="category-slider-1" data-aos="fade-up" data-aos-delay="100">
                                <div class="swiper-wrapper">
                                    @foreach ($categories as $category)
                                        <div class="swiper-slide">
                                            <a href="{{ route('frontend.listings', ['category_id' => $category->slug]) }}">
                                                <div class="category-item radius-md text-center">
                                                    <div class="category-icon">
                                                        <i class="{{ $category->icon }}"></i>
                                                    </div>
                                                    <h3 class="category-title mb-20">{{ $category->name }}</h3>
                                                    <span
                                                        class="category-qty">{{ $category->listing_contents_count }}</span>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                        @endif
                    </div>
                    <!-- Slider Pagination -->
                    <div class="swiper-pagination position-static mt-30" id="category-slider-1-pagination"></div>
                </div>
            </div>
            </div>
            </div>
            <!-- Bg Shape -->
            <div class="shape">
                <img class="shape-1" src="{{ asset('assets/front/images/shape/shape-4.svg') }}" alt="Shape">
                <img class="shape-2" src="{{ asset('assets/front/images/shape/shape-3.svg') }}" alt="Shape">
                <img class="shape-3" src="{{ asset('assets/front/images/shape/shape-1.svg') }}" alt="Shape">
                <img class="shape-4" src="{{ asset('assets/front/images/shape/shape-7.svg') }}" alt="Shape">
                <img class="shape-5" src="{{ asset('assets/front/images/shape/shape-2.svg') }}" alt="Shape">
            </div>
        </section>
    @endif
    <!-- Category-area end -->

    <!-- Product-area start -->
    @if ($secInfo->featured_listing_section_status == 1)
        <section class="product-area pt-100 pb-75 bg-primary-light">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title title-inline mb-30" data-aos="fade-up">
                            <h2 class="title mb-20"><span
                                    class="line">{{ $listingSecInfo ? $listingSecInfo->title : 'LISTINGS' }}
                                </span></h2>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="forAll">
                                <div class="row">
                                    @if (count($listing_contents) < 1)
                                        <h3 class="text-center mt-2">{{ __('NO LISTING FOUND') }}</h3>
                                    @else
                                        @foreach ($listing_contents as $listing_content)
                                            <div class="col-xl-3 col-lg-4 col-sm-6" data-aos="fade-up">
                                                <div class="product-default listing-card-home border radius-md mb-25">
                                                    <figure class="product-img">
                                                        <a href="{{ route('frontend.listing.details', ['slug' => $listing_content->slug, 'id' => $listing_content->id]) }}"
                                                            class="lazy-container ratio ratio-2-3">
                                                            <img class="lazyload"
                                                                data-src="{{ asset('assets/img/listing/' . $listing_content->feature_image) }}"
                                                                alt="{{ optional($listing_content)->title }}">
                                                        </a>
                                                        @if (Auth::guard('web')->check())
                                                            @php
                                                                $user_id = Auth::guard('web')->user()->id;
                                                                $checkWishList = checkWishList(
                                                                    $listing_content->id,
                                                                    $user_id,
                                                                );
                                                            @endphp
                                                        @else
                                                            @php
                                                                $checkWishList = false;
                                                            @endphp
                                                        @endif
                                                        <a href="{{ $checkWishList == false ? route('addto.wishlist', $listing_content->id) : route('remove.wishlist', $listing_content->id) }}"
                                                            class="btn-icon {{ $checkWishList == false ? '' : 'wishlist-active' }}"
                                                            data-tooltip="tooltip" data-bs-placement="top"
                                                            title="{{ $checkWishList == false ? __('Save to Wishlist') : __('Saved') }}">
                                                            <i class="fal fa-heart"></i>
                                                        </a>
                                                    </figure>

                                                    <div class="product-details">
                                                        <div class="listing-card-home__meta">
                                                            @php
                                                                $listingReviewCount = totalListingReview(
                                                                    $listing_content->id,
                                                                );
                                                                $categorySlug = App\Models\ListingCategory::findorfail(
                                                                    $listing_content->category_id,
                                                                );
                                                            @endphp
                                                            <a href="{{ route('frontend.listings', ['category_id' => $categorySlug->slug]) }}"
                                                                title="{{ $listing_content->category_name }}"
                                                                class="product-category listing-card-home__category font-sm icon-start">
                                                                <i
                                                                    class="{{ $listing_content->icon }}"></i>{{ $listing_content->category_name }}
                                                            </a>
                                                        </div>
                                                        <h5 class="product-title listing-card-home__title mb-10"><a
                                                                href="{{ route('frontend.listing.details', ['slug' => $listing_content->slug, 'id' => $listing_content->id]) }}">{{ optional($listing_content)->title }}</a>
                                                        </h5>
                                                        <div
                                                            class="product-ratings listing-card-home__ratings mb-10">
                                                            <div class="ratings">
                                                                <div class="rate"
                                                                    style="background-image:url('{{ asset($rateStar) }}')">
                                                                    <div class="rating-icon"
                                                                        style="background-image: url('{{ asset($rateStar) }}'); width: {{ $listing_content->average_rating * 20 . '%;' }}">
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="ratings-total font-sm">({{ number_format((float) $listing_content->average_rating, 1) }})</span>
                                                                @if ($listingReviewCount > 0)
                                                                    <span
                                                                        class="ratings-total color-medium ms-1 font-sm">{{ $listingReviewCount }}
                                                                        {{ __('Reviews') }}</span>
                                                                @else
                                                                    <span
                                                                        class="listing-card-home__no-reviews font-sm ms-1">{{ __('No reviews yet') }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @php

                                                            $location = implode(
                                                                ', ',
                                                                array_filter([
                                                                    $listing_content->city_id
                                                                        ? App\Models\Location\City::find(
                                                                            $listing_content->city_id,
                                                                        )?->name
                                                                        : null,
                                                                    $listing_content->state_id
                                                                        ? App\Models\Location\State::find(
                                                                            $listing_content->state_id,
                                                                        )?->name
                                                                        : null,
                                                                    $listing_content->country_id
                                                                        ? App\Models\Location\Country::find(
                                                                            $listing_content->country_id,
                                                                        )?->name
                                                                        : null,
                                                                ]),
                                                            );
                                                        @endphp

                                                        <div class="listing-card-home__location product-location icon-start font-sm">
                                                            <i class="fal fa-map-marker-alt"></i>
                                                            <span>{{ $location ?: __('Location not set') }}</span>
                                                        </div>
                                                        <div class="listing-card-home__price-footer">
                                                            @if ($listing_content->max_price && $listing_content->min_price)
                                                                <div class="product-price listing-card-home__price">
                                                                    <span
                                                                        class="listing-card-home__price-label">{{ __('From') }}</span>
                                                                    <h6 class="price mb-0 lh-1">
                                                                        {{ symbolPrice($listing_content->min_price) }} –
                                                                        {{ symbolPrice($listing_content->max_price) }}
                                                                    </h6>
                                                                </div>
                                                            @else
                                                                <a href="{{ route('frontend.listing.details', ['slug' => $listing_content->slug, 'id' => $listing_content->id]) }}"
                                                                    class="listing-card-home__price-request"><i
                                                                        class="fal fa-building me-1"></i>{{ __('View Business') }}</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div><!-- product-default -->
                                            </div>
                                        @endforeach
                                    @endif

                                </div>
                            </div>
                        </div>
                        @if (count($total_listing_contents) > count($listing_contents))
                            <div class="text-center mt-20 mb-25">
                                <a href="{{ route('frontend.listings') }}"
                                    class="btn btn-lg btn-primary">{{ $listingSecInfo ? $listingSecInfo->button_text : 'More' }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- Product-area end -->

    <!-- Work-area start -->
    @if ($secInfo->work_process_section_status == 1)
        <section class="work-area work-process-1 pt-100 pb-75">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title title-inline mb-30" data-aos="fade-up">
                            <h2 class="title mb-20">{{ @$workProcessSecInfo->title }}</h2>
                            @if ($workProcessSecInfo)
                                <a href="#" class="btn btn-lg btn-primary mb-20">
                                    {{ @$workProcessSecInfo->button_text }}</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row gx-xl-5">
                            @foreach ($processes as $process)
                                <div class="col-lg-4 col-sm-6" data-aos="fade-up">
                                    <div class="card radius-lg mb-25">
                                        <div class="card-content radius-md">
                                            <div class="card-step h3"><span
                                                    data-hover="0{{ $process->serial_number }}">0{{ $process->serial_number }}</span>
                                            </div>
                                            <div class="card-icon radius-md">
                                                <i class="{{ $process->icon }}"></i>
                                            </div>
                                            <h3 class="card-title m-0">{{ $process->title }}</h3>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <!-- Bg Shape -->
            <div class="shape">
                <img class="shape-1" src="{{ asset('assets/front/images/shape/shape-7.svg') }}" alt="Shape">
                <img class="shape-2" src="{{ asset('assets/front/images/shape/shape-6.svg') }}" alt="Shape">
                <img class="shape-3" src="{{ asset('assets/front/images/shape/shape-5.svg') }}" alt="Shape">
                <img class="shape-4" src="{{ asset('assets/front/images/shape/shape-2.svg') }}" alt="Shape">
                <img class="shape-5" src="{{ asset('assets/front/images/shape/shape-3.svg') }}" alt="Shape">
            </div>
        </section>
    @endif
    <!-- Work-area end -->

    <!-- Counter-area start -->
    @if ($secInfo->counter_section_status == 1)
        <div class="counter-area pt-100 pb-70">
            <!-- Background Image -->
            <img class="lazyload blur-up bg-img" src="{{ asset('assets/img/' . $counterSectionImage) }}" alt="Bg-img">
            <div class="overlay opacity-65"></div>
            <div class="container">
                <div class="section-title title-center mb-50" data-aos="fade-up">
                    <h2 class="title color-white mb-0">{{ $counterSectionInfo ? $counterSectionInfo->title : '' }}</h2>
                </div>
                <div class="row gx-xl-5 justify-content-center">

                    @foreach ($counters as $counter)
                        <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="150">
                            <div class="card mb-30">
                                <div class="d-flex align-items-center">
                                    <div class="card-icon color-white"><i class="{{ $counter->icon }}"></i></div>
                                    <div class="card-content">
                                        <h2 class="mb-1 color-white"><span class="counter">{{ $counter->amount }}</span>+
                                        </h2>
                                        <p class="card-text font-lg color-white">{{ $counter->title }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    <!-- Counter-area end -->

    <!-- Pricing-area start -->
    @if ($secInfo->package_section_status == 1)
        <section class="pricing-area pt-100 pb-75">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title title-center mb-40" data-aos="fade-up">
                            <h2 class="title">{{ $packageSecInfo ? $packageSecInfo->title : 'Most Affordable Package' }}
                            </h2>
                        </div>
                        <div class="tabs-navigation tabs-navigation-2 text-center mb-40" data-aos="fade-up">
                            <ul class="nav nav-tabs rounded-pill" data-hover="fancyHover">
                                @php
                                    $totalTerms = count($terms);
                                    $middleTerm = intdiv($totalTerms, 2);
                                @endphp
                                @foreach ($terms as $index => $term)
                                    <li class="nav-item {{ $index == $middleTerm ? 'active' : '' }}">
                                        <button
                                            class="nav-link hover-effect rounded-pill {{ $index == $middleTerm ? 'active' : '' }}"
                                            data-bs-toggle="tab" data-bs-target="#{{ strtolower($term) }}"
                                            type="button">
                                            {{ __($term) }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="tab-content">
                            @if (count($terms) < 1)
                                <h3 class="text-center mt-2">{{ __('NO PACKAGE FOUND') }}</h3>
                            @else
                                @foreach ($terms as $index => $term)
                                    <div class="tab-pane fade {{ $index == $middleTerm ? 'show active' : '' }}"
                                        id="{{ strtolower($term) }}">
                                        <div class="row justify-content-center">
                                            @php
                                                $packages = \App\Models\Package::where('status', '1')
                                                    ->where('term', strtolower($term))
                                                    ->get();
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
                                                    <div
                                                        class="pricing-item radius-lg {{ $package->recommended ? 'active' : '' }} mb-30">
                                                        <div class="d-flex align-items-center">
                                                            <div class="icon"><i class="{{ $package->icon }}"></i>
                                                            </div>
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
                                                                    {{ __('Listing') }}
                                                                    ({{ $package->number_of_listing }})
                                                                @else
                                                                    {{ __('Listings') }}
                                                                    ({{ $package->number_of_listing }})
                                                                @endif
                                                            </li>

                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_images_per_listing == 999999)
                                                                    {{ __('Images Per Listing (Unlimited)') }}
                                                                @elseif($package->number_of_images_per_listing == 1)
                                                                    {{ __('Image Per Listing') }}
                                                                    ({{ $package->number_of_images_per_listing }})
                                                                @else
                                                                    {{ __('Images Per Listings') }}
                                                                    ({{ $package->number_of_images_per_listing }})
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
                                                                        {{ __('Amenitie Per Listing') }}
                                                                        ({{ $package->number_of_amenities_per_listing }})
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
                                                                        {{ __('Social Link Per Listing') }}
                                                                        ({{ $package->number_of_social_links }})
                                                                    @else
                                                                        {{ __('Social Links Per Listing') }}
                                                                        ({{ $package->number_of_social_links }})
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
                                                                        {{ __('FAQ Per Listing') }}
                                                                        ({{ $package->number_of_faq }})
                                                                    @else
                                                                        {{ __('FAQs Per Listing') }}
                                                                        ({{ $package->number_of_faq }})
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
                                                                        {{ __('Product') . ' ' }}
                                                                        ({{ $package->number_of_products }})
                                                                    @else
                                                                        {{ __('Products') . ' ' }}
                                                                        ({{ $package->number_of_products }})
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
                                                                    $features = explode(
                                                                        "\n",
                                                                        $package->custom_features,
                                                                    );
                                                                @endphp
                                                                @if (count($features) > 0)
                                                                    @foreach ($features as $key => $value)
                                                                        <li><i
                                                                                class="fal fa-check"></i>{{ __($value) }}
                                                                        </li>
                                                                    @endforeach
                                                                @endif
                                                            @endif

                                                        </ul>
                                                        @auth('vendor')
                                                            <a href="{{ route('vendor.plan.extend.checkout', $package->id) }}"
                                                                class="btn btn-outline btn-lg" title="Purchase"
                                                                target="_self">{{ __('Purchase') }}</a>
                                                        @endauth
                                                        @guest('vendor')
                                                            <a href="{{ route('vendor.login', ['redirectPath' => 'buy_plan', 'package' => $package->id]) }}"
                                                                class="btn btn-outline btn-lg" title="Purchase"
                                                                target="_self">{{ __('Purchase') }}</a>
                                                        @endguest
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>
                                @endforeach
                            @endif
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
    @endif
    <!-- Pricing-area end -->

    <!-- Action banner start -->
    @if ($secInfo->call_to_action_section_status == 1)
        <section class="action-banner pt-100 pb-60">
            <!-- Background Image -->
            <img class="lazyload bg-img blur-up" src="{{ asset('assets/img/' . $callToActionSectionImage) }}"
                alt="Bg-img">
            <div class="overlay opacity-75"></div>

            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <div class="content-title mb-40 aos-init aos-animate" data-aos="fade-up">
                            <h2 class="title text-white mb-15 w-75 w-sm-100">
                                {{ $callToActionSecInfo ? $callToActionSecInfo->title : '' }}</h2>
                            <p class="text-white mb-30 w-50 w-sm-100">
                                {{ $callToActionSecInfo ? $callToActionSecInfo->subtitle : '' }}</p>
                            <div>
                                @if ($callToActionSecInfo && $callToActionSecInfo->button_url && $callToActionSecInfo->button_name)
                                    <a href="{{ $callToActionSecInfo->button_url }}" target="_blank"
                                        class="btn btn-lg btn-primary">
                                        {{ $callToActionSecInfo->button_name }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="image mb-40">
                            <img class="lazyload blur-up radius-md"
                                src="{{ asset('assets/img/' . $callToActionSectionHighlightImage) }}" alt="Image">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- Action banner end -->

    <!-- Blog-area start -->
    @if ($secInfo->blog_section_status == 1)
        <section class="blog-area pt-100 pb-75">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title title-inline mb-30" data-aos="fade-up">
                            <h2 class="title mb-20">
                                {{ !empty($blogSecInfo->title) ? $blogSecInfo->title : 'Read Our Latest Blog' }}
                            </h2>
                            @if (count($blog_count) > count($blogs))
                                <a href="{{ route('blog') }}" class="btn btn-lg btn-primary mb-20">
                                    {{ $blogSecInfo ? $blogSecInfo->button_text : 'More' }} </a>
                            @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row justify-content-center">
                            @if (count($blogs) < 1)
                                <h3 class="text-center mt-2">{{ __('NO POST FOUND') . '!' }}</h3>
                            @else
                                @foreach ($blogs as $blog)
                                    <div class="col-md-6 col-lg-4" data-aos="fade-up">
                                        <article class="card mb-25">
                                            <div class="card-img radius-md">
                                                <a href="{{ route('blog.details', ['slug' => $blog->slug]) }}"
                                                    class="lazy-container ratio ratio-16-10">
                                                    <img class="lazyload"
                                                        data-src="{{ asset('assets/img/blogs/' . $blog->image) }}"
                                                        alt="Blog Image">
                                                </a>
                                            </div>
                                            <div class="content">
                                                <h3 class="card-title">
                                                    <a href="{{ route('blog.details', ['slug' => $blog->slug]) }}">
                                                        {{ $blog->title }}
                                                    </a>
                                                </h3>
                                                <p class="card-text">
                                                    {{ strlen(strip_tags(convertUtf8($blog->content))) > 100 ? substr(strip_tags(convertUtf8($blog->content)), 0, 100) . '...' : strip_tags(convertUtf8($blog->content)) }}
                                                </p>
                                                <a href="{{ route('blog.details', ['slug' => $blog->slug]) }}"
                                                    class="card-btn">{{ __('Read More') }}</a>
                                            </div>
                                        </article>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- Blog-area end -->
    @include('frontend.home.partials.events-section')
    <!-- Modal -->
    @include('frontend.partials.map-modal')
@endsection

@section('script')
    <script>
        window.countryUrl = "{{ route('frontend.get_country') }}";
        window.getStateUrl = "{{ route('frontend.listings.get-state') }}";
        window.getCityUrl = "{{ route('frontend.listings.get-city') }}";
        window.heroSelectCountry = @json(__('Country'));
        window.heroSelectState = @json(__('State / region'));
        window.heroSelectCity = @json(__('City'));
    </script>
    <script src="{{ asset('assets/front/js/hero-location.js') }}"></script>
    <script>
        window.askAiUrl = "{{ route('frontend.listings.ask_ai') }}";
        window.askAiNoResults = @json(__('No direct matches yet — try different keywords or open full listings.'));
        window.askAiNeedFilter = @json(__('Please add keywords, pick a category, or choose a place.'));
    </script>
    <script src="{{ asset('assets/front/js/ask-ai-modal.js') }}"></script>
    <script src="{{ asset('assets/front/js/search-home.js') }}"></script>
@endsection
