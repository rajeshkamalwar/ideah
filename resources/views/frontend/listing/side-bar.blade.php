<div id="zx">
    <div class="widget-offcanvas offcanvas-xl offcanvas-start" tabindex="-1" id="widgetOffcanvas"
        aria-labelledby="widgetOffcanvas">
        <div class="offcanvas-header px-20">
            <h4 class="offcanvas-title">Filter</h4>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#widgetOffcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-3 p-xl-0" id="xx">
            <aside class="widget-area pb-10">
                <div class="widget widget-categories radius-md mb-30">
                    <h5 class="title">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#categories" aria-expanded="true" aria-controls="categories">
                            {{ __('Categories') }}
                        </button>
                    </h5>
                    <div id="categories" class="collapse show">
                        <div class="accordion-body">
                            <ul class="list-group" id="categoriesList" data-toggle-list="categoriesToggle">
                                <li class="list-item @if (request()->input('category_id') == null) open @endif">
                                    <a href="#" class="category-toggle @if (request()->input('category_id') == null)  @endif"
                                        id="">
                                        {{ __('All') }}
                                    </a>
                                </li>
                                @foreach ($categories as $categorie)
                                    <li class="list-item @if (request()->input('category_id') == $categorie->slug) open @endif">
                                        <a href="#" class="category-toggle" id="{{ $categorie->slug }}">
                                            {{ $categorie->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($hasMore)
                                <div class="load-more-btn-group">
                                    <a href="{{ route('frontend.listings.categories') }}" class="show-more font-sm">
                                        {{ __('Show More') }} +
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div id="filter-div">
                    <div class="widget radius-md mb-30 location-picks-widget">
                        <div class="accordion-body px-3 py-3">
                            @if ($countries->count() > 0)
                                <div id="location-picks" class="location-picks" data-label-all="{{ __('All') }}">
                                    <div class="location-picks__head">
                                        <div class="location-picks__head-text">
                                            <span class="location-picks__icon" aria-hidden="true"><i
                                                    class="fal fa-map-marked-alt"></i></span>
                                            <div>
                                                <h2 class="location-picks__title">{{ __('Explore by place') }}</h2>
                                                <p class="location-picks__subtitle mb-0">{{ __('Tap a country, then narrow down') }}</p>
                                            </div>
                                        </div>
                                        <button type="button" class="location-picks__clear"
                                            data-location-picks="clear" title="{{ __('Clear location') }}">
                                            <i class="fal fa-times"></i><span>{{ __('Clear') }}</span>
                                        </button>
                                    </div>
                                    <div class="location-picks__search">
                                        <label class="visually-hidden"
                                            for="location-picks-country-search">{{ __('Search countries') }}</label>
                                        <input type="search" id="location-picks-country-search"
                                            class="location-picks__search-input" autocomplete="off"
                                            data-location-picks="country-search"
                                            placeholder="{{ __('Search countries…') }}">
                                    </div>
                                    <div class="location-picks__section">
                                        <span class="location-picks__label">{{ __('Country') }}</span>
                                        <div class="location-picks__chips" data-location-picks="countries"></div>
                                        <button type="button" class="location-picks__more"
                                            data-location-picks="country-more" style="display:none">
                                            {{ __('Load more countries') }}
                                        </button>
                                    </div>
                                    <div class="location-picks__section location-picks__section--state"
                                        data-location-picks="section-state" style="display:none">
                                        <span class="location-picks__label">{{ __('State / region') }}</span>
                                        <div class="location-picks__chips" data-location-picks="states"></div>
                                    </div>
                                    <div class="location-picks__section location-picks__section--city"
                                        data-location-picks="section-city" style="display:none">
                                        <span class="location-picks__label">{{ __('City') }}</span>
                                        <div class="location-picks__chips" data-location-picks="cities"></div>
                                    </div>
                                </div>
                            @else
                                <p class="p-3 font-sm text-muted mb-0">{{ __('No countries available for this language.') }}
                                </p>
                            @endif
                            {{-- Legacy hook for scripts that expect these IDs (kept hidden) --}}
                            <select id="countryDropdown" class="d-none countryDropdown" aria-hidden="true"
                                tabindex="-1"></select>
                            <select id="stateDropdown" class="d-none stateDropdown" aria-hidden="true"
                                tabindex="-1"></select>
                            <select id="cityDropdown" class="d-none cityDropdown" aria-hidden="true"
                                tabindex="-1"></select>
                        </div>
                    </div>
                </div>
                {{-- Map / geocode scripts expect #location; no visible address field (place = country/state/city only). --}}
                <input type="hidden" id="location" value="" autocomplete="off" aria-hidden="true">

                <div id="amenities-div">
                    <div class="widget widget-amenities radius-md mb-30">
                        <h5 class="title">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#amenities" aria-expanded="true" aria-controls="amenities">
                                {{ __('Amenities') }}

                            </button>
                        </h5>
                        <div id="amenities" class="collapse show">
                            <div class="accordion-body">
                                <ul class="list-group custom-checkbox toggle-list" data-toggle-list="amenitiesToggle"
                                    data-toggle-show="5">
                                    @php
                                        $aminities = App\Models\Aminite::where('language_id', $language->id)->get();
                                        $vv = request()->input('amenitie');
                                        $hasaminitie = explode(',', $vv);
                                    @endphp

                                    @foreach ($aminities as $aminitie)
                                        @if (in_array($aminitie->id, $hasaminitie))
                                            <li>
                                                <input class="input-checkbox" type="checkbox" name="checkbox"
                                                    id="checkbox_{{ $aminitie->id }}" value="{{ $aminitie->id }}"
                                                    checked>
                                                <label class="form-check-label"
                                                    for="checkbox_{{ $aminitie->id }}"><span>{{ $aminitie->title }}</label>
                                            </li>
                                        @else
                                            <li>
                                                <input class="input-checkbox" type="checkbox" name="checkbox"
                                                    id="checkbox_{{ $aminitie->id }}" value="{{ $aminitie->id }}">
                                                <label class="form-check-label"
                                                    for="checkbox_{{ $aminitie->id }}"><span>{{ $aminitie->title }}</span></label>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                                <span class="show-more font-sm" data-toggle-btn="toggleListBtn">
                                    {{ __('Show More') }} +
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @php
                    $oMin = $o_min ?? null;
                    $oMax = $o_max ?? null;
                    $minV = $min ?? null;
                    $maxV = $max ?? null;

                    $allPriceNull = is_null($oMin) && is_null($oMax) && is_null($minV) && is_null($maxV);
                @endphp
                @if (!$allPriceNull)
                    <div class="widget widget-price radius-md mb-30">
                        <h5 class="title">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#price" aria-expanded="true" aria-controls="price">
                                {{ __('Pricing Filter') }}
                            </button>
                        </h5>
                        <div id="price" class="collapse show">
                            <div class="accordion-body">
                                <input class="form-control" type="hidden"
                                    value="{{ request()->filled('min_val') ? request()->input('min_val') : $min ?? '' }}"
                                    name="min" id="min">
                                <input class="form-control" type="hidden" value="{{ $min ?? '' }}"
                                    id="o_min">
                                <input class="form-control" type="hidden" value="{{ $max ?? '' }}"
                                    id="o_max">
                                <input class="form-control"
                                    value="{{ request()->filled('max_val') ? request()->input('max_val') : $max ?? '' }}"
                                    type="hidden" name="max" id="max">
                                <input type="hidden" id="currency_symbol"
                                    value="{{ $basicInfo->base_currency_symbol }}">
                                <div class="price-item">
                                    <div class="price-slider" data-range-slider='filterPriceSlider'></div>
                                    <div class="price-value">
                                        <span class="color-dark">{{ __('Price') }}:
                                            <span class="filter-price-range"
                                                data-range-value='filterPriceSliderValue'></span>
                                        </span>
                                    </div>
                                </div>
                                {{-- Price Not Mentioned Checkbox --}}
                                <div class="price-option  mt-20 pt-20 border-top">
                                    <div class="custom-checkbox">
                                        <input class="input-checkbox" type="checkbox" id="price_not_mentioned"
                                            value="1" {{ request('price_not_mentioned') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="price_not_mentioned">
                                            {{-- <i class="fas fa-tag me-1 text-primary"></i> --}}
                                            <span>{{ __('Price Not Mentioned') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div id="rating-div">
                    <div class="widget widget-rating radius-md mb-30">
                        <h5 class="title">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#rating" aria-expanded="true" aria-controls="rating">
                                {{ __('Rating') }}
                            </button>
                        </h5>
                        <div id="rating" class="collapse fade show">
                            <div class="accordion-body">
                                <ul class="list-group custom-radio">
                                    <li>
                                        <input class="input-radio" type="radio" name="radio" id="radio6"
                                            value="0"@if (request()->input('ratings') == '') checked @endif>
                                        <label class="form-radio-label" for="radio6">
                                            <div class="product-ratings text-xsm">
                                                <span>{{ __('All') }}</span>
                                            </div>
                                        </label>
                                    </li>
                                    <li>
                                        <input class="input-radio" type="radio" name="radio" id="radio1"
                                            value="5" @if (request()->input('ratings') == '5') checked @endif>
                                        <label class="form-radio-label" for="radio1">
                                            <div class="product-ratings text-xsm">
                                                <span>{{ __('5 stars') }}</span>
                                            </div>
                                        </label>
                                    </li>
                                    <li>
                                        <input class="input-radio" type="radio" name="radio" id="radio2"
                                            value="4"@if (request()->input('ratings') == '4') checked @endif>
                                        <label class="form-radio-label" for="radio2">
                                            <div class="product-ratings text-xsm">
                                                <span>{{ __('4 stars and above') }}</span>
                                            </div>
                                        </label>
                                    </li>
                                    <li>
                                        <input class="input-radio" type="radio" name="radio" id="radio3"
                                            value="3"@if (request()->input('ratings') == '3') checked @endif>
                                        <label class="form-radio-label" for="radio3">
                                            <div class="product-ratings text-xsm">
                                                <span>{{ __('3 stars and above') }}</span>
                                            </div>
                                        </label>
                                    </li>
                                    <li>
                                        <input class="input-radio" type="radio" name="radio" id="radio4"
                                            value="2"@if (request()->input('ratings') == '2') checked @endif>
                                        <label class="form-radio-label" for="radio4">
                                            <div class="product-ratings text-xsm">
                                                <span>{{ __('2 stars and above') }}</span>
                                            </div>
                                        </label>
                                    </li>
                                    <li>
                                        <input class="input-radio" type="radio" name="radio" id="radio5"
                                            value="1"@if (request()->input('ratings') == '1') checked @endif>
                                        <label class="form-radio-label" for="radio5">
                                            <div class="product-ratings text-xsm">
                                                <span>{{ __('1 star and above') }}</span>
                                            </div>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="cta mb-30">
                    <a href="{{ route('frontend.listings') }}" class="btn btn-lg btn-primary icon-start w-100"><i
                            class="fal fa-sync-alt"></i>{{ __('Reset All') }}</a>
                </div>
            </aside>
        </div>
    </div>
</div>
