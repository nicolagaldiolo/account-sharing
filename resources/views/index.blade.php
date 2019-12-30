@php
$config = [
    'appName' => config('app.name'),
    'locale' => $locale = app()->getLocale(),
    'locales' => config('app.locales'),
    'githubAuth' => config('services.github.client_id'),
    'stripeKey' => config('services.stripe.key'),
    'limitUserAge' => config('custom.limit_user_age'),
];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>{{ config('app.name') }}</title>

  <link rel="stylesheet" href="{{ mix('dist/css/app.css') }}">
</head>
<body>
  <div id="app"></div>

  {{-- Global configuration object --}}
  <script>
    window.config = @json($config);
  </script>

  <script src="https://js.stripe.com/v3/"></script>
  <script src="https://cdn.rawgit.com/cretueusebiu/412715093d6e8980e7b176e9de663d97/raw/aea128d8d15d5f9f2d87892fb7d18b5f6953e952/objectToFormData.js"></script>
  {{-- Load the application scripts --}}
  <script src="{{ mix('dist/js/app.js') }}"></script>
</body>
</html>
