<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_headings', function (Blueprint $table) {
            $table->string('events_page_title')->nullable()->after('blog_page_title');
        });

        Schema::table('seos', function (Blueprint $table) {
            $table->text('meta_keyword_events')->nullable()->after('meta_description_blog');
            $table->text('meta_description_events')->nullable()->after('meta_keyword_events');
        });

        if (Schema::hasTable('page_headings')) {
            foreach (DB::table('page_headings')->get() as $row) {
                DB::table('page_headings')->where('id', $row->id)->update([
                    'events_page_title' => $row->blog_page_title ?? 'Events',
                ]);
            }
        }

        if (Schema::hasTable('seos')) {
            foreach (DB::table('seos')->get() as $row) {
                DB::table('seos')->where('id', $row->id)->update([
                    'meta_keyword_events' => $row->meta_keyword_blog ?? null,
                    'meta_description_events' => $row->meta_description_blog ?? null,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('page_headings', function (Blueprint $table) {
            $table->dropColumn('events_page_title');
        });

        Schema::table('seos', function (Blueprint $table) {
            $table->dropColumn(['meta_keyword_events', 'meta_description_events']);
        });
    }
};
