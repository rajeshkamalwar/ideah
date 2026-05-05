@extends('frontend.layout')

@section('pageHeading')
    {{ __('Categories') }}
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
    @includeIf('frontend.partials.breadcrumb', [
        'breadcrumb' => $bgImg->breadcrumb,
        'title' => __('Categories'),
    ])

    <div class="listing-categories-page pt-100 pb-75">
        <div class="container">
            <div class="row justify-content-center mb-40">
                <div class="col-lg-8 col-xl-7" data-aos="fade-up">
                    <form method="get" action="{{ route('frontend.listings.categories') }}" class="categories-filter-form">
                        <label class="visually-hidden" for="categories-filter-q">{{ __('Search categories') }}</label>
                        <div class="input-group radius-md overflow-hidden border">
                            <span class="input-group-text bg-white border-0 ps-20"><i class="fal fa-search"></i></span>
                            <input type="search" name="q" id="categories-filter-q" class="form-control border-0"
                                value="{{ $filterQuery }}" placeholder="{{ __('Filter by name…') }}" autocomplete="off">
                            <button type="submit" class="btn btn-primary px-4">{{ __('Filter') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($categories->isEmpty())
                <div class="text-center" data-aos="fade-up">
                    <p class="color-dark mb-0">{{ __('No categories match your search.') }}</p>
                    @if ($filterQuery !== '')
                        <a href="{{ route('frontend.listings.categories') }}" class="btn btn-sm btn-outline-primary mt-20">{{ __('Clear filter') }}</a>
                    @endif
                </div>
            @else
                <div class="row g-4">
                    @foreach ($categories as $category)
                        <div class="col-sm-6 col-lg-4" data-aos="fade-up">
                            <a href="{{ route('frontend.listings', ['category_id' => $category->slug]) }}"
                                class="listing-category-card d-block radius-md p-4 h-100 text-decoration-none border bg-white">
                                <div class="d-flex align-items-start gap-3">
                                    @if (!empty($category->icon))
                                        <span class="listing-category-card__icon flex-shrink-0"><i
                                                class="{{ $category->icon }}"></i></span>
                                    @endif
                                    <div class="min-w-0">
                                        <h6 class="font-md color-dark mb-1">{{ $category->name }}</h6>
                                        <p class="font-xsm text-muted mb-0">
                                            {{ $category->listed_listing_contents_count }}
                                            {{ $category->listed_listing_contents_count === 1 ? __('listing') : __('listings') }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="text-center mt-40">
                <a href="{{ route('frontend.listings') }}" class="btn btn-outline-primary"><i
                        class="fal fa-arrow-left me-2"></i>{{ __('Back to listings') }}</a>
            </div>
        </div>
    </div>
@endsection
