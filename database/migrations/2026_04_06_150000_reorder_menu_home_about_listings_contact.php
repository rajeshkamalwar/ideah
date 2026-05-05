<?php

use App\Models\Language;
use App\Models\MenuBuilder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Menu order: Home → About Us → Listings → Contact Us.
     */
    public function up(): void
    {
        $menusEn = json_encode([
            ['text' => 'Home', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'home'],
            ['text' => 'About Us', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'about-us'],
            ['text' => 'Listings', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'listings'],
            ['text' => 'Contact Us', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'contact'],
        ], true);

        $menusAr = json_encode([
            ['text' => 'بيت', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'home'],
            ['text' => 'معلومات عنا', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'about-us'],
            ['text' => 'القوائم', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'listings'],
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
        //
    }
};
