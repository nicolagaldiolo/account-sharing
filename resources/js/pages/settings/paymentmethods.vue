<template>
  <div>
    <card title="Credit Card">
      <div v-if="paymentmethods.methods.data.length">

        <div v-for="paymentmethod in paymentmethods.methods.data" :key="paymentmethod.id">
          <h5 class="card-title">{{paymentmethod.card.brand}}</h5>
          <strong>{{paymentmethod.card.last4}}</strong>
          Scadenza: <span>{{paymentmethod.card.exp_month}}</span>/<span>{{paymentmethod.card.exp_year}}</span>
          <span v-if="paymentmethod.id === paymentmethods.defaultPaymentMethod" class="badge badge-pill badge-primary">Default</span>
          <a v-else class="btn btn-link" href="#" @click.prevent="setDefaultPaymentMethods(paymentmethod.id)">Rendi default</a>
          <a v-if="paymentmethod.id !== paymentmethods.defaultPaymentMethod && paymentmethods.methods.data.length > 1" class="btn btn-link" href="#" @click.prevent="removePaymentMethod(paymentmethod.id)">Elimina carta</a>
        </div>
        <button class="btn btn-primary btn-lg btn-block" v-if="checkoutMode && !showCardForm" @click.prevent="subscribeWithDefaultPaymentMethod">Iscriviti</button>
      </div>
      <div v-else>
        <h1>Non hai metodi di pagamento configurati, aggiungine uno</h1>
      </div>
      <template v-slot:footer>
        <div v-if="showCardForm || paymentmethods.methods.data.length <= 0">
          <div>
            <label for="card-element">
              Credit or debit card
            </label>
            <div id="card-element">
              <!-- A Stripe Element will be inserted here. -->
            </div>

            <!-- Used to display form errors. -->
            <div id="card-errors" role="alert"></div>
          </div>

          <div v-if="checkoutMode">
            <button class="btn btn-primary btn-lg btn-block" @click.prevent="subscribeWithNewPaymentMethod">Iscriviti</button>
          </div>
          <div v-else>
            <button @click.prevent="addPaymentMethod">Aggiungi carta</button>
          </div>
        </div>

      </template>
    </card>
    <button class="btn btn btn-outline-secondary btn-lg btn-block" @click.prevent="newCardForm">Nuova carta</button>
  </div>

</template>

<script>
import { mapGetters } from 'vuex'
import axios from 'axios'

export default {
  data: () => ({
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
    },
    mytestcard: '',
    stripe: window.Stripe(window.config.stripeKey),
    showCardForm: false
  }),

    props: {
        checkoutMode: { type: Boolean, default: false },
        action: { type: String, default: 'subscribe' }
    },

  computed: mapGetters({
    paymentmethods: 'stripe/paymentmethods',
    authUser: 'auth/user'
  }),

  created () {
    this.$store.dispatch('stripe/fetchPaymentMethods')
  },

  mounted () {
    // Create a Stripe client.


    // Create an instance of Elements.
    var element = this.stripe.elements();

    // Custom styling can be passed to options when creating an Element.
    // (Note that this demo uses a wider set of styles than the guide below.)


    // Create an instance of the card Element.
    this.mytestcard = element.create('card', {style: this.style});

    // Add an instance of the card Element into the `card-element` <div>.

    setTimeout(()=>this.mytestcard.mount('#card-element') , 5000);


    // Handle real-time validation errors from the card Element.
    this.mytestcard.addEventListener('change', function(event) {
      var displayError = document.getElementById('card-errors');
      if (event.error) {
        displayError.textContent = event.error.message;
      } else {
        displayError.textContent = '';
      }
    });


  },

  methods: {

      newCardForm () {
          this.showCardForm = !this.showCardForm
      },

      setDefaultPaymentMethods (id) {
      this.$store.dispatch('stripe/setDefaultPaymentMethods', id)
    },

      removePaymentMethod (id) {
        this.$store.dispatch('stripe/removePaymentMethod', id)
    },

      addPaymentMethod () {

        axios.get('/api/settings/setupintent').then(function (result) {
            if (result.error) {
                // Display error.message in your UI.
            } else {
                const clientSecret = result.data.client_secret;

                this.stripe.confirmCardSetup(clientSecret, {
                        payment_method: {
                            card: this.mytestcard,
                            billing_details: {
                                email: this.authUser.email
                            }
                        }
                    }
                ).then(function(result) {
                    if (result.error) {
                        // Display error.message in your UI.
                        console.log(result.error);
                    } else {
                        this.$store.dispatch('stripe/addPaymentMethod', result.setupIntent.payment_method)
                    }
                }.bind(this));

            }
        }.bind(this))
      },

      subscribeWithNewPaymentMethod () {
          this.stripe.createPaymentMethod('card', this.mytestcard).then(data => {
              this.subscribe(data.paymentMethod.id)
          })
      },

      subscribeWithDefaultPaymentMethod () {
          this.subscribe(this.paymentmethods.defaultPaymentMethod);
      },

      subscribe (paymentMethod) {

          const api = this.action === 'subscribe'
              ? `/api/sharings/${this.$route.params.sharing_id}/subscribe`
              : `/api/sharings/${this.$route.params.sharing_id}/restore`

          axios.post(api, {
              payment_method: paymentMethod
          }).then((response) => {
              //Outcome 1: Payment succeeds
              if (response.data.status === 'active' && response.data.latest_invoice.payment_intent.status === 'succeeded') {
                  this.redirectToSharing();
                  //Outcome 3: Payment fails
              } else if (response.data.status === 'incomplete' && response.data.latest_invoice.payment_intent.status === 'requires_payment_method') {
                  console.log("PROBLEMI COL METODO DI PAGAMENTO 22222");
                  alert(response);

              // posso entrare qui se sono on session e quindi sto facendo il primo pagamento (incomplete) oppure a seguito di un pagamento fallito in fase di rinnovo (past_due)
              } else if ((response.data.status === 'incomplete' || response.data.status === 'past_due') && response.data.latest_invoice.payment_intent.status === 'requires_action') {
                  console.log("AZIONE RICHIESTA");
                  const paymentIntentSecret = response.data.latest_invoice.payment_intent.client_secret;
                  this.stripe.handleCardPayment(paymentIntentSecret).then(function (result) {
                      if (result.error) {
                          console.log("PROBLEMI COL METODO DI PAGAMENTO 11111");
                          alert(response);
                      } else {
                          this.redirectToSharing();
                          // The payment has succeeded. Display a success message.
                      }
                  }.bind(this));
              }
          })
      },

    redirectToSharing () {
        alert("Complimenti, sei iscritto");
        this.$router.push({
            name: 'sharing.show',
            params: {
                category_id: this.$route.params.category_id,
                sharing_id: this.$route.params.sharing_id
            }
        })
    }
  }
}
</script>

<style>

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
