<template>
  <div class="mt-4">
    <div class="container">
      <div class="row">
        <div class="col-md-8">
          <paymentmethods :checkout-mode="true" :sharing="sharing"></paymentmethods>
        </div>
        <div class="col-md-4">
          <card :title="sharing.name">
            <img class="rounded-circle" width="50" :src="sharing.owner.photo_url">
            <h5>{{sharing.owner.username}}</h5>
            <hr>

            <ul class="list-group">
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Periodo
                <strong>{{ renewalDate.from.format('D MMM YYYY') }} -> {{ renewalDate.to.format('D MMM YYYY') }}</strong>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Ricorrenza pagamento
                <strong>{{ sharing.renewal_frequency.frequency }}</strong>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Contributo spese
                <strong><money-format :value="sharing.price_no_fee" :locale="authUser.country" :currency-code="authUser.currency" :subunit-value=false :hide-subunits=false></money-format></strong>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Spese di gestione
                <strong><money-format :value="sharing.fee" :locale="authUser.country" :currency-code="authUser.currency" :subunit-value=false :hide-subunits=false></money-format></strong>
              </li>
              <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
                <strong>Totale da pagare</strong>
                <h4><money-format :value="sharing.price_with_fee" :locale="authUser.country" :currency-code="authUser.currency" :subunit-value=false :hide-subunits=false></money-format></h4>
              </li>
            </ul>
          </card>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { helperMixin } from '~/mixins/helperMixin'
import { mapGetters } from 'vuex'
import axios from 'axios'
import paymentmethods from '../settings/paymentmethods';
import MoneyFormat from 'vue-money-format'

export default {
  components: {
    paymentmethods,
    MoneyFormat
  },

  mixins: [ helperMixin ],

  data: () => ({
    today: new Date(),
    stripe: window.Stripe(window.config.stripeKey)
  }),

  created () {},

  computed: {
    ...mapGetters({
        sharing: 'sharings/sharing',
        authUser: 'auth/user'
    }),
    renewalDate () {
      return this.calcRenewalData(this.sharing.renewal_frequency)
    }
  }
}
</script>

<style scoped>
</style>
