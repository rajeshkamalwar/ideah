<?php

use App\Models\Language;
use App\Models\MenuBuilder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Insert Products (type: shop → route shop.products) after Listings when missing.
     */
    public function up(): void
    {
        $shopItemEn = [
            'text' => 'Products',
            'href' => '',
            'icon' => 'empty',
            'target' => '_self',
            'title' => '',
            'type' => 'shop',
        ];
        $shopItemAr = [
            'text' => 'المنتجات',
            'href' => '',
            'icon' => 'empty',
            'target' => '_self',
            'title' => '',
            'type' => 'shop',
        ];

        foreach (MenuBuilder::query()->cursor() as $row) {
            $lang = Language::query()->find($row->language_id);
            $code = strtolower((string) ($lang->code ?? ''));
            $shopItem = ($code === 'ar') ? $shopItemAr : $shopItemEn;

            $menus = json_decode($row->menus, true);
            if (!is_array($menus)) {
                continue;
            }

            $hasShop = false;
            foreach ($menus as $item) {
                if (is_array($item) && (($item['type'] ?? '') === 'shop')) {
                    $hasShop = true;
                    break;
                }
            }
            if ($hasShop) {
                continue;
            }

            $insertAt = null;
            foreach ($menus as $i => $item) {
                if (is_array($item) && (($item['type'] ?? '') === 'listings')) {
                    $insertAt = $i + 1;
                    break;
                }
            }
            if ($insertAt === null) {
                foreach ($menus as $i => $item) {
                    if (is_array($item) && (($item['type'] ?? '') === 'home')) {
                        $insertAt = $i + 1;
                        break;
                    }
                }
            }
            if ($insertAt === null) {
                $insertAt = 0;
            }

            array_splice($menus, $insertAt, 0, [$shopItem]);
            $row->menus = json_encode($menus, true);
            $row->save();
        }
    }

    public function down(): void
    {
        foreach (MenuBuilder::query()->cursor() as $row) {
            $menus = json_decode($row->menus, true);
            if (!is_array($menus)) {
                continue;
            }
            $filtered = array_values(array_filter($menus, function ($item) {
                return !is_array($item) || (($item['type'] ?? '') !== 'shop');
            }));
            if (count($filtered) !== count($menus)) {
                $row->menus = json_encode($filtered, true);
                $row->save();
            }
        }
    }
};
