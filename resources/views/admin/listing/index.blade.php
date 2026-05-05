@extends('admin.layout')

{{-- this style will be applied when the direction of language is right-to-left --}}
@includeIf('admin.partials.rtl_style')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Listings') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="{{ route('admin.listing_management.listings') }}">{{ __('Listings Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Listings') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-2">
              <div class="card-title d-inline-block">{{ __('Listings') }}</div>
            </div>

            <div class="col-lg-8">
              <form action="{{ route('admin.listing_management.listings') }}" method="get" id="listingSearchForm">
                <div class="row">
                  <div class="col-sm-6 col-xl-2 mb-1 mb-xl-0">
                    <select name="vendor_id" id="" class="select2"
                      onchange="document.getElementById('listingSearchForm').submit()">
                      <option value="" selected disabled>{{ __('Vendor') }}</option>
                      <option value="All" {{ request()->input('vendor_id') == 'All' ? 'selected' : '' }}>
                        {{ __('All') }}</option>
                      @php
                        $vendorInfo = App\Models\Admin::first();
                      @endphp
                      <option value="admin" @selected(request()->input('vendor_id') == 'admin')>{{ $vendorInfo->username }}
                        ({{ __('admin') }})</option>
                      @foreach ($vendors as $vendor)
                        <option @selected($vendor->id == request()->input('vendor_id')) value="{{ $vendor->id }}">
                          {{ $vendor->username }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-sm-6 col-xl-2 mb-1 mb-xl-0">
                    <select name="category" id="" class="select2"
                      onchange="document.getElementById('listingSearchForm').submit()">
                      <option value="" selected disabled>{{ __('Category') }}</option>
                      <option value="All" {{ request()->input('category') == 'All' ? 'selected' : '' }}>
                        {{ __('All') }}</option>
                      @foreach ($categories as $category)
                        <option @selected($category->slug == request()->input('category')) value="{{ $category->slug }}">
                          {{ $category->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-sm-6 col-xl-2 mb-1 mb-xl-0">
                    <select name="status" id="" class="select2"
                      onchange="document.getElementById('listingSearchForm').submit()">
                      <option value="" selected disabled>{{ __('Status') }}</option>
                      <option value="All" {{ request()->input('status') == 'All' ? 'selected' : '' }}>
                        {{ __('All') }}</option>
                      <option value="approved" {{ request()->input('status') == 'approved' ? 'selected' : '' }}>
                        {{ __('Approved') }}
                      </option>
                      <option value="pending" {{ request()->input('status') == 'pending' ? 'selected' : '' }}>
                        {{ __('Pending') }}
                      </option>
                      <option value="rejected" {{ request()->input('status') == 'rejected' ? 'selected' : '' }}>
                        {{ __('Rejected') }}
                      </option>
                    </select>
                  </div>
                  <div class="col-sm-6 col-xl-2 mb-1 mb-xl-0">
                    <input type="text" name="title" value="{{ request()->input('title') }}" class="form-control"
                      placeholder="{{ __('Title') }}">
                  </div>
                  <div class="col-sm-6 col-xl-2 mb-1 mb-xl-0">
                    <select name="featured" id="" class="select2"
                      onchange="document.getElementById('listingSearchForm').submit()">
                      <option value="" selected disabled>{{ __('Featured') }}</option>
                      <option value="All" {{ request()->input('featured') == 'All' ? 'selected' : '' }}>
                        {{ __('All') }}</option>
                      <option value="active" {{ request()->input('featured') == 'active' ? 'selected' : '' }}>
                        {{ __('Active') }}
                      </option>
                      <option value="pending" {{ request()->input('featured') == 'pending' ? 'selected' : '' }}>
                        {{ __('Pending') }}
                      </option>
                      <option value="rejected" {{ request()->input('featured') == 'rejected' ? 'selected' : '' }}>
                        {{ __('Rejected') }}
                      </option>
                    </select>
                  </div>
                </div>
              </form>
            </div>

            <div class="col-lg-2 mt-2 mt-lg-0">
              <div class="text-right">
                <form action="{{ route('admin.listing_management.approve_all_listings') }}" method="post" class="d-inline"
                  onsubmit="return confirm('{{ __('Approve and show ALL listings? This cannot be undone from here.') }}');">
                  @csrf
                  <button type="submit" class="btn btn-success btn-sm mr-2" title="{{ __('Set every listing to Approved and Visible') }}">
                    <i class="fas fa-check-double"></i> {{ __('Approve all') }}
                  </button>
                </form>
                <a href="{{ route('admin.listing_management.select_vendor') }}" class="btn btn-primary btn-sm"><i
                    class="fas fa-plus"></i> {{ __('Add Listing') }}</a>
                <button class="btn btn-danger btn-sm ml-2 d-none bulk-delete"
                  data-href="{{ route('admin.listing_management.bulk_delete.listing') }}"><i
                    class="flaticon-interface-5"></i>
                  {{ __('Delete') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($listings) == 0)
                <h3 class="text-center">{{ __('NO LISTING FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Featured Image') }}</th>
                        <th scope="col">{{ __('Title') }}</th>
                        <th scope="col">{{ __('Vendor') }}</th>
                        <th scope="col">{{ __('Listing package') }}</th>
                        @if (count($charges) > 0)
                          <th scope="col">{{ __('Featured Status') }}</th>
                        @endif
                        <th scope="col">{{ __('Category') }}</th>
                        <th scope="col">{{ __('Approve Status') }}</th>
                        <th scope="col">{{ __('Hide/Show') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($listings as $listing)
                        @php
                          $listing_content = $listing->listing_content->first();
                          if (is_null($listing_content)) {
                              $listing_content = App\Models\Listing\ListingContent::where('listing_id', $listing->id)
                                  ->where('language_id', $language->id)
                                  ->first();
                          }
                        @endphp
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $listing->id }}">
                          </td>
                          <td>
                            @if (!empty($listing_content))
                              <a href="{{ route('frontend.listing.details', ['slug' => $listing_content->slug, 'id' => $listing->id]) }}"
                                target="_blank">
                                <div class="max-dimensions">
                                  <img
                                    src="{{ $listing->feature_image ? asset('assets/img/listing/' . $listing->feature_image) : asset('assets/admin/img/noimage.jpg') }}"
                                    alt="..." class="uploaded-img">
                                </div>
                              </a>
                            @else
                              <div class="max-dimensions">
                                <img
                                  src="{{ $listing->feature_image ? asset('assets/img/listing/' . $listing->feature_image) : asset('assets/admin/img/noimage.jpg') }}"
                                  alt="..." class="uploaded-img">
                              </div>
                            @endif
                          </td>
                          <td class="title">
                            @if (!empty($listing_content))
                              <a href="{{ route('frontend.listing.details', ['slug' => $listing_content->slug, 'id' => $listing->id]) }}"
                                target="_blank">
                                {{ strlen(@$listing_content->title) > 50 ? mb_substr(@$listing_content->title, 0, 50, 'utf-8') . '...' : @$listing_content->title }}
                              </a>
                            @else
                              --
                            @endif
                          </td>
                          <td>
                            @php
                              $adminVendorLabel = optional(App\Models\Admin::first())->username ?? __('Admin');
                            @endphp
                            <form action="{{ route('admin.listing_management.update_listing_vendor') }}" method="post"
                              class="m-0">
                              @csrf
                              <input type="hidden" name="listingId" value="{{ $listing->id }}">
                              <select name="vendor_id" class="form-control form-control-sm"
                                style="max-width: 12rem; min-width: 8rem;" onchange="this.form.submit()"
                                title="{{ __('Change listing owner') }}">
                                <option value="0" @selected((int) $listing->vendor_id === 0)>
                                  {{ $adminVendorLabel }} ({{ __('admin') }})
                                </option>
                                @foreach ($vendors as $vendor)
                                  <option value="{{ $vendor->id }}"
                                    @selected((int) $listing->vendor_id === (int) $vendor->id)>{{ $vendor->username }}</option>
                                @endforeach
                              </select>
                            </form>
                            @if ($listing->vendor_id != 0 && $listing->vendor)
                              <a class="small d-block mt-1"
                                href="{{ route('admin.vendor_management.vendor_details', ['id' => $listing->vendor->id, 'language' => $defaultLang->code]) }}">{{ __('Vendor profile') }}</a>
                            @endif
                          </td>
                          <td>
                            @php
                              $packageBtnLabel = $listing->package
                                  ? (mb_strlen($listing->package->title) > 20
                                      ? mb_substr($listing->package->title, 0, 20, 'UTF-8') . '…'
                                      : $listing->package->title)
                                  : __('Default');
                            @endphp
                            <form id="packageForm{{ $listing->id }}" class="d-inline-block m-0"
                              action="{{ route('admin.listing_management.update_listing_package') }}" method="post">
                              @csrf
                              <input type="hidden" name="listingId" value="{{ $listing->id }}">
                              <input type="hidden" name="package_id" id="packageIdInput{{ $listing->id }}"
                                value="{{ $listing->package_id ?? '' }}">
                              <div class="dropdown">
                                <button
                                  class="btn btn-sm dropdown-toggle {{ $listing->package_id ? 'btn-info text-white' : 'btn-secondary' }}"
                                  type="button" id="packageMenuBtn{{ $listing->id }}" data-toggle="dropdown"
                                  aria-haspopup="true" aria-expanded="false"
                                  title="{{ __('Default uses vendor membership; admin listings use full access when empty.') }}"
                                  style="max-width: 12rem;">
                                  <i class="fas fa-cube mr-1" aria-hidden="true"></i><span
                                    class="d-inline-block text-truncate align-bottom"
                                    style="max-width: 9rem; vertical-align: middle;">{{ $packageBtnLabel }}</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow py-1"
                                  aria-labelledby="packageMenuBtn{{ $listing->id }}" style="min-width: 13rem;">
                                  <h6 class="dropdown-header px-3 py-2 mb-0">{{ __('Listing package') }}</h6>
                                  <button type="button"
                                    class="dropdown-item d-flex justify-content-between align-items-center px-3 py-2 {{ !$listing->package_id ? 'active' : '' }}"
                                    onclick="document.getElementById('packageIdInput{{ $listing->id }}').value=''; document.getElementById('packageForm{{ $listing->id }}').submit();">
                                    <span><i class="fas fa-inbox text-muted mr-2"></i>{{ __('Default') }}</span>
                                    @if (!$listing->package_id)
                                      <i class="fas fa-check text-success small"></i>
                                    @endif
                                  </button>
                                  @foreach ($packages as $pkg)
                                    <button type="button"
                                      class="dropdown-item d-flex justify-content-between align-items-center px-3 py-2 {{ (int) ($listing->package_id ?? 0) === (int) $pkg->id ? 'active' : '' }}"
                                      onclick="document.getElementById('packageIdInput{{ $listing->id }}').value='{{ $pkg->id }}'; document.getElementById('packageForm{{ $listing->id }}').submit();">
                                      <span class="text-truncate pr-2">{{ $pkg->title }}</span>
                                      @if ((int) ($listing->package_id ?? 0) === (int) $pkg->id)
                                        <i class="fas fa-check text-success small flex-shrink-0"></i>
                                      @endif
                                    </button>
                                  @endforeach
                                </div>
                              </div>
                            </form>
                          </td>

                          @if (count($charges) > 0)
                            <td>
                              @php
                                $order_status = App\Models\FeatureOrder::where('listing_id', $listing->id)->first();
                                $today_date = now()->format('Y-m-d');
                              @endphp

                              @if (is_null($order_status))
                                <button class="btn btn-primary featurePaymentModal btn-sm " data-toggle="modal"
                                  data-target="#featurePaymentModal_{{ $listing->id }}" data-id="{{ $listing->id }}"
                                  data-listing-id="{{ $listing->id }}">
                                  {{ __('Feature It') }}
                                </button>
                              @endif

                              @if ($order_status)
                                @if ($order_status->order_status == 'pending')
                                  <h2 class="d-inline-block"><span
                                      class="badge badge-warning">{{ __('pending') }}</span>
                                  </h2>
                                @endif
                                @if ($order_status->order_status == 'completed')
                                  @if ($order_status->end_date < $today_date)
                                    <button class="btn btn-primary featurePaymentModal  btn-sm"
                                      data-toggle="modal"data-target="#featurePaymentModal_{{ $listing->id }}"
                                      data-id="{{ $listing->id }}">{{ __('Feature It') }}</button>
                                  @else
                                    @if ($listing->vendor_id != 0)
                                      <h1 class="d-inline-block text-large"><span
                                          class="badge badge-success">{{ __('Active') }}</span>
                                      </h1>
                                    @else
                                      <form class="deleteForm d-block"
                                        action="{{ route('admin.featured_listing.delete', ['id' => $order_status->id]) }}"
                                        method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-danger  mt-1 btn-sm unFeatureBtn">
                                          {{ __('Unfeature') }}
                                          </h1>
                                        </button>
                                      </form>
                                    @endif
                                  @endif
                                @endif
                                @if ($order_status->order_status == 'rejected')
                                  <button class="btn btn-primary featurePaymentModal btn-sm "
                                    data-toggle="modal"data-target="#featurePaymentModal_{{ $listing->id }}"
                                    data-id="{{ $listing->id }}">{{ __('Feature It') }}</button>
                                @endif
                              @endif
                            </td>
                          @endif
                          <td>
                            @if (!empty($listing_content))
                              @php
                                $categoryName = App\Models\ListingCategory::where(
                                    'id',
                                    $listing_content->category_id,
                                )->first();
                              @endphp

                              <a href="{{ route('frontend.listings', ['category_id' => @$categoryName->slug]) }}"
                                target="_blank">

                                {{ @$categoryName->name }}
                              </a>
                            @else
                              --
                            @endif
                          </td>

                          <td>
                            <form id="statusForm{{ $listing->id }}" class="d-inline-block"
                              action="{{ route('admin.listing_management.update_listing_status') }}" method="post">
                              @csrf
                              <input type="hidden" name="listingId" value="{{ $listing->id }}">
                              <select
                                class="form-control {{ $listing->status == 1 ? 'bg-success' : ($listing->status == 2 ? 'bg-danger' : 'bg-warning') }} form-control-sm"
                                name="status"
                                onchange="document.getElementById('statusForm{{ $listing->id }}').submit();">
                                <option value="1" {{ $listing->status == 1 ? 'selected' : '' }}>
                                  {{ __('Approved') }}
                                </option>
                                <option value="0" {{ $listing->status == 0 ? 'selected' : '' }}>
                                  {{ __('Pending') }}
                                </option>
                                <option value="2" {{ $listing->status == 2 ? 'selected' : '' }}>
                                  {{ __('Rejected') }}
                                </option>
                              </select>
                            </form>
                          </td>
                          <td>
                            <form id="visibilityStatusForm{{ $listing->id }}" class="d-inline-block"
                              action="{{ route('admin.listing_management.update_listing_visibility') }}"
                              method="post">
                              @csrf
                              <input type="hidden" name="listingId" value="{{ $listing->id }}">
                              <select
                                class="form-control {{ $listing->visibility == 1 ? 'bg-success' : 'bg-danger' }} form-control-sm"
                                name="visibility"
                                onchange="document.getElementById('visibilityStatusForm{{ $listing->id }}').submit();">
                                <option value="1" {{ $listing->visibility == 1 ? 'selected' : '' }}>
                                  {{ __('Show') }}
                                </option>
                                <option value="0" {{ $listing->visibility == 0 ? 'selected' : '' }}>
                                  {{ __('Hide') }}
                                </option>

                              </select>
                            </form>
                          </td>
                          <td>
                            <div class="dropdown">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                  id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                  aria-expanded="false">
                                  {{ __('Select') }}
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                  <a href="{{ route('admin.listing_management.edit_listing', ['id' => $listing->id]) }}"
                                    class="dropdown-item">
                                    {{ __('Edit') }}
                                  </a>
                                  @if (!empty($listing_content))
                                    <a href="{{ route('frontend.listing.details', ['slug' => $listing_content->slug, 'id' => $listing->id]) }}"
                                      class="dropdown-item"target="_blank">
                                      {{ __('Preview') }}
                                    </a>
                                  @endif

                                  @if (!empty($listing->admin_listing_feature_permissions) &&
                                          (in_array('Messenger', $listing->admin_listing_feature_permissions) ||
                                              in_array('WhatsApp', $listing->admin_listing_feature_permissions) ||
                                              in_array('Telegram', $listing->admin_listing_feature_permissions) ||
                                              in_array('Tawk.To', $listing->admin_listing_feature_permissions)))
                                    <a href="{{ route('admin.listing_management.listing.plugins', ['id' => $listing->id]) }}"
                                      class="dropdown-item">
                                      {{ __('Manage Plugins') }}
                                    </a>
                                  @endif
                                  @if (!empty($listing->admin_listing_feature_permissions) && in_array('Business Hours', $listing->admin_listing_feature_permissions))
                                    <a href="{{ route('admin.listing_management.listing.business_hours', ['id' => $listing->id]) }}"
                                      class="dropdown-item">
                                      {{ __('Business Hours') }}
                                    </a>
                                  @endif
                                  @if (!empty($listing->admin_listing_feature_permissions) && in_array('Social Links', $listing->admin_listing_feature_permissions))
                                    <a href="{{ route('admin.listing_management.manage_social_link', ['id' => $listing->id]) }}"
                                      class="dropdown-item">
                                      {{ __('Social Links') }}
                                    </a>
                                  @endif
                                  @if (!empty($listing->admin_listing_feature_permissions) && in_array('Feature', $listing->admin_listing_feature_permissions))
                                    <a href="{{ route('admin.listing_management.manage_additional_specification_link', ['id' => $listing->id]) }}"
                                      class="dropdown-item">
                                      {{ __('Features') }}
                                    </a>
                                  @endif
                                  @if (!empty($listing->admin_listing_feature_permissions) && in_array('FAQ', $listing->admin_listing_feature_permissions))
                                    <a href="{{ route('admin.listing_management.listing.faq', ['id' => $listing->id]) }}"
                                      class="dropdown-item">
                                      {{ __('FAQs') }}
                                    </a>
                                  @endif
                                  <form class="deleteForm d-block"
                                    action="{{ route('admin.listing_management.delete_listing', ['id' => $listing->id]) }}"
                                    method="post">
                                    @csrf
                                    <button type="submit" class="deleteBtn">
                                      {{ __('Delete') }}
                                    </button>
                                  </form>
                                </div>
                              </div>
                          </td>
                        </tr>
                        @include('admin.listing.feature-payment')
                      @endforeach
                      @if (count($listings) < 3)
                        <tr class="opacity-0">
                          <td></td>
                        </tr>
                        <tr class="opacity-0">
                          <td></td>
                        </tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="center">
            {{ $listings->appends([
                    'vendor_id' => request()->input('vendor_id'),
                    'title' => request()->input('title'),
                    'status' => request()->input('status'),
                    'category' => request()->input('category'),
                    'language' => request()->input('language'),
                    'featured' => request()->input('featured'),
                ])->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
