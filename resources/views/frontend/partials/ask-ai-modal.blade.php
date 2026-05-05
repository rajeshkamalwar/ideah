{{-- Smart search: step 1 = keywords + categories, step 2 = place --}}
<div class="modal fade ask-ai-modal" id="askAiModal" tabindex="-1" aria-labelledby="askAiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content ask-ai-modal__content border-0 overflow-hidden">
            <div class="ask-ai-modal__head text-white px-4 py-4">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="min-w-0">
                        <h2 class="h4 mb-2 fw-bold text-white" id="askAiModalLabel">
                            <i class="fal fa-compass me-2 opacity-90"></i>{{ __('Ask AI') }}
                        </h2>
                        <p class="mb-0 small text-white-50" id="askAiHeadSubtitle">
                            <span id="askAiSubStep1" class="ask-ai-head-sub">{{ __('Step 1 of 2 — Keywords and category.') }}</span>
                            <span id="askAiSubStep2" class="ask-ai-head-sub d-none">{{ __('Step 2 of 2 — Country, region, and city.') }}</span>
                        </p>
                    </div>
                    <button type="button" class="btn-close btn-close-white flex-shrink-0 mt-1" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}"></button>
                </div>

                <div class="ask-ai-steps-rail d-flex align-items-start gap-0 mt-4" role="navigation"
                    aria-label="{{ __('Steps') }}">
                    <button type="button" class="ask-ai-step-pill is-active text-start border-0 bg-transparent p-0 flex-shrink-0"
                        id="askAiStepPill1" data-go-step="1">
                        <span class="ask-ai-step-num">1</span>
                        <span class="ask-ai-step-label d-block small">{{ __('What you need') }}</span>
                    </button>
                    <div class="ask-ai-step-connector flex-grow-1 align-self-center mx-2" aria-hidden="true"></div>
                    <button type="button" class="ask-ai-step-pill text-start border-0 bg-transparent p-0 flex-shrink-0"
                        id="askAiStepPill2" data-go-step="2">
                        <span class="ask-ai-step-num">2</span>
                        <span class="ask-ai-step-label d-block small">{{ __('Where') }}</span>
                    </button>
                </div>
            </div>

            <div class="modal-body px-4 pt-4 pb-4 ask-ai-modal__body">
                <div class="ask-ai-step-panel ask-ai-step-panel--active" data-ask-panel="1">
                    <h3 class="h6 fw-bold mb-1 text-dark">{{ __('What are you looking for?') }}</h3>
                    <p class="small text-muted mb-3 mb-md-4">{{ __('Add keywords and optionally pick a category.') }}</p>
                    <label class="visually-hidden" for="askAiTitle">{{ __('Keywords') }}</label>
                    <input type="text" class="form-control ask-ai-input shadow-sm mb-3" id="askAiTitle" maxlength="255"
                        placeholder="{{ __('Service, product, or business name…') }}" autocomplete="off">

                    <p class="small fw-semibold text-dark mb-2">{{ __('Category') }}</p>
                    <div class="ask-ai-category-grid d-flex flex-wrap gap-2 ask-ai-category-scroll" role="group"
                        aria-label="{{ __('Choose category') }}">
                        <button type="button"
                            class="btn ask-ai-cat-btn ask-ai-cat-btn--all rounded-pill is-active"
                            data-slug="">{{ __('All categories') }}</button>
                        @foreach ($hero_categories ?? [] as $hc)
                            <button type="button" class="btn ask-ai-cat-btn rounded-pill"
                                data-slug="{{ $hc->slug }}">{{ $hc->name }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="ask-ai-step-panel d-none" data-ask-panel="2">
                    <h3 class="h6 fw-bold mb-1 text-dark">{{ __('Where are you looking?') }}</h3>
                    <p class="small text-muted mb-3 mb-md-4">{{ __('Optional — narrow by country, region, and city.') }}</p>
                    <div class="ask-ai-location-cascade">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="ask_ai_country">{{ __('Country') }}</label>
                            <select id="ask_ai_country" name="country" class="form-control js-ask-ai-country">
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="mb-3 ask-ai-state-wrap" style="display: none">
                            <label class="form-label small fw-semibold" for="ask_ai_state">{{ __('State / region') }}</label>
                            <select id="ask_ai_state" name="state" class="form-control js-ask-ai-state-select">
                                <option value="">{{ __('State / region') }}</option>
                            </select>
                        </div>
                        <div class="mb-0 ask-ai-city-wrap" style="display: none">
                            <label class="form-label small fw-semibold" for="ask_ai_city">{{ __('City') }}</label>
                            <select id="ask_ai_city" name="city" class="form-control js-ask-ai-city-select">
                                <option value="">{{ __('City') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 pt-3 ask-ai-modal__footer-nav">
                    <button type="button" class="btn btn-outline-secondary rounded-pill d-none" id="askAiBack">
                        <i class="fal fa-arrow-left me-1"></i>{{ __('Back') }}
                    </button>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-dark rounded-pill px-4" id="askAiNext">
                            {{ __('Continue') }}<i class="fal fa-arrow-right ms-2"></i>
                        </button>
                        <button type="button" class="btn btn-ask-ai-submit rounded-pill px-4 d-none" id="askAiSearchBtn">
                            <span class="ask-ai-submit-label"><i class="fal fa-search me-2"></i>{{ __('Search') }}</span>
                            <span class="ask-ai-submit-loading d-none"><span class="spinner-border spinner-border-sm me-2"
                                    role="status"></span>{{ __('Searching…') }}</span>
                        </button>
                    </div>
                </div>

                <div class="alert alert-danger d-none mt-3 mb-0 small" id="askAiError" role="alert"></div>
                <div id="askAiResultsWrap" class="mt-4 d-none border-top pt-4">
                    <div class="ask-ai-results-intro small text-muted mb-3" id="askAiExplanation"></div>
                    <ul class="list-unstyled mb-0 ask-ai-results-list" id="askAiResultsList"></ul>
                    <div class="text-center mt-4 d-none" id="askAiViewAllWrap">
                        <a href="{{ route('frontend.listings') }}" class="btn btn-outline-primary rounded-pill"
                            id="askAiViewAll">{{ __('View all matching listings') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
