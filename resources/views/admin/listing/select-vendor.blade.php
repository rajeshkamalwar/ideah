@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Add Listing') }}</h4>
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
        <a href="#">{{ __('Add Listing') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Select Vendor') }}</a>
      </li>
    </ul>
  </div>
  <div class="alert alert-danger d-none" id="vendorMessage">

  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row align-items-center w-100">
            <div class="col-md-8">
              <div class="card-title d-inline-block mb-md-0">{{ __('Add listing wizard') }}</div>
              <div class="mt-2 mt-md-0 text-muted small">
                <span class="font-weight-bold text-primary">{{ __('Step 1') }}:</span> {{ __('Vendor') }}
                <span class="mx-1">→</span>
                <span class="text-muted">{{ __('Step 2') }}:</span> {{ __('Listing details') }}
              </div>
            </div>
            <div class="col-md-4 text-md-right mt-2 mt-md-0">
              <a href="{{ route('admin.vendor_management.add_vendor', ['language' => $selectVendorLanguage, 'for_listing' => 1]) }}"
                class="small text-secondary">
                <i class="fas fa-external-link-alt"></i> {{ __('Open full-page vendor form') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-10 offset-lg-1">
              <div class="alert alert-danger pb-1 dis-none" id="listingErrors">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <ul></ul>
              </div>

              <ul class="nav nav-tabs nav-pills nav-secondary mb-4" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="wizard-tab-choose" data-toggle="tab" href="#wizard-pane-choose"
                    role="tab" aria-controls="wizard-pane-choose" aria-selected="true">
                    {{ __('Choose vendor') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="wizard-tab-create" data-toggle="tab" href="#wizard-pane-create" role="tab"
                    aria-controls="wizard-pane-create" aria-selected="false">
                    {{ __('Create new vendor') }}
                  </a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane fade show active" id="wizard-pane-choose" role="tabpanel"
                  aria-labelledby="wizard-tab-choose">
                  <form id="vendorSelect" action="{{ route('admin.listing_management.find_vendor_id') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                      <div class="col-lg-12">
                        <div class="form-group">
                          <label for="">{{ __('Vendor') }}</label>
                          <select name="vendor_id" class="form-control js-example-basic-single1">
                            <option selected value="0">{{ __('Please Select') }}</option>
                            @foreach ($vendors as $vendor)
                              <option value="{{ $vendor->id }}">{{ $vendor->username }}</option>
                            @endforeach
                          </select>
                          <p class="text-warning mb-0">
                            {{ __('if you do not select any vendor, then this Listing will be listed for Admin') }}</p>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>

                <div class="tab-pane fade" id="wizard-pane-create" role="tabpanel"
                  aria-labelledby="wizard-tab-create">
                  <div class="alert alert-danger pb-1 dis-none" id="vendorWizardErrors">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <ul class="mb-0"></ul>
                  </div>
                  <p class="text-muted small mb-3">
                    {{ __('Save the vendor account, then you will continue to add the listing.') }}
                  </p>
                  <form id="vendorWizardCreateForm" action="{{ route('admin.vendor_management.save-vendor') }}"
                    method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="for_listing" value="1">
                    <input type="hidden" name="return_language" value="{{ $selectVendorLanguage }}">
                    @include('admin.end-user.vendor.partials.vendor-create-fields')
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="row">
            <div class="col-12 text-center">
              <button type="submit" form="vendorSelect" class="btn btn-success" id="footerNextChoose">
                {{ __('Next') }}
              </button>
              <button type="submit" form="vendorWizardCreateForm" class="btn btn-success d-none"
                id="footerSaveCreate">
                {{ __('Save and continue') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection


@section('script')
  <script>
    'use strict';
    (function () {
      function syncFooter() {
        var createActive = $('#wizard-tab-create').hasClass('active');
        if (createActive) {
          $('#footerNextChoose').addClass('d-none');
          $('#footerSaveCreate').removeClass('d-none');
        } else {
          $('#footerNextChoose').removeClass('d-none');
          $('#footerSaveCreate').addClass('d-none');
        }
      }
      $(function () {
        syncFooter();
        $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
          syncFooter();
        });
      });
    })();
  </script>
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-dropzone.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-vendor-wizard.js') }}"></script>
@endsection
