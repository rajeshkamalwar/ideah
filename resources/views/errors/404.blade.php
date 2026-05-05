<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>404 | {{ config('app.name') }}</title>
  <link rel="stylesheet" href="{{ asset('assets/front/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/front/css/responsive.css') }}">
</head>

<body>
  <section class="error-area ptb-100 text-center" style="min-height: 60vh; display: flex; align-items: center;">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="not-found mb-4">
            <img src="{{ asset('assets/front/images/404.svg') }}" alt="404" style="max-width: 100%; height: auto;">
          </div>
          <div class="error-txt">
            <h2>{{ __('404 not found') }}</h2>
            <p class="mx-auto">
              {{ __('The page you are looking for might have been moved, renamed, or might never existed.') }}</p>
            <a href="{{ url('/') }}" class="btn btn-lg btn-primary mt-3">{{ __('Back to Home') }}</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</body>

</html>
