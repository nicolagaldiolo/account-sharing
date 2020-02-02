<template>
  <div class="mt-4">
    <div class="container">
      <div class="row">
        <div class="col-md-8">
          <paymentmethods checkout-mode="true"></paymentmethods>
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
import paymentmethods from '../settings/paymentmethods';

export default {
  middleware: [
    'userCanPay'
  ],
  components: {
    paymentmethods
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
      })
  }
}
</script>

<style scoped>
</style>
