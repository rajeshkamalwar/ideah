@extends('frontend.layout')
@section('pageHeading')
    @if (!empty($pageHeading))
        {{ $pageHeading->listing_page_title }}
    @else
        {{ __('Pricing') }}
    @endif
@endsection

@section('metaKeywords')
    @if (!empty($seoInfo))
        {{ $seoInfo->meta_keyword_listings }}
    @endif
@endsection

@section('metaDescription')
    @if (!empty($seoInfo))
        {{ $seoInfo->meta_description_listings }}
    @endif
@endsection

@section('content')

    <!-- Listing-map-area start -->
    <div class="listing-map-area header-next border-top pt-40">
        <div class="container">
            <div class="row">
                <div class="col-xl-3" data-aos="fade-up">
                    @include('frontend.listing.side-bar')
                </div>
                <div class="col-xl-9" data-aos="fade-up">
                    <div class="product-sort-area pb-15">
                        <div class="row align-items-center">
                            <div class="col-4 d-xl-none">
                                <button class="btn btn-sm btn-outline icon-end radius-sm mb-20" type="button"
                                    data-bs-toggle="offcanvas" data-bs-target="#widgetOffcanvas"
                                    aria-controls="widgetOffcanvas">
                                    <i class="fal fa-filter"></i>
                                </button>
                            </div>
                            <div class="col-sm-8 col-xl-12">
                                <div class="sort-item d-flex align-items-center justify-content-end mb-20">
                                    <div class="item">
                                        <form>
                                            <label class="color-dark" for="select_sort">{{ __('Sort By') }}:</label>
                                            <select name="select_sort" id="select_sort" class="niceselect right color-dark">
                                                <option {{ request()->input('sort') == 'new' ? 'selected' : '' }}
                                                    value="new">
                                                    {{ __('Date : Newest on top') }}
                                                </option>
                                                <option {{ request()->input('sort') == 'old' ? 'selected' : '' }}
                                                    value="old">
                                                    {{ __('Date : Oldest on top') }}
                                                </option>
                                                <option {{ request()->input('sort') == 'low' ? 'selected' : '' }}
                                                    value="low">
                                                    {{ __('Price : Low to High') }}</option>
                                                <option {{ request()->input('sort') == 'high' ? 'selected' : '' }}
                                                    value="high">
                                                    {{ __('Price : High to Low') }}</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="search-container">
                        @if (count($featured_contents) < 1 && count($listing_contents) < 1)
                            <div class="p-4 text-center bg-light radius-md">
                                <h3 class="mb-0">{{ __('NO LISTING FOUND') }}</h3>
                            </div>
                        @else
                            <div class="row">
                                @foreach ($featured_contents as $listing_content)
                                    <div class="col-md-6 col-lg-4" data-aos="fade-up">
                                        <div class="product-default border radius-md mb-25 active">
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
                                                        $checkWishList = checkWishList($listing_content->id, $user_id);
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
                                                <div class="product-top mb-10">
                                                    @php
                                                        $categorySlug = App\Models\ListingCategory::findorfail(
                                                            $listing_content->category_id,
                                                        );
                                                    @endphp
                                                    <a href="{{ route('frontend.listings', ['category_id' => $categorySlug->slug]) }}"
                                                        title="Link" class="product-category font-sm icon-start">
                                                        <i
                                                            class="{{ $listing_content->icon }}"></i>{{ $listing_content->category_name }}
                                                    </a>
                                                </div>
                                                <h6 class="product-title mb-10"><a
                                                        href="{{ route('frontend.listing.details', ['slug' => $listing_content->slug, 'id' => $listing_content->id]) }}">{{ optional($listing_content)->title }}</a>
                                                </h6>
                                                <div class="product-ratings mb-10">
                                                    <div class="ratings">
                                                        <div class="rate"
                                                            style="background-image: url('{{ asset($rateStar) }}')">
                                                            <div class="rating-icon"
                                                                style="background-image: url('{{ asset($rateStar) }}'); width: {{ $listing_content->average_rating * 20 . '%;' }}">
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="ratings-total font-sm">({{ number_format($listing_content->average_rating, 2) }})</span>
                                                        <span
                                                            class="ratings-total color-medium ms-1 font-sm">{{ totalListingReview($listing_content->id) }}
                                                            {{ __('Reviews') }}</span>
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

                                                <span class="product-location icon-start font-sm"><i
                                                        class="fal fa-map-marker-alt"></i>{{ $location }}
                                                </span>
                                                @if ($listingbs->google_map_api_key_status == 1 && !empty($listing_content->distance))
                                                    <span class="font-sm icon-start d-block">
                                                        <i class="fas fa-map-signs"></i>
                                                        {{ number_format($listing_content->distance, 2) }}
                                                        {{ __('kilometers away') }}
                                                    </span>
                                                @endif
                                                <div
                                                    class="d-flex align-items-center justify-content-between mt-10 pt-10 border-top flex-wrap gap-2">
                                                    <div class="product-price flex-grow-1 min-w-0">
                                                        @if ($listing_content->max_price && $listing_content->min_price)
                                                            <span class="color-medium me-2">{{ __('From') }}</span>
                                                            <h6 class="price mb-0 lh-1 d-inline">
                                                                {{ symbolPrice($listing_content->min_price) }} -
                                                                {{ symbolPrice($listing_content->max_price) }}
                                                            </h6>
                                                        @else
                                                            <a href="{{ route('frontend.listing.details', ['slug' => $listing_content->slug, 'id' => $listing_content->id]) }}"
                                                                class="listing-card-home__price-request d-block w-100 text-center text-decoration-none">
                                                                <i class="fal fa-building me-1"></i>{{ __('View Business') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
                                                        @php
                                                            $claimed =
                                                                !empty($listing_content->has_pending_claim) &&
                                                                $listing_content->has_pending_claim;
                                                        @endphp
                                                        @if (is_null($listing_content->vendor_id) || $listing_content->vendor_id == 0)
                                                            <a @if (!$claimed) href="#" data-bs-toggle="modal" data-bs-target="#ClaimRequestModal" @endif
                                                                class="btn btn-primary btn-sm claim-btn {{ $claimed ? 'disabled-link' : '' }}"
                                                                data-tooltip="tooltip" data-bs-placement="right"
                                                                title="{{ $claimed ? __('Already claimed, awaiting fulfillment') : __('Claim Listing') }}"
                                                                data-listing-id="{{ $listing_content->id }}"
                                                                data-vendor-id="{{ $listing_content->vendor_id }}"
                                                                aria-disabled="{{ $claimed ? 'true' : 'false' }}">
                                                                {{ $claimed ? __('Claimed') : __('Claim') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>

                                            </div>

                                        </div><!-- product-default -->
                                    </div>
                                @endforeach
                                @foreach ($listing_contents as $listing_content)
                                    <div class="col-md-6 col-lg-4" data-aos="fade-up">
                                        <div class="product-default border radius-md mb-25">
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
                                                        $checkWishList = checkWishList($listing_content->id, $user_id);
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
                                                <div class="product-top mb-10">
                                                    @php
                                                        $categorySlug = App\Models\ListingCategory::findorfail(
                                                            $listing_content->category_id,
                                                        );
                                                    @endphp
                                                    <a href="{{ route('frontend.listings', ['category_id' => $categorySlug->slug]) }}"
                                                        title="Link" class="product-category font-sm icon-start">
                                                        <i
                                                            class="{{ $listing_content->icon }}"></i>{{ $listing_content->category_name }}
                                                    </a>
                                                </div>
                                                <h6 class="product-title mb-10"><a
                                                        href="{{ route('frontend.listing.details', ['slug' => $listing_content->slug, 'id' => $listing_content->id]) }}">{{ optional($listing_content)->title }}</a>
                                                </h6>
                                                <div class="product-ratings mb-10">
                                                    <div class="ratings">
                                                        <div class="rate"
                                                            style="background-image: url('{{ asset($rateStar) }}')">
                                                            <div class="rating-icon"
                                                                style="background-image: url('{{ asset($rateStar) }}'); width: {{ $listing_content->average_rating * 20 . '%;' }}">
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="ratings-total font-sm">({{ number_format($listing_content->average_rating, 2) }})</span>
                                                        <span
                                                            class="ratings-total color-medium ms-1 font-sm">{{ totalListingReview($listing_content->id) }}
                                                            {{ __('Reviews') }}</span>
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
                                                <span class="product-location icon-start font-sm"><i
                                                        class="fal fa-map-marker-alt"></i>{{ $location }}
                                                </span>
                                                @if ($listingbs->google_map_api_key_status == 1 && !empty($listing_content->distance))
                                                    <span class="font-sm icon-start d-block">
                                                        <i class="fas fa-map-signs"></i>
                                                        {{ number_format($listing_content->distance, 2) }}
                                                        {{ __('kilometers away') }}
                                                    </span>
                                                @endif

                                                <div
                                                    class="d-flex align-items-center justify-content-between mt-10 pt-10 border-top flex-wrap gap-2">
                                                    <div class="product-price flex-grow-1 min-w-0">
                                                        @if ($listing_content->max_price && $listing_content->min_price)
                                                            <span class="color-medium me-2">{{ __('From') }}</span>
                                                            <h6 class="price mb-0 lh-1 d-inline">
                                                                {{ symbolPrice($listing_content->min_price) }} -
                                                                {{ symbolPrice($listing_content->max_price) }}
                                                            </h6>
                                                        @else
                                                            <a href="{{ route('frontend.listing.details', ['slug' => $listing_content->slug, 'id' => $listing_content->id]) }}"
                                                                class="listing-card-home__price-request d-block w-100 text-center text-decoration-none">
                                                                <i class="fal fa-building me-1"></i>{{ __('View Business') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
                                                        @php
                                                            $claimed =
                                                                !empty($listing_content->has_pending_claim) &&
                                                                $listing_content->has_pending_claim;
                                                        @endphp

                                                        @if (is_null($listing_content->vendor_id) || $listing_content->vendor_id == 0)
                                                            <a @if (!$claimed) href="#" data-bs-toggle="modal" data-bs-target="#ClaimRequestModal" @endif
                                                                class="btn btn-primary btn-sm claim-btn {{ $claimed ? 'disabled-link' : '' }}"
                                                                data-tooltip="tooltip" data-bs-placement="right"
                                                                title="{{ $claimed ? __('Already claimed, awaiting fulfillment') : __('Claim Listing') }}"
                                                                data-listing-id="{{ $listing_content->id }}"
                                                                data-vendor-id="{{ $listing_content->vendor_id }}"
                                                                aria-disabled="{{ $claimed ? 'true' : 'false' }}">
                                                                {{ $claimed ? __('Claimed') : __('Claim') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- product-default -->
                                    </div>
                                @endforeach
                            </div>

                            @if ($listing_contents instanceof \Illuminate\Pagination\LengthAwarePaginator && $listing_contents->hasPages())
                                <div class="pagination mt-20 mb-40 justify-content-center" data-aos="fade-up">
                                    {{ $listing_contents->appends(request()->except('page'))->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Listing-map-area end -->

    <form action="{{ route('frontend.listings') }}" id="searchForm" method="GET">
        <input type="hidden" name="title" id="title" value="{{ request()->input('title') }}">
        <input type="hidden" name="location_val" id="location_val" value="{{ request()->input('location') }}">
        <input type="hidden" name="category_id" id="category_id" value="{{ request()->input('category_id') }}">
        <input type="hidden" name="max_val" id="max_val" value="{{ request()->input('max_val') }}">
        <input type="hidden" name="min_val" id="min_val" value="{{ request()->input('min_val') }}">
        <input type="hidden" name="ratings" id="ratings" value="{{ request()->input('ratings') }}">
        <input type="hidden" name="amenitie" id="amenitie" value="{{ request()->input('amenitie') }}">
        <input type="hidden" name="sort" id="sort" value="{{ request()->input('sort') }}">
        <input type="hidden" name="vendor" id="vendor" value="{{ request()->input('vendor') }}">
        <input type="hidden" name="country" id="country" value="{{ request()->input('country') }}">
        <input type="hidden" name="state" id="state" value="{{ request()->input('state') }}">
        <input type="hidden" name="city" id="city" value="{{ request()->input('city') }}">
        <input type="hidden" name="price_not_mentioned" id="price_not_mentioned_value"
            value="{{ request()->input('price_not_mentioned') }}">
        <input type="hidden" name="page" id="page" value="">
    </form>
    @if (Auth::guard('web')->check())
        @include('frontend.listing.claim-request-modal')
    @endif

    @include('frontend.partials.map-modal')
@endsection
@section('script')
    <script>
        window.countryUrl = "{{ route('frontend.get_country') }}";
        window.cityUrl = "{{ route('frontend.get_city') }}";
        window.stateUrl = "{{ route('frontend.get_state') }}";
        var countryUrl = window.countryUrl;
        var cityUrl = window.cityUrl;
        var stateUrl = window.stateUrl;
        var listing_view = "{{ $basicInfo->listing_view }}";
    </script>
    <!-- Map JS -->
    @if ($basicInfo->google_map_api_key_status == 1)
        <script src="{{ asset('assets/front/js/api-search-2.js') }}"></script>
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ $basicInfo->google_map_api_key }}&libraries=places&callback=initMap&loading=async"
            async defer></script>
    @endif
    @php
        $mapJsPath = public_path('assets/front/js/map.js');
        $mapJsVersion = is_file($mapJsPath) ? (string) filemtime($mapJsPath) : (string) time();
        $featuredForMapJson = $featured_contents instanceof \Illuminate\Support\Collection
            ? $featured_contents->values()
            : collect($featured_contents ?? [])->values();
    @endphp
    <script>
        "use strict";

        var featured_contents = {!! json_encode($featuredForMapJson) !!};
        var listing_contents = {!! json_encode(isset($map_listing_contents) ? $map_listing_contents->values() : $listing_contents) !!};
        var searchUrl = "{{ route('frontend.search_listing') }}";
        var getStateUrl = "{{ route('frontend.listings.get-state') }}";
        var getCityUrl = "{{ route('frontend.listings.get-city') }}";
        var isLoggedIn = {{ Auth::guard('web')->check() ? 'true' : 'false' }};
        var claimErrMsg = "{{ __('Please login first to claim this listing') . '.' }}";
        var currentUser = @json(Auth::guard('web')->check() ? Auth::guard('web')->user() : null);
    </script>
    @if ($basicInfo->listing_view == 0)
        <script src="{{ asset('assets/front/js/map.js') }}?v={{ $mapJsVersion }}"></script>
    @endif
    <script src="{{ asset('assets/front/js/search.js') }}"></script>
    <script src="{{ asset('assets/front/js/location-picks.js') }}"></script>
@endsection
