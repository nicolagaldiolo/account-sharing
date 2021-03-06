@php
$config = [
    'appName' => config('app.name'),
    'locale' => $locale = app()->getLocale(),
    'locales' => config('app.locales'),
    'countries' => config('custom.countries'),
    'githubAuth' => config('services.github.client_id'),
    'stripeKey' => config('services.stripe.key'),
    'limitUserAge' => config('custom.limit_user_age'),
    'maxPaymentMethod' => config('custom.stripe.max_payment_method'),
    'dayRefundLimit' => config('custom.day_refund_limit'),
    'sharingUserStatus' => \App\Enums\SharingStatus::toSelectArray(),
    'sharingsVisibility' => \App\Enums\SharingVisibility::toSelectArray(),
    'renewalFrequency' => \App\Http\Resources\RenewalFrequency::collection(\App\RenewalFrequency::all())
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
