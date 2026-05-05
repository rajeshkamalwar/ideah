<?php

use App\Models\Footer\QuickLink;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Add Blog, Privacy Policy, and Terms & Conditions to footer quick links (per language).
     */
    public function up(): void
    {
        foreach (Language::query()->cursor() as $lang) {
            $code = strtolower((string) ($lang->code ?? ''));
            $rows = $code === 'ar'
                ? [
                    ['مدونة', '/blog', 100],
                    ['سياسة الخصوصية', '/privacy', 101],
                    ['الشروط والأحكام', '/terms-and-conditions', 102],
                  ]
                : [
                    ['Blog', '/blog', 100],
                    ['Privacy Policy', '/privacy', 101],
                    ['Terms & Conditions', '/terms-and-conditions', 102],
                  ];

            foreach ($rows as [$title, $url, $serial]) {
                QuickLink::query()->firstOrCreate(
                    [
                        'language_id' => $lang->id,
                        'url' => $url,
                    ],
                    [
                        'title' => $title,
                        'serial_number' => $serial,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        QuickLink::query()->whereIn('url', ['/blog', '/privacy', '/terms-and-conditions'])->delete();
    }
};
