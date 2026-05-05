@extends('frontend.layout')

@section('pageHeading')
  @if (!empty($pageHeading))
    {{ $pageHeading->events_page_title }}
  @else
    {{ __('Events') }}
  @endif
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_events }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_events }}
  @endif
@endsection

@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->events_page_title : __('Events'),
  ])

  <section class="blog-area ptb-100">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="row justify-content-center">
            @if ($events->count() === 0)
              <h3 class="text-center">{{ __('NO EVENT FOUND') . '!' }}</h3>
            @else
              @foreach ($events as $item)
                <div class="col-md-6 col-lg-4" data-aos="fade-up">
                  <article class="card mb-25 h-100">
                    <div class="card-img radius-md">
                      <a href="{{ route('frontend.events.show', ['slug' => $item->slug]) }}"
                        class="lazy-container ratio ratio-16-10">
                        <img class="lazyload" src="{{ lazyImagePlaceholder() }}"
                          data-src="{{ $item->image ? asset('assets/img/events/' . $item->image) : asset('assets/img/noimage.jpg') }}"
                          alt="{{ $item->title }}">
                      </a>
                    </div>
                    <div class="content">
                      <p class="font-sm color-medium mb-2">
                        <i class="fal fa-calendar-alt me-1"></i>{{ $item->event_start->format('M d, Y H:i') }}
                        @if (!empty($item->location))
                          <span class="ms-2"><i class="fal fa-map-marker-alt me-1"></i>{{ $item->location }}</span>
                        @endif
                      </p>
                      <h3 class="card-title">
                        <a href="{{ route('frontend.events.show', ['slug' => $item->slug]) }}">{{ $item->title }}</a>
                      </h3>
                      @if (!empty($item->summary))
                        <p class="card-text">
                          {{ \Illuminate\Support\Str::limit(strip_tags(convertUtf8($item->summary)), 120) }}</p>
                      @elseif (!empty($item->content))
                        <p class="card-text">
                          {{ \Illuminate\Support\Str::limit(strip_tags(convertUtf8($item->content)), 120) }}</p>
                      @endif
                      <a href="{{ route('frontend.events.show', ['slug' => $item->slug]) }}"
                        class="card-btn">{{ __('Read More') }}</a>
                    </div>
                  </article>
                </div>
              @endforeach
            @endif
          </div>
        </div>
      </div>
      <div class="pagination mt-20 justify-content-center" data-aos="fade-up">
        {{ $events->links() }}
      </div>
    </div>
    @if (!empty(showAd(3)))
      <div class="text-center mt-40">
        {!! showAd(3) !!}
      </div>
    @endif
  </section>
@endsection
