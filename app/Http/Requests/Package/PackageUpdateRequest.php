<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class PackageUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(Request $request): array
    {
        $permissions = $request->features;

        if (is_array($permissions) && in_array('Amenities', $permissions)) {
            $Amenities = true;
        } else {
            $Amenities = false;
        }

        if (is_array($permissions) && in_array('Feature', $permissions)) {
            $additionalSpecification = true;
        } else {
            $additionalSpecification = false;
        }
        if (is_array($permissions) && in_array('Social Links', $permissions)) {
            $socialLinks = true;
        } else {
            $socialLinks = false;
        }
        if (is_array($permissions) && in_array('FAQ', $permissions)) {
            $faq = true;
        } else {
            $faq = false;
        }
        if (is_array($permissions) && in_array('Products', $permissions)) {
            $product = true;
        } else {
            $product = false;
        }
        return [
            'title' => 'required|max:255',
            'price' => 'required',
            'number_of_images_per_listing' => 'required',
            'number_of_listing' => 'required',
            'term' => 'required',
            'icon' => 'required',
            'status' => 'required',
            'number_of_amenities_per_listing' => $Amenities ? 'required' : '',
            'number_of_additional_specification' => $additionalSpecification ? 'required' : '',
            'number_of_social_links' => $socialLinks ? 'required' : '',
            'number_of_faq' => $faq ? 'required' : '',
            'number_of_products' => $product ? 'required' : '',
            'number_of_images_per_products' => $product ? 'required' : '',
        ];
    }
}
