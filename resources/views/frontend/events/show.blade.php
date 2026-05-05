@extends('frontend.layout')

@php
  $title = strlen($content->title) > 40 ? mb_substr($content->title, 0, 40, 'UTF-8') . '...' : $content->title;
@endphp

@section('pageHeading')
  @if (!empty($title))
    {{ $title ? $title : (!empty($pageHeading) ? $pageHeading->events_page_title : __('Events')) }}
  @endif
@endsection

@section('metaKeywords')
  {{ $content->meta_keywords ?: ($seoInfo->meta_keyword_events ?? '') }}
@endsection

@section('metaDescription')
  {{ $content->meta_description ?: ($seoInfo->meta_description_events ?? '') }}
@endsection

@section('sharetitle')
  {{ $content->title }}
@endsection

@section('shareimage')
  {{ $event->image ? asset('assets/img/events/' . $event->image) : asset('assets/img/noimage.jpg') }}
@endsection

@section('content')
  @includeIf('frontend.partials.details-breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'heading' => $content->title,
      'title' => !empty($pageHeading) ? $pageHeading->events_page_title : __('Events'),
  ])

  <div class="blog-details-area pt-100 pb-60">
    <div class="container">
      <div class="row justify-content-center gx-xl-5">
        <div class="col-lg-10">
          <div class="blog-description mb-40">
            <article class="item-single">
              @if (!empty($event->image))
                <div class="image radius-md mb-30">
                  <div class="lazy-container ratio ratio-16-9">
                    <img class="lazyload" src="{{ lazyImagePlaceholder() }}"
                      data-src="{{ asset('assets/img/events/' . $event->image) }}" alt="{{ $content->title }}">
                  </div>
                </div>
              @endif
              <div class="content">
                <ul class="info-list flex-wrap">
                  <li><i class="fal fa-calendar-alt"></i>{{ $event->event_start->format('l, M d, Y H:i') }}</li>
                  @if ($event->event_end)
                    <li><i class="fal fa-calendar-check"></i>{{ $event->event_end->format('l, M d, Y H:i') }}</li>
                  @endif
                  @if (!empty($event->location))
                    <li><i class="fal fa-map-marker-alt"></i>{{ $event->location }}</li>
                  @endif
                </ul>
                <h3 class="title">{{ $content->title }}</h3>
                @if (!empty($content->summary))
                  <p class="lead">{{ $content->summary }}</p>
                @endif
                <div class="tinymce-content">
                  {!! replaceBaseUrl($content->content, 'summernote') !!}
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>

      @if ($recent->count() > 0)
        <div class="row mt-30">
          <div class="col-12">
            <h3 class="title mb-25">{{ __('More events') }}</h3>
          </div>
          @foreach ($recent as $r)
            <div class="col-md-6 col-lg-3 mb-20" data-aos="fade-up">
              <article class="card h-100">
                <div class="card-img radius-sm">
                  <a href="{{ route('frontend.events.show', ['slug' => $r->slug]) }}"
                    class="lazy-container ratio ratio-16-10">
                    <img class="lazyload" src="{{ lazyImagePlaceholder() }}"
                      data-src="{{ $r->image ? asset('assets/img/events/' . $r->image) : asset('assets/img/noimage.jpg') }}"
                      alt="{{ $r->title }}">
                  </a>
                </div>
                <div class="content p-15">
                  <p class="font-xsm color-medium mb-1">{{ $r->event_start->format('M d, Y') }}</p>
                  <h4 class="card-title font-base">
                    <a href="{{ route('frontend.events.show', ['slug' => $r->slug]) }}">{{ $r->title }}</a>
                  </h4>
                </div>
              </article>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
@endsection
