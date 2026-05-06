@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Email Settings') }}</h4>
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
        <a href="#">{{ __('Basic Settings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Email Settings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Mail To Admin') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('admin.basic_settings.update_mail_to_admin') }}" method="post">
          @csrf
          <div class="card-header">
            <div class="row">
              <div class="col-lg-12">
                <div class="card-title">{{ __('Mail To Admin') }}</div>
              </div>
            </div>
          </div>

          <div class="card-body py-5">
            <div class="row">
              <div class="col-lg-6 offset-lg-3">
                <div class="alert alert-warning text-center" role="alert">
                  <strong class="text-dark">{{ __('These addresses receive notifications (contact form, listing inquiries when no vendor email, etc.). Add one per line or separate with commas.') }}</strong>
                </div>

                <div class="form-group">
                  <label>{{ __('Email addresses') . '*' }}</label>
                  <textarea class="form-control" name="to_mail" rows="5"
                    placeholder="{{ __('One email per line (example@domain.com)') }}">{{ old('to_mail', $to_mail_display ?? '') }}</textarea>
                  <small class="form-text text-muted">{{ __('One address per line, or separated by commas / semicolons.') }}</small>
                  @if ($errors->has('to_mail'))
                    <p class="mt-2 mb-0 text-danger">{{ $errors->first('to_mail') }}</p>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="row">
              <div class="col-12 text-center">
                <button type="submit" class="btn btn-success">
                  {{ __('Update') }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="card mt-4">
        <div class="card-header">
          <div class="card-title">{{ __('Send test email') }}</div>
        </div>
        <form action="{{ route('admin.basic_settings.test_mail_to_admin') }}" method="post">
          @csrf
          <div class="card-body">
            <p class="text-muted mb-3">
              {{ __('Uses the same SMTP settings as') }}
              <a href="{{ route('admin.basic_settings.mail_from_admin') }}">{{ __('Mail From Admin') }}</a>.
              {{ __('SMTP must be enabled there before a test can be sent.') }}
            </p>
            <div class="row justify-content-center">
              <div class="col-lg-6">
                <div class="form-group">
                  <label>{{ __('Test recipient (optional)') }}</label>
                  <input class="form-control" type="email" name="test_recipient" value="{{ old('test_recipient') }}"
                    placeholder="{{ __('Leave empty to send a test to every address saved above') }}">
                  @if ($errors->has('test_recipient'))
                    <p class="mt-2 mb-0 text-danger">{{ $errors->first('test_recipient') }}</p>
                  @endif
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="row">
              <div class="col-12 text-center">
                <button type="submit" class="btn btn-info">
                  {{ __('Send test email') }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
