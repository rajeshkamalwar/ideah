<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkEmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'body',
        'audience',
        'total_recipients',
        'sent_count',
        'status',
        'scheduled_at',
    ];

    protected $casts = [
        'audience'     => 'array',
        'scheduled_at' => 'datetime',
    ];
}
