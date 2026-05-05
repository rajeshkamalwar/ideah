<?php

use App\Models\Language;
use App\Models\MenuBuilder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Show "Business" instead of "Listings" in the header menu (type stays listings).
     */
    public function up(): void
    {
        $menusEn = json_encode([
            ['text' => 'Home', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'home'],
            ['text' => 'About Us', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'about-us'],
            ['text' => 'Business', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'listings'],
            ['text' => 'Contact Us', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'contact'],
        ], true);

        $menusAr = json_encode([
            ['text' => 'بيت', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'home'],
            ['text' => 'معلومات عنا', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'about-us'],
            ['text' => 'الأعمال', 'href' => '', 'icon' => 'empty', 'target' => '_self', 'title' => '', 'type' => 'listings'],
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
