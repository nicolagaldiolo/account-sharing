<template>

  <div v-if="paymentmethods">
    <card title="Credit Card" v-if="paymentmethods.length">
        <div class="d-flex flex-wrap">
          <div v-for="paymentmethod in paymentmethods" :key="paymentmethod.id">
            <credit-card :paymentmethod="paymentmethod" :defaultPaymentmethod="defaultPaymentmethod" :eraseable="paymentmethods.length > 1"/>
          </div>
        </div>

        <div v-if="maxPaymentMethod > paymentmethods.length">
          <button class="mt-5 btn btn btn-outline-secondary btn-lg btn-block" @click.prevent="newCardForm">Nuova carta</button>
          <div v-if="showCardForm">
            <credit-card-new :checkoutMode="checkoutMode" v-on:payment-method-added="subscribe"/>
          </div>
        </div>

      <template v-slot:footer>
        <button class="btn btn-primary btn-lg btn-block" v-if="checkoutMode && !showCardForm" @click.prevent="subscribe">Iscriviti</button>
      </template>

    </card>
    <card v-else title="Credit Card">
      <div class="text-center">
        <fa icon="credit-card" size="4x"/>
        <h5 class="mt-2">Non hai metodi di pagamento configurati<br>aggiungine uno</h5>
      </div>

      <div v-if="maxPaymentMethod > paymentmethods.length">
        <button class="mt-5 btn btn btn-outline-secondary btn-lg btn-block" @click.prevent="newCardForm">Nuova carta</button>
        <div v-if="showCardForm">
          <credit-card-new :checkoutMode="checkoutMode" v-on:payment-method-added="subscribe"/>
        </div>
      </div>
    </card>

  </div>


</template>

<script>
import { mapGetters } from 'vuex'
import axios from 'axios'
import CreditCard from '../../components/CreditCard'
import CreditCardNew from '../../components/CreditCardNew'
import Swal from 'sweetalert2'

export default {
  components: {
    CreditCard,
    CreditCardNew
  },
  data: () => ({
    stripe: window.Stripe(window.config.stripeKey),
    showCardForm: false,
    maxPaymentMethod: window.config.maxPaymentMethod,
  }),

    props: {
        checkoutMode: { type: Boolean, default: false },
        action: { type: String, default: 'subscribe' }
    },

  computed: mapGetters({
    paymentmethods: 'stripe/paymentmethods',
    defaultPaymentmethod: 'stripe/defaultPaymentmethod',
    authUser: 'auth/user'
  }),

  created () {
    this.$store.dispatch('stripe/fetchPaymentMethods');
  },

  methods: {
      newCardForm () {
          this.showCardForm = !this.showCardForm
      },

      subscribe () {

        this.showCardForm = false

        if (this.checkoutMode) {
          const api = this.action === 'subscribe'
            ? `/api/sharings/${this.$route.params.sharing_id}/subscribe`
            : `/api/sharings/${this.$route.params.sharing_id}/restore`

          axios.post(api, {
            payment_method: this.defaultPaymentmethod
          }).then((response) => {
            //Outcome 1: Payment succeeds

            console.log(response);

            if (response.data.status === 'active' && response.data.latest_invoice.payment_intent.status === 'succeeded') {
              alert("TUTTO OK, PUOI ENRTRARE");
              this.redirectToSharing();
              //Outcome 3: Payment fails
            } else if (response.data.status === 'incomplete' && response.data.latest_invoice.payment_intent.status === 'requires_payment_method') {
              console.log("PROBLEMI COL METODO DI PAGAMENTO 22222");
              alert(response);

              // posso entrare qui se sono on session e quindi sto facendo il primo pagamento (incomplete) oppure a seguito di un pagamento fallito in fase di rinnovo (past_due)
            } else if ((response.data.status === 'incomplete' || response.data.status === 'past_due') && response.data.latest_invoice.payment_intent.status === 'requires_action') {
              console.log("AZIONE RICHIESTA");
              const paymentIntentSecret = response.data.latest_invoice.payment_intent.client_secret;
              this.stripe.handleCardPayment(paymentIntentSecret).then((result) => {
                if (result.error) {
                  console.log("PROBLEMI COL METODO DI PAGAMENTO 11111");
                  alert(response);
                } else {
                  this.redirectToSharing();
                  // The payment has succeeded. Display a success message.
                }
              });
            }
          })
        }
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
  },
}
</script>
