<template>
  <div>
    <card v-if="customer.sources.data.length" title="Credit Card">
      <div v-for="card in customer.sources.data" :key="card.id">
        <h5 class="card-title">{{card.brand}}</h5>
        <strong>{{card.last4}}</strong>
        <span>{{card.exp_month}}</span>
        <span>{{card.exp_year}}</span>
        <span v-if="card.id === customer.default_source" class="badge badge-pill badge-primary">Default</span>
        <a v-else class="btn btn-link" href="#" @click.prevent="updateCustomer(card.id)">Rendi default</a>
        <a v-if="customer.sources.data.length > 1" class="btn btn-link" href="#" @click.prevent="removeCard(card.id)">Elimina carta</a>
      </div>
      <template v-slot:footer>
        <div class="d-flex">
          <card-stripe class='stripe-card' ref='card'
                       :class='{ complete }'
                       :stripe='api_key'
                       :options='stripeOptions'
                       @change='complete = $event.complete'
          />
          <button class='pay-with-stripe btn btn-primary flex-shrink-0' @click='addCard' :disabled='!complete'>Aggiungi carta di pagamento</button>
        </div>
      </template>
    </card>

  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import { Card, createToken } from 'vue-stripe-elements-plus'

export default {
    components: {
        'card-stripe' : Card
    },

    scrollToTop: false,

  data: () => ({
    api_key: window.config.stripeKey,
      complete: false,
    stripeOptions: {
      hidePostalCode: true
      // see https://stripe.com/docs/stripe.js#element-options for details
    }
  }),

  computed: mapGetters({
    customer: 'stripe/customer'
  }),

  created () {
    this.$store.dispatch('stripe/fetchCustomer')
  },

  methods: {
    updateCustomer (id) {
      this.$store.dispatch('stripe/updateCustomer', id)
    },

    removeCard (cardId) {
        this.$store.dispatch('stripe/removeCard', cardId)
    },

    addCard () {
      // createToken returns a Promise which resolves in a result object with
      // either a token or an error key.
      // See https://stripe.com/docs/api#tokens for the token object.
      // See https://stripe.com/docs/api#errors for the error object.
      // More general https://stripe.com/docs/stripe.js#stripe-create-token.
      createToken().then(data => {
          this.$store.dispatch('stripe/addCard', data.token)
          this.$refs.card.clear()
      })
    }
  }
}
</script>

<style>
  .stripe-card {
    width: 100%;
    border: 1px solid grey;
  }
  .stripe-card.complete {
    border-color: green;
  }
</style>
