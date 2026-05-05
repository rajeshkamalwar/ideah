<?php

namespace App\Http\Controllers\Admin\Event;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UploadFile;
use App\Models\Event;
use App\Models\EventContent;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Mews\Purifier\Facades\Purifier;

class EventController extends Controller
{
  public function index(Request $request)
  {
    $langCode = $request->get('language', Language::query()->where('is_default', 1)->value('code'));
    $language = Language::where('code', $langCode)->firstOrFail();

    $information['events'] = Event::query()
      ->join('event_contents', 'events.id', '=', 'event_contents.event_id')
      ->where('event_contents.language_id', $language->id)
      ->select('events.*', 'event_contents.title')
      ->orderByDesc('events.event_start')
      ->get();

    $information['langs'] = Language::all();

    return view('admin.events.index', $information);
  }

  public function create()
  {
    $information['languages'] = Language::all();

    return view('admin.events.create', $information);
  }

  public function store(Request $request)
  {
    $rules = [
      'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
      'event_start' => 'required|date',
      'event_end' => 'nullable|date|after_or_equal:event_start',
      'location' => 'nullable|string|max:500',
      'status' => 'required|in:0,1',
      'serial_number' => 'required|integer|min:0',
    ];

    foreach (Language::all() as $lang) {
      $rules[$lang->code . '_title'] = 'required|string|max:255';
      $rules[$lang->code . '_summary'] = 'nullable|string|max:500';
      $rules[$lang->code . '_content'] = 'nullable|string';
      $rules[$lang->code . '_meta_keywords'] = 'nullable|string';
      $rules[$lang->code . '_meta_description'] = 'nullable|string';
    }

    $request->validate($rules);

    $imgName = UploadFile::store(public_path('assets/img/events/'), $request->file('image'));

    $event = Event::create([
      'image' => $imgName,
      'event_start' => $request->event_start,
      'event_end' => $request->event_end,
      'location' => $request->location,
      'status' => (int) $request->status,
      'serial_number' => (int) $request->serial_number,
    ]);

    foreach (Language::all() as $language) {
      $title = $request->input($language->code . '_title');
      $slug = $this->uniqueSlugForLanguage(createSlug($title), $language->id);

      EventContent::create([
        'event_id' => $event->id,
        'language_id' => $language->id,
        'title' => $title,
        'slug' => $slug,
        'summary' => $request->input($language->code . '_summary'),
        'content' => Purifier::clean($request->input($language->code . '_content', ''), 'youtube'),
        'meta_keywords' => $request->input($language->code . '_meta_keywords'),
        'meta_description' => $request->input($language->code . '_meta_description'),
      ]);
    }

    Session::flash('success', __('Event added successfully') . '!');

    $langCode = $request->input('language', Language::query()->where('is_default', 1)->value('code'));

    return redirect()->route('admin.event_management.events', ['language' => $langCode]);
  }

  public function edit($id)
  {
    $event = Event::findOrFail($id);
    $information['event'] = $event;

    $languages = Language::all();
    foreach ($languages as $language) {
      $language->eventData = EventContent::where('event_id', $event->id)
        ->where('language_id', $language->id)
        ->first();
    }

    $information['languages'] = $languages;

    return view('admin.events.edit', $information);
  }

  public function update(Request $request, $id)
  {
    $event = Event::findOrFail($id);

    $rules = [
      'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
      'event_start' => 'required|date',
      'event_end' => 'nullable|date|after_or_equal:event_start',
      'location' => 'nullable|string|max:500',
      'status' => 'required|in:0,1',
      'serial_number' => 'required|integer|min:0',
    ];

    foreach (Language::all() as $lang) {
      $rules[$lang->code . '_title'] = 'required|string|max:255';
      $rules[$lang->code . '_summary'] = 'nullable|string|max:500';
      $rules[$lang->code . '_content'] = 'nullable|string';
      $rules[$lang->code . '_meta_keywords'] = 'nullable|string';
      $rules[$lang->code . '_meta_description'] = 'nullable|string';
    }

    $request->validate($rules);

    if ($request->hasFile('image')) {
      $imgName = UploadFile::update(public_path('assets/img/events/'), $request->file('image'), $event->image);
    } else {
      $imgName = $event->image;
    }

    $event->update([
      'image' => $imgName,
      'event_start' => $request->event_start,
      'event_end' => $request->event_end,
      'location' => $request->location,
      'status' => (int) $request->status,
      'serial_number' => (int) $request->serial_number,
    ]);

    foreach (Language::all() as $language) {
      $content = EventContent::firstOrNew([
        'event_id' => $event->id,
        'language_id' => $language->id,
      ]);

      $title = $request->input($language->code . '_title');
      $slug = $this->uniqueSlugForLanguage(createSlug($title), $language->id, $content->id);

      $content->fill([
        'title' => $title,
        'slug' => $slug,
        'summary' => $request->input($language->code . '_summary'),
        'content' => Purifier::clean($request->input($language->code . '_content', ''), 'youtube'),
        'meta_keywords' => $request->input($language->code . '_meta_keywords'),
        'meta_description' => $request->input($language->code . '_meta_description'),
      ]);
      $content->save();
    }

    Session::flash('success', __('Event updated successfully') . '!');

    $langCode = $request->input('language', Language::query()->where('is_default', 1)->value('code'));

    return redirect()->route('admin.event_management.events', ['language' => $langCode]);
  }

  public function destroy($id)
  {
    $event = Event::findOrFail($id);
    @unlink(public_path('assets/img/events/') . $event->image);
    EventContent::where('event_id', $event->id)->delete();
    $event->delete();

    return redirect()->back()->with('success', __('Event deleted successfully') . '!');
  }

  protected function uniqueSlugForLanguage(string $baseSlug, int $languageId, ?int $excludeContentId = null): string
  {
    $slug = $baseSlug ?: 'event';
    $candidate = $slug;
    $n = 0;

    while ($this->slugExists($candidate, $languageId, $excludeContentId)) {
      $n++;
      $candidate = $slug . '-' . $n;
    }

    return $candidate;
  }

  protected function slugExists(string $slug, int $languageId, ?int $excludeContentId): bool
  {
    $q = EventContent::where('language_id', $languageId)->where('slug', $slug);
    if ($excludeContentId) {
      $q->where('id', '!=', $excludeContentId);
    }

    return $q->exists();
  }
}
