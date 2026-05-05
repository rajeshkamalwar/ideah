<?php

use App\Models\MenuBuilder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->unsignedTinyInteger('events_section_status')->default(0)->after('blog_section_status');
        });

        Schema::create('event_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id');
            $table->string('title')->nullable();
            $table->string('button_text')->nullable();
            $table->timestamps();

            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->unique('language_id');
        });

        foreach (DB::table('languages')->get() as $lang) {
            DB::table('event_sections')->updateOrInsert(
                ['language_id' => $lang->id],
                [
                    'title' => 'Upcoming Events',
                    'button_text' => 'View All',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->appendEventsMenuItems();
    }

    public function down(): void
    {
        Schema::dropIfExists('event_sections');

        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('events_section_status');
        });
    }

    protected function appendEventsMenuItems(): void
    {
        if (! Schema::hasTable('menu_builders')) {
            return;
        }

        $item = [
            'text' => 'Events',
            'href' => '',
            'icon' => 'empty',
            'target' => '_self',
            'title' => '',
            'type' => 'events',
        ];

        foreach (MenuBuilder::query()->cursor() as $row) {
            $menus = json_decode($row->menus, true);
            if (! is_array($menus)) {
                continue;
            }
            if ($this->menuHasEventsType($menus)) {
                continue;
            }
            $menus = $this->insertEventsInMenu($menus, $item);
            $row->menus = json_encode($menus);
            $row->save();
        }
    }

    protected function menuHasEventsType(array $menus): bool
    {
        foreach ($menus as $m) {
            if (($m['type'] ?? null) === 'events') {
                return true;
            }
            if (! empty($m['children']) && is_array($m['children'])) {
                if ($this->menuHasEventsType($m['children'])) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function insertEventsInMenu(array $menus, array $eventsItem): array
    {
        foreach ($menus as $i => $m) {
            if (($m['type'] ?? null) === 'custom' && ! empty($m['children']) && is_array($m['children'])) {
                $children = $m['children'];
                $insertAt = null;
                foreach ($children as $ci => $child) {
                    if (($child['type'] ?? null) === 'blog') {
                        $insertAt = $ci + 1;
                        break;
                    }
                }
                if ($insertAt !== null) {
                    array_splice($children, $insertAt, 0, [$eventsItem]);
                    $menus[$i]['children'] = $children;

                    return $menus;
                }
                $menus[$i]['children'][] = $eventsItem;

                return $menus;
            }
        }

        $menus[] = $eventsItem;

        return $menus;
    }
};
