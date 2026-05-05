<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
  protected $fillable = [
    'image',
    'event_start',
    'event_end',
    'location',
    'status',
    'serial_number',
  ];

  protected $casts = [
    'event_start' => 'datetime',
    'event_end' => 'datetime',
  ];

  public function contents(): HasMany
  {
    return $this->hasMany(EventContent::class, 'event_id');
  }
}
