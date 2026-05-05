<?php

namespace App\Http\Controllers\Admin\Listing;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Listing\ListingCategoryFaqTemplate;
use App\Models\ListingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CategoryFaqTemplateController extends Controller
{
    public function index(ListingCategory $category)
    {
        $language = Language::query()->findOrFail($category->language_id);
        $faqs = ListingCategoryFaqTemplate::query()
            ->where('listing_category_id', $category->id)
            ->orderByDesc('serial_number')
            ->get();

        return view('admin.listing.category.faq-templates', [
            'category' => $category,
            'language' => $language,
            'faqs' => $faqs,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'listing_category_id' => 'required|exists:listing_categories,id',
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'serial_number' => 'required|integer|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ListingCategoryFaqTemplate::query()->create($validator->validated());

        Session::flash('success', __('Category FAQ template added successfully') . '!');

        return redirect()->back();
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|exists:listing_category_faq_templates,id',
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'serial_number' => 'required|integer|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $faq = ListingCategoryFaqTemplate::query()->findOrFail($request->id);
        $faq->update($request->only(['question', 'answer', 'serial_number']));

        Session::flash('success', __('Category FAQ template updated successfully') . '!');

        return redirect()->back();
    }

    public function destroy(int $id)
    {
        $faq = ListingCategoryFaqTemplate::query()->findOrFail($id);
        $faq->delete();

        Session::flash('success', __('Category FAQ template deleted successfully') . '!');

        return redirect()->back();
    }
}
