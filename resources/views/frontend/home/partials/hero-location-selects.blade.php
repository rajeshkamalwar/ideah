{{-- Country → state → city: horizontal row beside pin icon (hero search). --}}
<div class="hero-location-cascade min-w-0">
    <div class="hero-location-cascade__row d-flex flex-row flex-wrap flex-md-nowrap align-items-center gap-2 gap-md-1 gap-xl-2">
        <div class="hero-loc-field flex-grow-1 min-w-0">
            <label class="visually-hidden" for="hero_country">{{ __('Country') }}</label>
            <select id="hero_country" name="country" class="form-control js-hero-country-select">
                <option value=""></option>
            </select>
        </div>
        <div class="hero-loc-field flex-grow-1 min-w-0 hero-state-wrap" style="display: none">
            <label class="visually-hidden" for="hero_state">{{ __('State / region') }}</label>
            <select id="hero_state" name="state" class="form-control js-hero-state-select">
                <option value="">{{ __('State / region') }}</option>
            </select>
        </div>
        <div class="hero-loc-field flex-grow-1 min-w-0 hero-city-wrap" style="display: none">
            <label class="visually-hidden" for="hero_city">{{ __('City') }}</label>
            <select id="hero_city" name="city" class="form-control js-hero-city-select">
                <option value="">{{ __('City') }}</option>
            </select>
        </div>
    </div>
</div>
