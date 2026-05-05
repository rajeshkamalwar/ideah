<?php

namespace Database\Seeders;

use App\Models\HomePage\HeroSection;
use App\Models\Language;
use Illuminate\Database\Seeder;

/**
 * IDEAH hero banner copy (consultant-provided). Updates all languages with the same English HTML.
 */
class HeroBannerIdeahContentSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'Build. Scale. Expand — Without Guesswork';

        $text = <<<'HTML'
<p class="mb-15 lh-1"><span class="text-primary font-sm fw-semibold">Business Growth Ecosystem for Entrepreneurs</span></p>
<p class="mb-20">India's Premier Indo-Dutch Business Network — Helping Indian Startups &amp; Businesses Expanding from India to Europe.</p>
<p class="mb-20">High-Performance Business Network is helping entrepreneurs grow revenue, build strong connections, and expand globally.</p>
<p class="mb-20">Are you struggling with quality lead generation in India? Want to start an export business or enter the European market? IDEAH—the Indo-Dutch Entrepreneur Association Holland—gives you the network, strategy, and end-to-end support to grow locally and expand globally.</p>
<p class="mb-15">Most businesses fail due to the wrong network, poor positioning, and a lack of a scalable strategy. IDEAH provides:</p>
<ul class="mb-0 ps-3">
<li>Right people</li>
<li>Right guidance</li>
<li>Right growth systems</li>
</ul>
HTML;

        foreach (Language::query()->get() as $language) {
            HeroSection::query()->updateOrCreate(
                ['language_id' => $language->id],
                [
                    'title' => $title,
                    'text' => $text,
                ]
            );
        }
    }
}
