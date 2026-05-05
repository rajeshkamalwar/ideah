<?php

use App\Models\Language;
use App\Models\MenuBuilder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Reset header menus to Home, About Us, and Contact only (per language).
     */
    public function up(): void
    {
        $menusEn = json_encode([
            ['text' => 'Home', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'home'],
            ['text' => 'About Us', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'about-us'],
            ['text' => 'Contact', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'contact'],
        ], true);

        $menusAr = json_encode([
            ['text' => 'بيت', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'home'],
            ['text' => 'معلومات عنا', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'about-us'],
            ['text' => 'اتصال', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'contact'],
        ], true);

        foreach (MenuBuilder::query()->cursor() as $row) {
            $lang = Language::query()->find($row->language_id);
            $code = strtolower((string) ($lang->code ?? ''));
            $row->menus = ($code === 'ar') ? $menusAr : $menusEn;
            $row->save();
        }
    }

    public function down(): void
    {
        // Previous menu JSON is not stored; admins can restore from backups if needed.
    }
};
