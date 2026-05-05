@extends('admin.layout')

@includeIf('admin.partials.rtl-style')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Category FAQ templates') }}</h4>
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
        <a href="{{ route('admin.listing_specification.categories', ['language' => $language->code]) }}">{{ __('Categories') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ strlen($category->name) > 40 ? mb_substr($category->name, 0, 40, 'UTF-8') . '…' : $category->name }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="alert alert-info">
        <i class="fas fa-info-circle mr-1"></i>
        {{ __('These questions and answers are shown on every listing in this category for the selected language. They appear first, followed by FAQs added on each listing.') }}
      </div>

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="card">
        <div class="card-header">
          <div class="row align-items-center">
            <div class="col-lg-8">
              <div class="card-title d-inline-block mb-0">{{ __('FAQs for') }}:
                <strong>{{ $category->name }}</strong>
                <span class="badge badge-secondary ml-1">{{ $language->name }}</span>
              </div>
            </div>
            <div class="col-lg-4 text-lg-right mt-2 mt-lg-0">
              <a href="#" data-toggle="modal" data-target="#createCategoryFaqModal" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> {{ __('Add') }}
              </a>
              <a href="{{ route('admin.listing_specification.categories', ['language' => $language->code]) }}"
                class="btn btn-info btn-sm">
                <i class="fas fa-backward"></i> {{ __('Back') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          @if ($faqs->isEmpty())
            <h3 class="text-center">{{ __('NO FAQ FOUND') . '!' }}</h3>
          @else
            <div class="table-responsive">
              <table class="table table-striped mt-3">
                <thead>
                  <tr>
                    <th scope="col">{{ __('Question') }}</th>
                    <th scope="col">{{ __('Serial Number') }}</th>
                    <th scope="col">{{ __('Actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($faqs as $faq)
                    <tr>
                      <td>{{ strlen($faq->question) > 80 ? mb_substr($faq->question, 0, 80, 'UTF-8') . '…' : $faq->question }}
                      </td>
                      <td>{{ $faq->serial_number }}</td>
                      <td>
                        <a class="btn btn-secondary btn-sm editCategoryFaqBtn" href="#" data-toggle="modal"
                          data-target="#editCategoryFaqModal" data-id="{{ $faq->id }}">
                          <span class="btn-label"><i class="fas fa-edit"></i></span>
                        </a>
                        <form class="deleteForm d-inline-block"
                          action="{{ route('admin.listing_specification.delete_category_faq_template', ['id' => $faq->id]) }}"
                          method="post">
                          @csrf
                          <button type="submit" class="btn btn-danger btn-sm deleteBtn">
                            <span class="btn-label"><i class="fas fa-trash"></i></span>
                          </button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Create --}}
  <div class="modal fade" id="createCategoryFaqModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('Add category FAQ') }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ route('admin.listing_specification.store_category_faq_template') }}" method="post">
          @csrf
          <input type="hidden" name="listing_category_id" value="{{ $category->id }}">
          <div class="modal-body">
            <div class="form-group">
              <label>{{ __('Question') }}*</label>
              <input type="text" class="form-control" name="question" value="{{ old('question') }}" required
                maxlength="255">
            </div>
            <div class="form-group">
              <label>{{ __('Answer') }}*</label>
              <textarea class="form-control" name="answer" rows="5" required>{{ old('answer') }}</textarea>
            </div>
            <div class="form-group">
              <label>{{ __('Serial Number') }}*</label>
              <input type="number" class="form-control ltr" name="serial_number" value="{{ old('serial_number', 0) }}"
                required min="0">
              <small
                class="form-text text-muted">{{ __('The higher the serial number is, the later the FAQ will be shown within category templates.') }}</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">{{ __('Close') }}</button>
            <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Edit --}}
  <div class="modal fade" id="editCategoryFaqModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('Edit category FAQ') }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ route('admin.listing_specification.update_category_faq_template') }}" method="post">
          @csrf
          <input type="hidden" name="id" id="edit_category_faq_id">
          <div class="modal-body">
            <div class="form-group">
              <label>{{ __('Question') }}*</label>
              <input type="text" class="form-control" name="question" id="edit_category_faq_question" required
                maxlength="255">
            </div>
            <div class="form-group">
              <label>{{ __('Answer') }}*</label>
              <textarea class="form-control" name="answer" id="edit_category_faq_answer" rows="5" required></textarea>
            </div>
            <div class="form-group">
              <label>{{ __('Serial Number') }}*</label>
              <input type="number" class="form-control ltr" name="serial_number" id="edit_category_faq_serial"
                required min="0">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">{{ __('Close') }}</button>
            <button type="submit" class="btn btn-primary btn-sm">{{ __('Update') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
@section('script')
  <script>
    window.__categoryFaqTemplates = @json($faqs->keyBy('id'));
  </script>
  <script>
    (function($) {
      "use strict";
      $(document).on('click', '.editCategoryFaqBtn', function() {
        var id = String($(this).data('id'));
        var payload = (window.__categoryFaqTemplates && window.__categoryFaqTemplates[id]) || {};
        $('#edit_category_faq_id').val(id);
        $('#edit_category_faq_question').val(payload.question || '');
        $('#edit_category_faq_answer').val(payload.answer || '');
        $('#edit_category_faq_serial').val(payload.serial_number != null ? payload.serial_number : 0);
      });
    })(jQuery);
  </script>
@endsection
