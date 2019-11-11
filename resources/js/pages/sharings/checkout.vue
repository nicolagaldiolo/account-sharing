<template>
  <div class="mt-4">
    <div class="container">
      <div class="row">
        <div class="col-md-8">
          <wallet checkout-mode="true"></wallet>
        </div>
        <div class="col-md-4">
          <card title="Credit Card">
            <h5>{{sharing.name}}</h5>
            <h2>{{sharing.price}}</h2>
            <template v-slot:footer>
              <div>
                <button @click.prevent="pay">Conferma pagamento</button>
              </div>
            </template>
          </card>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'
import axios from 'axios'
import wallet from '../settings/wallet';

export default {
  middleware: [
    'auth',
    'userCanPay'
  ],
  components: {
    wallet
  },

  data: () => ({
      stripe: window.Stripe(window.config.stripeKey)
  }),

  created () {
  },

  computed: {
      ...mapGetters({
          sharing: 'sharings/sharing',
          authUser: 'auth/user'
      }),
  },

  methods: {
      /*
      pay () {
          axios.post(`/api/sharings/${this.sharing.id}/subscribe`).then((response) => {

              console.log(response);
              if(response.data.status === 'active' && response.data.latest_invoice.payment_intent.status === 'succeeded') {
                  console.log("Entro qui");
                  this.$router.push({ name: 'sharing.show', params: { category_id: this.sharing.category_id, sharing_id: this.sharing.id } })
              } else if (response.data.status === 'incomplete' && response.data.latest_invoice.payment_intent.status === 'requires_action') {
                  console.log("Entro qui 2");
                  alert('AZIONE RICHIESTA');
                  console.log(response);
                  const paymentIntentSecret = response.data.latest_invoice.payment_intent.client_secret;
                  this.stripe.handleCardPayment(paymentIntentSecret).then(function(result) {
                      if (result.error) {
                          this.changePaymentMethod(result);
                      } else {
                          this.$router.push({ name: 'sharing.show', params: { category_id: this.sharing.category_id, sharing_id: this.sharing.id } })
                          // The payment has succeeded. Display a success message.
                      }
                  }.bind(this));
              } else if (response.data.latest_invoice.payment_intent.status === 'requires_payment_method') {
                  this.changePaymentMethod(response);
              }
          })
      },

      changePaymentMethod (response) {
          alert('Problemi con metodo di pagamento, si prega di cambiare metodo di pagamento')
      }

       */
  }
}
</script>

<style scoped>
</style>
