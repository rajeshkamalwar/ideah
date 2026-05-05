<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventContent;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DummyEventSeeder extends Seeder
{
  /**
   * Blog image filenames from the default installer dataset (public/installer/database.sql).
   * Files are copied from assets/img/blogs → assets/img/events when present.
   */
  protected function eventDefinitions(): array
  {
    $base = now()->startOfHour();

    return [
      [
        'serial_number' => 1,
        'blog_image' => '663b3fa55cb46.png',
        'event_start' => $base->copy()->addDays(7),
        'event_end' => $base->copy()->addDays(7)->addHours(3),
        'location' => 'Demo Convention Center',
        'title' => 'Community Open Day',
        'slug_prefix' => 'community-open-day',
        'summary' => 'Join us for a demo event that showcases the events listing and detail pages.',
        'content' => '<p>This is sample event content. Edit or remove this event from <strong>Admin → Event Management</strong>.</p>',
        'meta_keywords' => 'demo, events, community',
        'meta_description' => 'Sample event for testing the events section on the home page and menus.',
      ],
      [
        'serial_number' => 2,
        'blog_image' => '663b3fb1052e5.png',
        'event_start' => $base->copy()->addDays(14),
        'event_end' => $base->copy()->addDays(14)->addHours(4),
        'location' => 'Central Hall Auditorium',
        'title' => 'Tech Meetup & Networking',
        'slug_prefix' => 'tech-meetup-networking',
        'summary' => 'An evening of lightning talks, networking, and Q&A with local builders and founders.',
        'content' => '<p>Bring your ideas and meet others working on products, listings, and community projects.</p>',
        'meta_keywords' => 'tech, networking, meetup',
        'meta_description' => 'Tech meetup and networking evening with lightning talks.',
      ],
      [
        'serial_number' => 3,
        'blog_image' => '663b3fc9a3c11.png',
        'event_start' => $base->copy()->addDays(21),
        'event_end' => $base->copy()->addDays(21)->addHours(5),
        'location' => 'Riverside Workshop Studio',
        'title' => 'Weekend Workshop Series',
        'slug_prefix' => 'weekend-workshop-series',
        'summary' => 'Hands-on sessions: content, SEO basics, and getting the most from your directory profile.',
        'content' => '<p>Small groups, practical exercises, and take-home checklists. Registration suggested.</p>',
        'meta_keywords' => 'workshop, training, weekend',
        'meta_description' => 'Weekend workshop series with practical sessions for directory users.',
      ],
    ];
  }

  public function run(): void
  {
    if (! Schema::hasTable('events') || ! Schema::hasTable('event_contents')) {
      return;
    }

    $eventsDir = public_path('assets/img/events');
    if (! is_dir($eventsDir)) {
      File::makeDirectory($eventsDir, 0755, true);
    }

    foreach ($this->eventDefinitions() as $def) {
      $this->copyBlogImageToEvents($def['blog_image']);

      $event = Event::query()->updateOrCreate(
        ['serial_number' => $def['serial_number']],
        [
          'image' => $def['blog_image'],
          'event_start' => $def['event_start'],
          'event_end' => $def['event_end'],
          'location' => $def['location'],
          'status' => 1,
        ]
      );

      foreach (Language::query()->get() as $language) {
        $slug = $def['slug_prefix'] . '-' . $language->code;

        EventContent::query()->updateOrCreate(
          [
            'event_id' => $event->id,
            'language_id' => $language->id,
          ],
          [
            'title' => $def['title'],
            'slug' => $slug,
            'summary' => $def['summary'],
            'content' => $def['content'],
            'meta_keywords' => $def['meta_keywords'],
            'meta_description' => $def['meta_description'],
          ]
        );
      }
    }

    if (Schema::hasTable('sections')) {
      DB::table('sections')->update(['events_section_status' => 1]);
    }
  }

  protected function copyBlogImageToEvents(string $filename): void
  {
    $from = public_path('assets/img/blogs/' . $filename);
    $to = public_path('assets/img/events/' . $filename);

    if (is_file($from) && ! is_file($to)) {
      @copy($from, $to);
    }
  }
}
