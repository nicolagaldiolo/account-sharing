<template>
  <div class="balance">
    <h2>Balance</h2>
    <ul class="list-group mb-3">
      <li class="list-group-item d-flex justify-content-between lh-condensed">
        <div>
          <h4 class="my-0">Saldo contabile</h4>
          <small class="text-muted" v-if="dayRefundLimit">Disponibili dopo <strong>{{dayRefundLimit}}gg</strong> dalla ricezione del pagamento</small>
        </div>
        <span class="text-muted">
        <money-format :value="balance.pending" :locale="authUser.country" :currency-code='authUser.currency' :subunit-value=false :hide-subunits=false></money-format>
        </span>
      </li>
      <li class="list-group-item d-flex justify-content-between lh-condensed">
        <div>
          <h4 class="my-0">Saldo disponibile</h4>
        </div>
        <span class="text-muted">
          <money-format :value="balance.available" :locale="authUser.country" :currency-code='authUser.currency' :subunit-value=false :hide-subunits=false></money-format>
        </span>
      </li>
    </ul>
    <a href="#" class="btn btn-primary btn-lg btn-block">Preleva</a>
  </div>
</template>

<script>
import axios from 'axios'
import { helperMixin } from '~/mixins/helperMixin'
import MoneyFormat from 'vue-money-format'
import { mapGetters } from 'vuex'

export default {
  name: 'Balance',
  data: () => ({
    balance: {}
  }),

  mixins: [
    helperMixin
  ],

  components: {
    'money-format': MoneyFormat
  },

  computed: {
    ...mapGetters({
      dayRefundLimit: 'config/dayRefundLimit',
      authUser: 'auth/user',
      transactions: 'settings/transactions'
    })
  },

  created () {
    this.getBalance()
  },

  methods: {
    getBalance () {
      axios.get('/api/settings/balance').then(function (result) {
        if (result.error) {
          // Display error.message in your UI.
        } else {
          this.balance = result.data.data
        }
      }.bind(this))
    }
  }
}
</script>
