<?php

use App\Models\MenuBuilder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('menu_builders')) {
            return;
        }

        foreach (MenuBuilder::query()->cursor() as $row) {
            $menus = json_decode($row->menus, true);
            if (! is_array($menus)) {
                continue;
            }

            $eventsItem = null;
            $menus = $this->extractAndRemoveEvents($menus, $eventsItem);

            if ($eventsItem === null) {
                $eventsItem = [
                    'text' => 'Events',
                    'href' => '',
                    'icon' => 'empty',
                    'target' => '_self',
                    'title' => '',
                    'type' => 'events',
                ];
            }

            $row->menus = json_encode($this->insertEventsBeforeContact($menus, $eventsItem));
            $row->save();
        }
    }

    public function down(): void
    {
        // Order is user-specific; no safe automatic revert.
    }

    protected function extractAndRemoveEvents(array $menus, ?array &$firstFound = null): array
    {
        $out = [];
        foreach ($menus as $m) {
            if (($m['type'] ?? null) === 'events') {
                if ($firstFound === null) {
                    $firstFound = $m;
                }

                continue;
            }
            if (! empty($m['children']) && is_array($m['children'])) {
                $m['children'] = $this->extractAndRemoveEvents($m['children'], $firstFound);
            }
            $out[] = $m;
        }

        return $out;
    }

    protected function insertEventsBeforeContact(array $menus, array $eventsItem): array
    {
        $contactIndex = null;
        foreach ($menus as $i => $m) {
            if (($m['type'] ?? null) === 'contact') {
                $contactIndex = $i;
                break;
            }
        }

        if ($contactIndex === null) {
            $menus[] = $eventsItem;

            return $menus;
        }

        array_splice($menus, $contactIndex, 0, [$eventsItem]);

        return $menus;
    }
};
