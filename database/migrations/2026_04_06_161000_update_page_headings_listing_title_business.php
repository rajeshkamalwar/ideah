<?php

use App\Models\Language;
use App\Models\BasicSettings\PageHeading;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Match listing directory page titles to "Business" branding.
     */
    public function up(): void
    {
        foreach (PageHeading::query()->cursor() as $row) {
            $lang = Language::query()->find($row->language_id);
            $code = strtolower((string) ($lang->code ?? ''));
            $row->listing_page_title = ($code === 'ar') ? 'الأعمال' : 'Business';
            $row->save();
        }
    }

    public function down(): void
    {
        //
    }
};
