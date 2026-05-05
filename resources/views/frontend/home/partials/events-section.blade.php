@if ($secInfo->events_section_status == 1)
  @php
    $evClass = 'blog-area pt-100 pb-75';
    if ($basicInfo->theme_version == 2) {
        $evClass = 'blog-area blog-2 pb-75';
    } elseif ($basicInfo->theme_version == 3) {
        $evClass = 'blog-area pb-75';
    } elseif ($basicInfo->theme_version == 4) {
        $evClass = 'blog-area blog-3 pt-100 pb-75';
    }
  @endphp
  <section class="{{ $evClass }}">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="section-title title-inline mb-30" data-aos="fade-up">
            <h2 class="title mb-20">
              {{ !empty($eventSecInfo?->title) ? $eventSecInfo->title : __('Upcoming Events') }}
            </h2>
            @if (count($event_count) > count($homeEvents))
              <a href="{{ route('frontend.events') }}" class="btn btn-lg btn-primary mb-20">
                {{ $eventSecInfo?->button_text ?? __('View All') }}</a>
            @endif
          </div>
        </div>
        <div class="col-12">
          <div class="row justify-content-center">
            @if ($homeEvents->count() < 1)
              <h3 class="text-center mt-2">{{ __('NO EVENT FOUND') . '!' }}</h3>
            @else
              @foreach ($homeEvents as $ev)
                <div class="col-md-6 col-lg-4" data-aos="fade-up">
                  <article class="card mb-25 h-100">
                    <div class="card-img radius-md">
                      <a href="{{ route('frontend.events.show', ['slug' => $ev->slug]) }}"
                        class="lazy-container ratio ratio-16-10">
                        <img class="lazyload" src="{{ lazyImagePlaceholder() }}"
                          data-src="{{ $ev->image ? asset('assets/img/events/' . $ev->image) : asset('assets/img/noimage.jpg') }}"
                          alt="{{ $ev->title }}">
                      </a>
                    </div>
                    <div class="content">
                      <p class="font-sm color-medium mb-2">
                        <i class="fal fa-calendar-alt me-1"></i>{{ $ev->event_start->format('M d, Y H:i') }}
                        @if (!empty($ev->location))
                          <span class="ms-2"><i class="fal fa-map-marker-alt me-1"></i>{{ $ev->location }}</span>
                        @endif
                      </p>
                      <h3 class="card-title">
                        <a href="{{ route('frontend.events.show', ['slug' => $ev->slug]) }}">{{ $ev->title }}</a>
                      </h3>
                      @if (!empty($ev->summary))
                        <p class="card-text">
                          {{ strlen(strip_tags(convertUtf8($ev->summary))) > 100 ? substr(strip_tags(convertUtf8($ev->summary)), 0, 100) . '...' : strip_tags(convertUtf8($ev->summary)) }}
                        </p>
                      @elseif (!empty($ev->content))
                        <p class="card-text">
                          {{ strlen(strip_tags(convertUtf8($ev->content))) > 100 ? substr(strip_tags(convertUtf8($ev->content)), 0, 100) . '...' : strip_tags(convertUtf8($ev->content)) }}
                        </p>
                      @endif
                      <a href="{{ route('frontend.events.show', ['slug' => $ev->slug]) }}"
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
