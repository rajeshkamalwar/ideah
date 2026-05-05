@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Add vendor') }}</h4>
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
        <a href="#">{{ __('Vendors Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Add vendor') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-12">
              <div class="card-title">{{ __('Add vendor') }}</div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-10 mx-auto">
              <div class="alert alert-danger pb-1 dis-none" id="listingErrors">
                <ul></ul>
              </div>
              <form id="listingForm" action="{{ route('admin.vendor_management.save-vendor') }}" method="post">
                @csrf
                @if (! empty($forListingFlow))
                  <input type="hidden" name="for_listing" value="1">
                  <input type="hidden" name="return_language" value="{{ $vendorAddLanguageCode }}">
                @endif
                @include('admin.end-user.vendor.partials.vendor-create-fields')
              </form>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="row">
            <div class="col-12 text-center">
              <button type="submit" form="listingForm" class="btn btn-success">
                {{ __('Update') }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('script')
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-listing.js') }}"></script>
@endsection
