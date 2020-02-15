<template>
  <card title="Aggiungi nuova carta di pagamento">
    <div id="card-element"></div>
    <div id="card-errors" role="alert"></div>
    <template v-slot:footer>
      <v-link :class="[{ 'disabled': disabled }, 'btn-lg btn-block']" type="primary" :loading="loading" :action="createSetupIntent">{{ checkoutMode ? 'Iscriviti' : 'Aggiungi carta' }}</v-link>
    </template>
  </card>
</template>

<script>
  import axios from 'axios'
  import Swal from 'sweetalert2'
  import {mapGetters} from "vuex";

  export default {
    name: 'CreditCardNew',
    data: () => ({
      disabled: true,
      loading: false,
      stripe: window.Stripe(window.config.stripeKey),
      cardInstance: '',
      cardOption: {
        hidePostalCode: true,
        style: {
          base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
              color: '#aab7c4'
            }
          },
          invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
          }
        }
      },
    }),

    props: {
      checkoutMode: {
        type: Boolean,
        default: false
      }
    },

    computed: mapGetters({
      authUser: 'auth/user'
    }),

    mounted () {
      // Create a Stripe client.
      this.$nextTick(function () {
        if (document.getElementById('card-element') != null) {
          var element = this.stripe.elements();

          // Create an instance of the card Element.
          this.cardInstance = element.create('card', { ...this.cardOption })

          // Add an instance of the card Element into the `card-element` <div>.
          this.cardInstance.mount('#card-element');

          // Handle real-time validation errors from the card Element.
          this.cardInstance.addEventListener('change', (event) => {
            var displayError = document.getElementById('card-errors')

            displayError.textContent = (event.error) ? event.error.message : ''
            this.disabled = !event.complete
          })

        }
      })
    },

    methods: {
      createSetupIntent () {
        this.loading = true
        axios.get('/api/settings/setupintent').then((result) => {
          try{
            this.stripe.confirmCardSetup(result.data.data.client_secret, {
                payment_method: {
                  card: this.cardInstance,
                  billing_details: {
                    email: this.authUser.email
                  }
                }
              }
            ).then((result) => {
              if (result.error) {
                Swal.fire({
                  type: 'error',
                  title: 'Errore',
                  text: 'Per favore riprovare con un altro metodo di pagamento'
                }).then(() => {
                  this.loading = false
                })
              } else {
                this.addPaymentMethod(result.setupIntent.payment_method)
              }
            })
          } catch (e) {
            //console.log(e);
            Swal.fire({
              type: 'error',
              title: 'Errore generico',
              text: 'Si è verificato un errore, riprovare più tardi'
            }).then(() => {
              this.loading = false
            })
          }
        })
      },

      addPaymentMethod (paymentMethod) {
        this.$store.dispatch('stripe/addPaymentMethod', paymentMethod).then((result) => {
          if (result) {
            Swal.fire({
              type: 'success',
              title: 'Fantastico',
              text: 'Hai aggiunto un nuovo metodo di pagamento'
            }).then(() => {
              this.loading = false
              this.$emit('payment-method-added')
            })
          } else {
            Swal.fire({
              type: 'error',
              title: 'Errore',
              text: 'Per favore riprovare con un altro metodo di pagamento'
            }).then(() => {
              this.loading = false
            })
          }
        })
      }
    }
  }
</script>

<style scoped>

  .StripeElement {
    box-sizing: border-box;

    height: 40px;

    padding: 10px 12px;

    border: 1px solid transparent;
    border-radius: 4px;
    background-color: white;

    box-shadow: 0 1px 3px 0 #e6ebf1;
    -webkit-transition: box-shadow 150ms ease;
    transition: box-shadow 150ms ease;
  }

  .StripeElement--focus {
    box-shadow: 0 1px 3px 0 #cfd7df;
  }

  .StripeElement--invalid {
    border-color: #fa755a;
  }

  .StripeElement--webkit-autofill {
    background-color: #fefde5 !important;
  }

</style>
