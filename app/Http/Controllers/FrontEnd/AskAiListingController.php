<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ListingVisibility;
use App\Models\Language;
use App\Models\Listing\ListingContent;
use App\Models\ListingCategory;
use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Guided "Ask AI" smart search: keyword + category + location — no external AI APIs.
 */
class AskAiListingController extends Controller
{
  public function search(Request $request): JsonResponse
  {
    $request->validate([
      'title' => ['nullable', 'string', 'max:255'],
      'category_id' => ['nullable', 'string', 'max:190'],
      'country' => ['nullable', 'string', 'max:32'],
      'state' => ['nullable', 'string', 'max:32'],
      'city' => ['nullable', 'string', 'max:32'],
    ]);

    $title = trim((string) $request->input('title', ''));
    $hasTitle = $title !== '';
    $hasCat = $request->filled('category_id') && trim((string) $request->category_id) !== '';
    $hasCountry = $request->filled('country');
    $hasState = $request->filled('state');
    $hasCity = $request->filled('city');

    if (! $hasTitle && ! $hasCat && ! $hasCountry && ! $hasState && ! $hasCity) {
      return response()->json([
        'ok' => false,
        'message' => __('Please answer at least one question, or pick a location.'),
      ], 422);
    }

    $misc = new MiscellaneousController();
    $language = $misc->getLanguage();
    if (! $language) {
      $language = Language::query()->orderByDesc('is_default')->orderBy('id')->first();
    }
    if (! $language) {
      return response()->json([
        'ok' => false,
        'message' => __('Application language is not configured.'),
      ], 503);
    }

    $categoryModel = null;
    if ($hasCat) {
      $categoryModel = ListingCategory::where('language_id', $language->id)
        ->where('slug', $request->category_id)
        ->first();
    }

    $query = ListingContent::query()
      ->join('listings', 'listings.id', '=', 'listing_contents.listing_id')
      ->join('listing_categories', 'listing_categories.id', '=', 'listing_contents.category_id')
      ->where('listing_contents.language_id', $language->id)
      ->where('listings.status', '1')
      ->where('listings.visibility', '1')
      ->tap(function ($q) {
        ListingVisibility::applyListingPublicFilters($q);
      });

    if ($hasTitle) {
      $query->where(function ($q) use ($title) {
        $q->where('listing_contents.title', 'like', '%' . $title . '%')
          ->orWhere('listing_contents.summary', 'like', '%' . $title . '%');
      });
    }

    if ($hasCat) {
      if ($categoryModel) {
        $query->where('listing_contents.category_id', $categoryModel->id);
      } else {
        $query->whereRaw('1 = 0');
      }
    }

    if ($hasCountry) {
      $query->where('listing_contents.country_id', $request->country);
    }

    if ($hasState) {
      $query->where('listing_contents.state_id', $request->state);
    }

    if ($hasCity) {
      $query->where('listing_contents.city_id', $request->city);
    }

    $rows = $query
      ->select(
        'listings.id',
        'listing_contents.title',
        'listing_contents.slug',
        'listing_categories.name as category_name',
      )
      ->distinct(['listings.id'])
      ->orderByDesc('listings.id')
      ->limit(15)
      ->get();

    $listings = $rows->map(function ($row) {
      return [
        'id' => (int) $row->id,
        'title' => $row->title,
        'slug' => $row->slug,
        'category_name' => $row->category_name,
        'url' => route('frontend.listing.details', ['slug' => $row->slug, 'id' => $row->id]),
      ];
    });

    $viewAllParams = [];
    if ($hasTitle) {
      $viewAllParams['title'] = $title;
    }
    if ($hasCat && $categoryModel) {
      $viewAllParams['category_id'] = $categoryModel->slug;
    }
    if ($hasCountry) {
      $viewAllParams['country'] = $request->country;
    }
    if ($hasState) {
      $viewAllParams['state'] = $request->state;
    }
    if ($hasCity) {
      $viewAllParams['city'] = $request->city;
    }

    $explanation = $this->buildSummaryLine(
      $language->id,
      $hasTitle ? $title : null,
      $categoryModel,
      $hasCountry ? $request->country : null,
      $hasState ? $request->state : null,
      $hasCity ? $request->city : null
    );

    return response()->json([
      'ok' => true,
      'explanation' => $explanation,
      'listings' => $listings,
      'view_all_url' => route('frontend.listings', $viewAllParams),
    ]);
  }

  private function buildSummaryLine(
    int $languageId,
    ?string $title,
    ?ListingCategory $category,
    $countryId,
    $stateId,
    $cityId
  ): string {
    $parts = [];

    if ($title !== null && $title !== '') {
      $parts[] = '“' . $title . '”';
    }

    if ($category !== null) {
      $parts[] = $category->name;
    }

    if ($cityId !== null) {
      $city = City::where('language_id', $languageId)->where('id', $cityId)->first();
      if ($city) {
        $parts[] = $city->name;
      }
    } elseif ($stateId !== null) {
      $state = State::where('language_id', $languageId)->where('id', $stateId)->first();
      if ($state) {
        $parts[] = $state->name;
      }
    } elseif ($countryId !== null) {
      $country = Country::where('language_id', $languageId)->where('id', $countryId)->first();
      if ($country) {
        $parts[] = $country->name;
      }
    }

    if ($parts === []) {
      return __('Here are listings that match your filters.');
    }

    return __('Showing matches for: :parts', ['parts' => implode(' · ', $parts)]);
  }
}
