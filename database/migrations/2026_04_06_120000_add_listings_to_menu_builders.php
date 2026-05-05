<?php

use App\Models\Language;
use App\Models\MenuBuilder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Add Listings (type: listings → route frontend.listings) after Home.
     */
    public function up(): void
    {
        $menusEn = json_encode([
            ['text' => 'Home', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'home'],
            ['text' => 'Listings', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'listings'],
            ['text' => 'About Us', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'about-us'],
            ['text' => 'Contact', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'contact'],
        ], true);

        $menusAr = json_encode([
            ['text' => 'بيت', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'home'],
            ['text' => 'القوائم', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'listings'],
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
        //
    }
};
