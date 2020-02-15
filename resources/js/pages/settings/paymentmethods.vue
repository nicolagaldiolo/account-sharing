<template>

  <div v-if="paymentmethods">
    <card v-if="paymentmethods.length" title="Credit Card">
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
        <v-link v-if="checkoutMode && !showCardForm" :class="['btn-lg btn-block']" type="primary" :loading="loading" :action="subscribe">Iscriviti</v-link>
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
    loading: false,
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

    console.log('Eccomi')
    window.Echo.private(`App.User.${this.authUser.id}`).notification(notifications => {
      console.log('Ricevuto', notifications)
    })

  },

  methods: {
    newCardForm () {
      this.showCardForm = !this.showCardForm
    },

    subscribe () {
      this.showCardForm = false
      this.loading = true

      if (this.checkoutMode) {
        const api = this.action === 'subscribe'
          ? `/api/sharings/${this.$route.params.sharing_id}/subscribe`
          : `/api/sharings/${this.$route.params.sharing_id}/restore`

        axios.post(api).then((response) => {
          if (response.data.data.status === 1 && response.data.meta.latest_invoice.payment_intent.status === 'succeeded') {
            // Outcome 1: Payment succeeds (Subscription active)
            //this.redirectToSharing()
            console.log('redirectToSharing');
          } else if (response.data.data.status === 2 && response.data.meta.latest_invoice.payment_intent.status === 'requires_payment_method') {
            // Outcome 2: Payment fails (Subscription incomplete)
            this.paymentFailed()
          } else if ((response.data.data.status === 2 || response.data.data.status === 4) && response.data.meta.latest_invoice.payment_intent.status === 'requires_action') {
            // Outcome 3: Subscription incomplete or past_due and action require
            const paymentIntentSecret = response.data.meta.latest_invoice.payment_intent.client_secret;
            this.stripe.handleCardPayment(paymentIntentSecret).then((result) => {
              if (result.error) {
                this.paymentFailed()
              } else {
                axios.post(`/api/sharings/${this.$route.params.sharing_id}/subscribe-confirm`).then((response) => {
                  if (response.data.data.subscription && response.data.data.subscription.status === 1) {
                    console.log('redirectToSharing');
                  } else {
                    this.genericError()
                  }
                })
              }
            })
          } else {
            // Outcome 4: other cases
            this.genericError()
          }
        })
      }
    },

    redirectToSharing () {
      Swal.fire({
        type: 'success',
        title: 'Pagamento avvenuto con successo',
        text: 'Goditi la tua condivisione'
      }).then((result) => {
        this.$router.push({
          name: 'sharing.show',
          params: {
            category_id: this.$route.params.category_id,
            sharing_id: this.$route.params.sharing_id
          }
        })
      })
    },
    paymentFailed () {
      Swal.fire({
        type: 'Error',
        title: 'Pagamento fallito',
        text: 'Siamo spiacenti, il pagamento non è andato a buon fine, si prega di utilizzare un\'altro metodo di pagamento'
      }).then(result => {
        this.loading = false
      })
    },
    genericError () {
      Swal.fire({
        type: 'error',
        title: 'Errore generico',
        text: 'Si è verificato un errore, riprovare più tardi'
      }).then(result => {
        this.loading = false
      })
    }
  }
}
</script>
