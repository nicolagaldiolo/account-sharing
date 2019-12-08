<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>{{ config('app.name') }}</title>

  <link rel="stylesheet" href="{{ mix('dist/css/app.css') }}">
</head>
<body>

@foreach ($test as $user)
  {{$user->owner->name}}
@endforeach


{{--
  <input id="cardholder-name" type="text" value="Andrea Vallusso">
  <!-- placeholder for Elements -->
  <div id="card-element"></div>
  <button id="card-button" data-secret="{{ $payment_intent->client_secret }}">
    Submit Payment
  </button>
--}}
  <script src="https://js.stripe.com/v3/"></script>

  <script>

      var stripe = Stripe('pk_test_lvXZ2N70WJ37XevvFybQal0400qwAHfw2f');

      var elements = stripe.elements();
      var cardElement = elements.create('card');
      cardElement.mount('#card-element');

      var cardholderName = document.getElementById('cardholder-name');
      var cardButton = document.getElementById('card-button');
      var clientSecret = cardButton.dataset.secret;

      cardButton.addEventListener('click', function(ev) {
          stripe.handleCardPayment(
              clientSecret, cardElement, {
                  payment_method_data: {
                      billing_details: {name: cardholderName.value}
                  }
              }
          ).then(function(result) {
              if (result.error) {
                  // Display error.message in your UI.
                  console.log(result.error);
              } else {
                  console.log(result);
                  // The payment has succeeded. Display a success message.
              }
          });
      });

  </script>

  {{-- Load the application scripts --}}
  <script src="{{ mix('dist/js/app.js') }}"></script>
</body>
</html>
