<?php

namespace App\Console\Commands;

use App\Models\Listing\Listing;
use App\Support\MapListingCoordinates;
use Illuminate\Console\Command;

class BackfillListingCoordinatesCommand extends Command
{
    protected $signature = 'listings:backfill-coordinates
                            {--dry-run : Show which listings would be updated; no writes}';

    protected $description = 'Set listings.latitude / listings.longitude from city + address centroids when missing (no Google API required).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Dry run — no database writes.');
        }

        $updated = 0;
        $skippedHasCoords = 0;
        $skippedNoContent = 0;

        Listing::query()->orderBy('id')->chunkById(100, function ($listings) use ($dryRun, &$updated, &$skippedHasCoords, &$skippedNoContent) {
            foreach ($listings as $listing) {
                if (! MapListingCoordinates::shouldBackfillLatitudeLongitude($listing->latitude, $listing->longitude)) {
                    $skippedHasCoords++;

                    continue;
                }

                if ($dryRun) {
                    $approx = MapListingCoordinates::approximateCoordinatesForListing($listing);
                    if ($approx === null) {
                        $this->line("#{$listing->id} SKIP no listing_content");
                        $skippedNoContent++;

                        continue;
                    }
                    $this->line("#{$listing->id} → {$approx[0]}, {$approx[1]}");
                    $updated++;

                    continue;
                }

                if (! MapListingCoordinates::persistApproximateIfMissing($listing)) {
                    if (MapListingCoordinates::approximateCoordinatesForListing($listing) === null) {
                        $skippedNoContent++;
                    }

                    continue;
                }

                $updated++;
            }
        });

        $this->newLine();
        $this->info(($dryRun ? 'Would update' : 'Updated') . ": {$updated}. Already had coords: {$skippedHasCoords}. No content: {$skippedNoContent}.");

        return self::SUCCESS;
    }
}
