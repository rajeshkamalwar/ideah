<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Models\Event;
use App\Models\EventContent;
use Illuminate\Http\Request;

class EventController extends Controller
{
  public function index(Request $request)
  {
    $misc = new MiscellaneousController();
    $language = $misc->getLanguage();

    $information['seoInfo'] = $language->seoInfo()->select('meta_keyword_events', 'meta_description_events')->first();
    $information['pageHeading'] = $misc->getPageHeading($language);
    $information['bgImg'] = $misc->getBreadcrumb();

    $information['events'] = Event::query()
      ->join('event_contents', 'events.id', '=', 'event_contents.event_id')
      ->where('event_contents.language_id', $language->id)
      ->where('events.status', 1)
      ->select(
        'events.id',
        'events.image',
        'events.event_start',
        'events.event_end',
        'events.location',
        'event_contents.title',
        'event_contents.slug',
        'event_contents.summary',
        'event_contents.content'
      )
      ->orderByDesc('events.event_start')
      ->paginate(9);

    return view('frontend.events.index', $information);
  }

  public function show($slug)
  {
    $misc = new MiscellaneousController();
    $language = $misc->getLanguage();

    $content = EventContent::where('language_id', $language->id)
      ->where('slug', $slug)
      ->firstOrFail();

    $event = Event::where('id', $content->event_id)->where('status', 1)->firstOrFail();

    $information['event'] = $event;
    $information['content'] = $content;
    $information['seoInfo'] = $language->seoInfo()->select('meta_keyword_events', 'meta_description_events')->first();
    $information['pageHeading'] = $misc->getPageHeading($language);
    $information['bgImg'] = $misc->getBreadcrumb();

    $information['recent'] = Event::query()
      ->join('event_contents', 'events.id', '=', 'event_contents.event_id')
      ->where('event_contents.language_id', $language->id)
      ->where('events.status', 1)
      ->where('events.id', '!=', $event->id)
      ->select('events.image', 'events.event_start', 'event_contents.title', 'event_contents.slug')
      ->orderByDesc('events.event_start')
      ->limit(4)
      ->get();

    return view('frontend.events.show', $information);
  }
}
