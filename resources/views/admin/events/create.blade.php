@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Add Event') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
      </li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Event Management') }}</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Add Event') }}</a></li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Add Event') }}</div>
          <a class="btn btn-info btn-sm float-right"
            href="{{ route('admin.event_management.events', ['language' => request()->get('language', $defaultLang->code)]) }}">
            <i class="fas fa-backward"></i> {{ __('Back') }}
          </a>
        </div>

        <div class="card-body">
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $err)
                  <li>{{ $err }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('admin.event_management.store_event') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="language" value="{{ request()->get('language', $defaultLang->code) }}">

            <div class="form-group">
              <label>{{ __('Image') }}*</label>
              <div class="thumb-preview mb-2">
                <img src="{{ asset('assets/img/noimage.jpg') }}" alt="" class="uploaded-img" style="max-height: 200px;">
              </div>
              <div class="btn btn-primary btn-sm upload-btn">
                {{ __('Choose Image') }}
                <input type="file" class="img-input" name="image" accept="image/*" required>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('Start date & time') }}*</label>
                  <input type="datetime-local" name="event_start" class="form-control" required
                    value="{{ old('event_start') }}">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('End date & time') }}</label>
                  <input type="datetime-local" name="event_end" class="form-control" value="{{ old('event_end') }}">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>{{ __('Location') }}</label>
              <input type="text" name="location" class="form-control" value="{{ old('location') }}"
                placeholder="{{ __('Venue or city') }}">
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('Status') }}*</label>
                  <select name="status" class="form-control" required>
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>{{ __('Published') }}</option>
                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('Serial Number') }}*</label>
                  <input type="number" name="serial_number" class="form-control" min="0"
                    value="{{ old('serial_number', 0) }}" required>
                </div>
              </div>
            </div>

            <div id="accordion" class="mt-3">
              @foreach ($languages as $language)
                <div class="version">
                  <div class="version-header" id="heading{{ $language->id }}">
                    <h5 class="mb-0">
                      <button type="button" class="btn btn-link" data-toggle="collapse"
                        data-target="#collapse{{ $language->id }}"
                        aria-expanded="{{ $language->is_default == 1 ? 'true' : 'false' }}">
                        {{ $language->name }} {{ $language->is_default == 1 ? '(Default)' : '' }}
                      </button>
                    </h5>
                  </div>
                  <div id="collapse{{ $language->id }}"
                    class="collapse {{ $language->is_default == 1 ? 'show' : '' }}"
                    data-parent="#accordion">
                    <div class="version-body p-3 border">
                      <div class="form-group">
                        <label>{{ __('Title') }}*</label>
                        <input type="text" class="form-control" name="{{ $language->code }}_title"
                          value="{{ old($language->code . '_title') }}" required>
                      </div>
                      <div class="form-group">
                        <label>{{ __('Summary') }}</label>
                        <textarea class="form-control" name="{{ $language->code }}_summary" rows="2">{{ old($language->code . '_summary') }}</textarea>
                      </div>
                      <div class="form-group">
                        <label>{{ __('Content') }}</label>
                        <textarea class="form-control summernote" name="{{ $language->code }}_content"
                          data-height="300">{{ old($language->code . '_content') }}</textarea>
                      </div>
                      <div class="form-group">
                        <label>{{ __('Meta Keywords') }}</label>
                        <input class="form-control" name="{{ $language->code }}_meta_keywords"
                          value="{{ old($language->code . '_meta_keywords') }}">
                      </div>
                      <div class="form-group">
                        <label>{{ __('Meta Description') }}</label>
                        <textarea class="form-control" rows="3"
                          name="{{ $language->code }}_meta_description">{{ old($language->code . '_meta_description') }}</textarea>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>

            <div class="text-center mt-4">
              <button type="submit" class="btn btn-success">{{ __('Save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script type="text/javascript" src="{{ asset('assets/js/admin-partial.js') }}"></script>
@endsection
