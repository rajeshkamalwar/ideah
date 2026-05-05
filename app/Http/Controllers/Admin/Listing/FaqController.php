<?php

namespace App\Http\Controllers\Admin\Listing;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;
use App\Models\Listing\Listing;
use App\Models\Listing\ListingCategoryFaqTemplate;
use App\Models\Listing\ListingContent;
use App\Models\Listing\ListingFaq;
use App\Models\ListingCategory;
use App\Services\ListingCategoryFaqDefaultsApplier;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller

{
    public function index(Request $request, $id)
    {
        Listing::findorFail($id);
        $permissions = faqPermission($id);

        if ($permissions) {
            if (is_null($request->language)) {
                $language = Language::where('is_default', 1)->first();
            } else {
                $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
            }
            $information['language'] = $language;

            $title = ListingContent::Where('listing_id', $id)
                ->where('language_id', $language->id)
                ->first();
            $information['title'] = $title;


            $information['faqs'] = ListingFaq::Where('listing_id', $id)
                ->where('language_id', $language->id)
                ->orderByDesc('serial_number')
                ->get();
            $information['slug'] = ListingContent::where([['listing_id', $id], ['language_id', $language->id]])->pluck('slug')->first();

            $information['category_faqs'] = collect();
            $information['listing_category'] = null;
            $information['can_apply_category_defaults'] = false;

            if ($title && $title->category_id && Schema::hasTable('listing_category_faq_templates')) {
                $information['listing_category'] = ListingCategory::query()->find($title->category_id);
                $information['category_faqs'] = ListingCategoryFaqTemplate::query()
                    ->where('listing_category_id', $title->category_id)
                    ->orderBy('serial_number')
                    ->get();
                $information['can_apply_category_defaults'] = $information['category_faqs']->isEmpty();
            }

            $information['langs'] = Language::all();
            $information['listing_id'] = $id;
            return view('admin.listing.faq.index', $information);
        } else {

            Session::flash('warning', __('This Vendor FAQ Permission is not granted') . '!');
            return redirect()->route('admin.listing_management.listings');
        }
    }

    public function applyCategoryDefaults(Request $request, $id)
    {
        Listing::query()->findOrFail($id);
        $permissions = faqPermission($id);

        if (!$permissions) {
            Session::flash('warning', __('This Vendor FAQ Permission is not granted') . '!');

            return redirect()->route('admin.listing_management.listings');
        }

        if (is_null($request->language)) {
            $language = Language::query()->where('is_default', 1)->first();
        } else {
            $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        }

        $content = ListingContent::query()
            ->where('listing_id', $id)
            ->where('language_id', $language->id)
            ->first();

        if (!$content || !$content->category_id) {
            Session::flash('warning', __('No category is set for this listing in this language. Edit the listing and choose a category first.'));

            return redirect()->back();
        }

        $added = ListingCategoryFaqDefaultsApplier::applyForListingCategoryId((int) $content->category_id);

        if ($added === 0) {
            Session::flash('info', __('This category already has FAQ templates, or the database table is missing. Run: php artisan migrate'));
        } else {
            Session::flash('success', __('Default business-type FAQs were added for this category.') . ' (' . $added . ' ' . __('entries') . ')');
        }

        return redirect()->back();
    }

    public function store(Request $request)
    {
        $totalFaq = ListingFaq::where('listing_id', $request->listing_id)
            ->where('language_id', $request->language_id)
            ->count();

        if ($totalFaq < packageTotalFaqs($request->listing_id)) {
            $rules = [
                'language_id' => 'required',
                'question' => 'required',
                'answer' => 'required',
                'serial_number' => 'required'
            ];

            $message = [
                'language_id.required' => __('The language field is required.')
            ];

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json([
                    'errors' => $validator->getMessageBag()->toArray()
                ], 400);
            }

            ListingFaq::query()->create($request->all());

            Session::flash('success', __('New faq added successfully') . '!');

            return Response::json(['status' => 'success'], 200);
        } else {
            Session::flash('warning', __('FAQ limit reached or exceeded') . '!');
            return Response::json(['status' => 'success'], 200);
        }
    }

    public function update(Request $request)
    {
        $rules = [
            'question' => 'required',
            'answer' => 'required',
            'serial_number' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $faq = ListingFaq::query()->find($request->id);

        $faq->update($request->all());

        Session::flash('success', __('FAQ updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $faq = ListingFaq::query()->find($id);

        $faq->delete();

        return redirect()->back()->with('success', __('FAQ deleted successfully') . '!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $faq = ListingFaq::query()->find($id);
            $faq->delete();
        }
        Session::flash('success', __('FAQs deleted successfully') . '!');
        return Response::json(['status' => 'success'], 200);
    }
}
