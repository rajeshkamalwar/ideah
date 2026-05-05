<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces legacy demo brand strings with "IDEAH" in common DB text fields.
 * Safe to run multiple times.
 */
class IdeahRebrandSeeder extends Seeder
{
    private const PAIRS = [
        ['Bulistio', 'IDEAH'],
        ['bulistio', 'ideah'],
    ];

    public function run(): void
    {
        if (!Schema::hasTable('basic_settings')) {
            return;
        }

        foreach (DB::table('basic_settings')->get() as $row) {
            $updates = [];
            foreach (['website_title', 'from_name', 'email_address', 'to_mail'] as $col) {
                if (!isset($row->$col) || !is_string($row->$col)) {
                    continue;
                }
                $new = $this->replacePairs($row->$col);
                if ($new !== $row->$col) {
                    $updates[$col] = $new;
                }
            }
            if ($updates !== []) {
                DB::table('basic_settings')->where('id', $row->id)->update($updates);
            }
        }

        $this->replaceColumnWhereExists('languages', 'name');
        $this->replaceColumnWhereExists('page_headings', 'about_us_title');
        $this->replaceColumnWhereExists('faqs', 'question');
        $this->replaceColumnWhereExists('faqs', 'answer');

        if (Schema::hasTable('blog_informations')) {
            $this->replaceColumnWhereExists('blog_informations', 'title');
            $this->replaceColumnWhereExists('blog_informations', 'content');
            $this->replaceColumnWhereExists('blog_informations', 'slug');
            $this->replaceColumnWhereExists('blog_informations', 'meta_keywords');
            $this->replaceColumnWhereExists('blog_informations', 'meta_description');
        }

        if (Schema::hasTable('sections')) {
            $this->replaceColumnWhereExists('sections', 'title');
            $this->replaceColumnWhereExists('sections', 'subtitle');
        }

        if (Schema::hasTable('home_sections')) {
            $this->replaceColumnWhereExists('home_sections', 'title');
            $this->replaceColumnWhereExists('home_sections', 'subtitle');
        }

        if (Schema::hasTable('testimonial_sections')) {
            $this->replaceColumnWhereExists('testimonial_sections', 'title');
            $this->replaceColumnWhereExists('testimonial_sections', 'subtitle');
            $this->replaceColumnWhereExists('testimonial_sections', 'clients');
        }
    }

    private function replaceColumnWhereExists(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        foreach (DB::table($table)->get() as $row) {
            if (!isset($row->$column) || !is_string($row->$column)) {
                continue;
            }
            $new = $this->replacePairs($row->$column);
            if ($new === $row->$column) {
                continue;
            }
            DB::table($table)->where('id', $row->id)->update([$column => $new]);
        }
    }

    private function replacePairs(string $text): string
    {
        foreach (self::PAIRS as [$from, $to]) {
            $text = str_replace($from, $to, $text);
        }

        return $text;
    }
}
