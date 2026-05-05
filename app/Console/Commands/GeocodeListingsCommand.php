<?php

namespace App\Console\Commands;

use App\Models\Listing\Listing;
use App\Support\ListingGeocoder;
use Illuminate\Console\Command;

class GeocodeListingsCommand extends Command
{
    protected $signature = 'ideah:geocode-listings
                            {--force : Update all listings, not only those missing coordinates}';

    protected $description = 'Fill latitude/longitude from Google Geocoding using each listing\'s default-language address (requires Maps API key enabled in basic settings).';

    public function handle(): int
    {
        if (!ListingGeocoder::mapsGeocodingEnabled()) {
            $this->warn('Google Maps API is disabled or the API key is empty (basic settings). Nothing to do.');

            return self::FAILURE;
        }

        $onlyMissing = !$this->option('force');
        $query = Listing::query()->orderBy('id');

        if ($onlyMissing) {
            $query->where(function ($q) {
                $q->whereNull('latitude')
                    ->orWhere('latitude', '')
                    ->orWhereNull('longitude')
                    ->orWhere('longitude', '');
            });
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info($onlyMissing
                ? 'No listings with missing coordinates.'
                : 'No listings found.');

            return self::SUCCESS;
        }

        $ok = 0;
        $fail = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($query->cursor() as $listing) {
            if (ListingGeocoder::syncFromDefaultLanguageAddress($listing)) {
                $ok++;
            } else {
                $fail++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Geocoded: {$ok} listing(s). Skipped or API miss: {$fail} listing(s).");

        return self::SUCCESS;
    }
}
