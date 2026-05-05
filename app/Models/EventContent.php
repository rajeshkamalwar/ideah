<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventContent extends Model
{
  protected $fillable = [
    'event_id',
    'language_id',
    'title',
    'slug',
    'summary',
    'content',
    'meta_keywords',
    'meta_description',
  ];

  public function event(): BelongsTo
  {
    return $this->belongsTo(Event::class, 'event_id');
  }

  public function language(): BelongsTo
  {
    return $this->belongsTo(Language::class, 'language_id');
  }
}
